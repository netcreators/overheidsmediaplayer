(function ($) {
	$(document).ready(function () {

		// Close all foldouts
		$('.overheidsmediaplayer .foldout .foldout-body')
			.hide()
			.prev('.foldout-header').removeClass('opened');

		// Open upon click or via keyboard (tab to focus, then enter to activate)
		$('.overheidsmediaplayer .foldout .foldout-header a').click(function (event) {

			// Disable "#' href activation
			event.preventDefault();

			$(this).toggleClass('opened')
				.parent().next('.foldout-body').toggle()
				.prev('.foldout-header').toggleClass('opened');

			var $accessibilityFoldoutHeaderStatusLabel = $(this).find('.foldout-header-status');
			$accessibilityFoldoutHeaderStatusLabel.html(
				$accessibilityFoldoutHeaderStatusLabel.data(
					$(this).hasClass('opened')
						? 'label-opened'
						: 'label-closed'
				)
			);

		});


		$('.overheidsmediaplayer').each(function (index, element) {
			// Initialize video and optional additional audio

			var $element = $(element);
			var $additionalAudioToggle = $($element.find('input[type=\'checkbox\'].overheidsmediaplayer-additional-audio-toggle').get(0));


			// Initialize video element via ME.js (native HTML5, flash, silverlight)
			$element.find('video').mediaelementplayer({

				toggleCaptionsButtonWhenOnlyOne: true,
				pauseOtherPlayers: false,

				success: function (videoElementShim, domVideoElement) {

					// Adjust foldouts with to video width (which might be smaller than its container)
					$element.find('.foldouts').css('width', $element.find('video').css('width'));

					if (!$element.find('audio').length) {
						return;
					}


					// Initialize additional audio track - also with flash and silverlight fallbacks via ME.js
					var $audio = $($element.find('audio').get(0));

					$audio.mediaelementplayer({

						pauseOtherPlayers: false,

						success: function (audioElementShim, domAudioElement) {

							// Initial state: not linked.
							$additionalAudioToggle.attr('checked', false);

							var onPlayPause = function () {
								// If we have audio and the audio is paused, play that too
								if (videoElementShim.paused) {
									audioElementShim.pause();
								} else {
									audioElementShim.play();
								}
							};


							var onEnded = function () {
								// Pause the video and audio
								videoElementShim.pause();
								audioElementShim.pause();
							};


							var onTimeUpdate = function () {
								// If we have audio and the audio has sufficiently loaded
								if (audioElementShim.readyState >= 4) {
									// If the audio and video times are different,
									// update the audio time to keep it in sync
									if (Math.ceil(audioElementShim.currentTime) !== Math.ceil(videoElementShim.currentTime)) {
										audioElementShim.currentTime = videoElementShim.currentTime;
									}
								}
							};


							var onSeeked = function () {
								// If we have audio and the audio has sufficiently loaded
								if (audioElementShim.readyState >= 4) {
									// Update the audio time to keep it in sync
									audioElementShim.currentTime = videoElementShim.currentTime;
								}
							};


							// Link audio track to the video element's media group when checkbox is checked.
							$additionalAudioToggle.on('change',function () {
								if (this.checked) {
									onSeeked();
									onPlayPause();

									videoElementShim.addEventListener('play', onPlayPause);
									videoElementShim.addEventListener('pause', onPlayPause);
									videoElementShim.addEventListener('ended', onEnded);
									videoElementShim.addEventListener('timeupdate', onTimeUpdate);
									videoElementShim.addEventListener('seeked', onSeeked);
								} else {
									audioElementShim.pause();

									videoElementShim.removeEventListener('play', onPlayPause);
									videoElementShim.removeEventListener('pause', onPlayPause);
									videoElementShim.removeEventListener('ended', onEnded);
									videoElementShim.removeEventListener('timeupdate', onTimeUpdate);
									videoElementShim.removeEventListener('seeked', onSeeked);
								}
							}).trigger('change');

							// Show the 'Aanvullende audiotrack' checkbox toggle.
							$additionalAudioToggle.closest('.overheidsmediaplayer-additional-audio-toggle-container').css('display', 'block');
						}
					});
				}
			});
		});

	});
})(jQuery);
