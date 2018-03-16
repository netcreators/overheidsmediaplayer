<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Patrick Broens
 *  (c) 2013-2017 Leonie Philine Bitto <leonie@netcreators.nl>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

namespace Netcreators\Overheidsmediaplayer\View;

use Netcreators\Overheidsmediaplayer\Domain\Model\VideoPlayer;
use Netcreators\Overheidsmediaplayer\Domain\Repository\VideoPlayerRepository;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * HTML view video
 *
 * @author Leonie Philine Bitto <leonie@netcreators.nl>, Patrick Broens
 * @package TYPO3
 * @subpackage overheidsmediaplayer
 */
class VideoView extends AbstractView
{

    const FLASH_FALLBACK_VIDEO_MEDIA_TYPE = 'flash';
    const ADDITIONAL_AUDIO_MEDIA_TYPE = 'mp3';
    const CAPTION_MEDIA_TYPE = 'caption_file';

    protected $videoTypes = [
        'webm',
        'mp4',
        'theora',
        'flash',
        'quicktime',
        'windowsmedia'
    ];


    /**
     * The repository
     *
     * @var VideoPlayerRepository
     */
    protected $repository;

    /**
     * The player object
     *
     * @var VideoPlayer
     */
    protected $playerObject;

    /**
     * The current cObj
     *
     * @var ContentObjectRenderer
     */
    protected $contentObject;


    /**
     * Render the response part which means there is at least one video
     *
     * @param string $layoutTemplatePartialRootPath HTML part for response
     * @return string HTML
     */
    public function render($layoutTemplatePartialRootPath)
    {

        $this->playerObject = $this->repository->buildModel();
        if (!count($this->playerObject->getVideoFileReferences())) {
            return $this->controller->pi_getLL('error_video');
        }


        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $standaloneView */
        $standaloneView = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager')
            ->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');

        $standaloneView->getRequest()->setControllerExtensionName('Overheidsmediaplayer');
        $standaloneView->getRequest()->setPluginName('Videocontroller');
        $standaloneView->setFormat('html');

        $layoutTemplatePartialRootPath = GeneralUtility::getFileAbsFileName($layoutTemplatePartialRootPath);
        $standaloneView->setTemplatePathAndFilename($layoutTemplatePartialRootPath . 'Templates/Video.html');
        $standaloneView->setLayoutRootPaths([$layoutTemplatePartialRootPath . 'Layouts']);
        $standaloneView->setPartialRootPaths([$layoutTemplatePartialRootPath . 'Partials']);


        $videoFiles = [];
        foreach($this->videoTypes as $mediaType) {
            $videoFiles[] = $this->getMediaFileInfo($mediaType);
        }
        $videoFiles = array_filter($videoFiles);

        $fluidTemplateVariables = [
            'contentElementUid' => $this->contentObject->data['uid'],
            // Player

            'width' => $this->playerObject->getWidth(),
            'height' => $this->playerObject->getHeight(),
            'title' => $this->contentObject->stdWrap(
                    $this->playerObject->getTitle(),
                    $this->controller->configuration['title.']
                ),
            'description' => $this->contentObject->stdWrap(
                    $this->playerObject->getDescription(),
                    $this->controller->configuration['description.']
                ),
            'posterImageUrl' => $this->playerObject->getImageFileReference()
                    ? $this->contentObject->cObjGetSingle('IMG_RESOURCE',
                        [
                            'file' =>
                                $this->playerObject->getImageFileReference()->getPublicUrl(),
                            'file.' => [
                                'width' => $this->playerObject->getWidth(),
                                'height' => $this->playerObject->getHeight()
                            ]
                        ]
                    )
                    : null,
            // Downloads Foldout and media sources
            'hasDownloads' => $this->isVideoDownloadAvailable()
                || $this->isCaptionDownloadAvailable(),
            'showVideoLinks' => $this->playerObject->getShowVideoLinks(),
            'videoFiles' => $videoFiles,
            'flashFallbackVideoFileInfo' => $this->getMediaFileInfo(self::FLASH_FALLBACK_VIDEO_MEDIA_TYPE),
            'audioFileInfo' => $this->getMediaFileInfo(self::ADDITIONAL_AUDIO_MEDIA_TYPE),
            'showCaptionLink' => $this->playerObject->getShowCaptionLink(),
            'captionFiles' => $this->getCaptionFileInfoSet(),
            // Transcription foldout

            'transcription' => trim($this->playerObject->getTranscription())
                    ? $this->controller->pi_RTEcssText(
                        $this->playerObject->getTranscription()
                    )
                    : ''
        ];

        $fluidTemplateVariables['renderFoldout'] = $fluidTemplateVariables['hasDownloads']
            || trim($fluidTemplateVariables['transcription']);

        $standaloneView->assignMultiple($fluidTemplateVariables);

        $this->addResponsiveStylesToHeaderData();

        return $standaloneView->render();
    }


    protected function addResponsiveStylesToHeaderData()
    {

        $this->getTypoScriptFrontendController()->additionalHeaderData[
                'overheidsmediaplayer' . $this->contentObject->data['uid']
            ] = "

		<style>
			#overheidsmediaplayer" . $this->contentObject->data['uid'] . " {
				/* For this specific player instance with its explicit default width */
				max-width: " . $this->playerObject->getWidth() . "px;

				/* Not setting max-height (" . $this->playerObject->getHeight() . ") for auto-calculated aspect ratio. */
			}

			#overheidsmediaplayer" . $this->contentObject->data['uid'] . " video {
				/* For all players */
				width: 100%;
				height: auto;

				/* For this specific player instance with its explicit default width */
				max-width: " . $this->playerObject->getWidth() . "px;

				/* Not setting max-height (" . $this->playerObject->getHeight() . ") for auto-calculated aspect ratio. */
			}

			#overheidsmediaplayer" . $this->contentObject->data['uid'] . " .mejs-container-fullscreen video {
			    max-width: 100%;
			}
		</style>
		";
    }


    /**
     * @param string $mediaType @see $this->videoTypes
     * @return array|NULL
     */
    protected function getMediaFileInfo($mediaType)
    {
        $mediaFileReference = ($mediaType == 'mp3')
            ? $this->playerObject->getAudioFileReference()
            : $this->playerObject->getVideoByType($mediaType);

        if (!$mediaFileReference) {
            return null;
        }

        return [
            'fileReference' => $mediaFileReference,
            'mediaType' => $mediaType,
            'mediaSourceUrl' => GeneralUtility::locationHeaderUrl('/') . $mediaFileReference->getPublicUrl(),
            'fileDownloadUrl' => $this->getFileDownloadUrl($mediaFileReference),
            'formattedFileCreationDateDate' => $this->getFormattedFileCreationDateDate($mediaFileReference),
            'fileTypeIcon' => $this->getFileTypeIcon($mediaType),
            'formattedFileSize' => $this->getFormattedFileSize($mediaFileReference)
        ];
    }


    /**
     * @return array
     */
    protected function getCaptionFileInfoSet()
    {
        $captionFileInfoSet = [];

        /** @var FileReference $captionFileReference */
        foreach ($this->playerObject->getCaptionFileReferences() as $captionFileReference) {

            $captionFileInfoSet[] = [
                'fileReference' => $captionFileReference,
                'mediaType' => self::CAPTION_MEDIA_TYPE,
                'kind' => 'subtitles',
                'mediaSourceUrl' => GeneralUtility::locationHeaderUrl('/') . $captionFileReference->getPublicUrl(),
                'fileDownloadUrl' => $this->getFileDownloadUrl($captionFileReference),
                'formattedFileCreationDateDate' => $this->getFormattedFileCreationDateDate($captionFileReference),
                'fileTypeIcon' => $this->getFileTypeIcon(self::CAPTION_MEDIA_TYPE),
                'formattedFileSize' => $this->getFormattedFileSize($captionFileReference),

                // This is nasty yet unchanged from v3.x - but can we improve on the situation?
                // Map sys_file_reference.sys_language_uid?
                'language' => 'nl-NL',
            ];
        }

        return $captionFileInfoSet;
    }


    /**
     * @param FileReference $fileReference
     * @return string
     */
    protected function getFileDownloadUrl(FileReference $fileReference = null)
    {
        if (!$fileReference) {
            return '';
        }

        if ($this->controller->configuration['secureDownloads']) {
            $action = 'file';
            $file = $fileReference->getUid();
            $secureHash = substr(
                md5(
                    $action .
                    $file .
                    $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']
                ),
                0,
                8
            );


            $localAbsoluteReferencePrefix = $GLOBALS['TSFE']->absRefPrefix
                ? '' // if TSFE::$absRefPrefix is set, then $GLOBALS['TSFE']->tmpl->linkData(),
                // called in ContentObjectRenderer::typolink(), will prefix the returned URL.
                // In this case, we would not want to add a manual prefix here.
                : GeneralUtility::locationHeaderUrl('/');

            return $localAbsoluteReferencePrefix . $this->controller->pi_linkTP_keepPIvars_url(
                [
                    'action' => $action,
                    'file' => $file,
                    'sHash' => $secureHash
                ],
                0,
                1
            );
        }

        return GeneralUtility::locationHeaderUrl('/') . $fileReference->getPublicUrl();
    }


    /**
     * @param FileReference $fileReference
     * @return string
     */
    protected function getFormattedFileCreationDateDate(FileReference $fileReference = null)
    {
        if (!$fileReference) {
            return '';
        }

        return $this->contentObject->stdWrap(
            $fileReference->getCreationTime(),
            $this->controller->configuration['formatting.']['date.']
        );
    }


    /**
     * @param string $type @see $this->videoTypes
     * @return string
     */
    protected function getFileTypeIcon($type)
    {
        if (!$type) {
            return '';
        }

        return $this->controller->configuration['icons.'][$type];
    }


    /**
     * @param FileReference $fileReference
     * @return string
     */
    protected function getFormattedFileSize(FileReference $fileReference = null)
    {
        if (!$fileReference) {
            return '';
        }

        return $this->contentObject->stdWrap(
            $fileReference->getSize(),
            $this->controller->configuration['formatting.']['size.']
        );
    }


    /**
     * Check if there are videos available to download
     * and if the user is allowed to download
     *
     * @return boolean TRUE if available
     */
    protected function isVideoDownloadAvailable()
    {
        if ($this->playerObject->getShowVideoLinks()) {
            foreach ($this->videoTypes as $type) {
                $videoObject = $this->playerObject->getVideoByType($type);
                if (is_object($videoObject)) {
                    return true;
                }
            }
        }
        return false;
    }


    /**
     * Check if the caption file is available to download
     * and if the user is allowed to download
     *
     * @return boolean TRUE if available
     */
    protected function isCaptionDownloadAvailable()
    {
        if ($this->playerObject->getShowCaptionLink() &&
            count($this->playerObject->getCaptionFileReferences())
        ) {
            return true;
        }
        return false;
    }


    /**
     * Sets the repository to be used for rendering
     *
     * @param VideoPlayerRepository $repository
     * @return void
     */
    public function setRepository(VideoPlayerRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Sets the cObj
     *
     * @param ContentObjectRenderer $contentObject
     * @return void
     */
    public function setContentObject(ContentObjectRenderer $contentObject)
    {
        $this->contentObject = $contentObject;
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        /** var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $TSFE */
        global $TSFE;

        return $TSFE;
    }
}
