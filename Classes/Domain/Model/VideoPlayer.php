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

namespace Netcreators\Overheidsmediaplayer\Domain\Model;

use TYPO3\CMS\Core\Resource\FileReference;

/**
 * Video player model
 *
 * @author Leonie Philine Bitto <leonie@netcreators.nl>, Patrick Broens
 * @package TYPO3
 * @subpackage overheidsmediaplayer
 */
class VideoPlayer
{

    /**
     * The uid
     *
     * @var integer
     */
    private $uid = 0;

    /**
     * The title
     *
     * @var string
     */
    private $title = '';

    /**
     * The description
     *
     * @var string
     */
    private $description = '';

    /**
     * The width of the player in pixels
     *
     * @var integer
     */
    private $width = 0;

    /**
     * The height of the player in pixels
     *
     * @var integer
     */
    private $height = 0;

    /**
     * The transcription of the videos
     *
     * @var string
     */
    private $transcription = '';

    /**
     * Show the links to download the video files
     *
     * @var boolean
     */
    private $showVideoLinks = false;

    /**
     * Show the link to download the caption file
     *
     * @var boolean
     */
    private $showCaptionLink = false;

    /**
     * The video file objects
     *
     * @var array<FileReference>
     */
    private $videoFileReferences = [];

    /**
     * The fallback image object
     *
     * @var FileReference
     */
    private $imageFileReference = null;

    /**
     * The caption object
     *
     * @var array<FileReference>
     */
    private $captionFileReferences = [];

    /**
     * The audio object
     *
     * extra audio stream to be embedded in the player
     *
     * @var FileReference
     */
    private $audioFileReference = null;

    /**
     * Sets the uid
     *
     * @param integer $uid The uid
     * @return void
     */
    public function setUid($uid)
    {
        $this->uid = (integer)$uid;
    }

    /**
     * Returns the uid
     *
     * @return integer The uid
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Sets the title
     *
     * @param string $title The title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = (string)$title;
    }

    /**
     * Returns the title
     *
     * @return string The title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the description
     *
     * @param string $description The description
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = (string)$description;
    }

    /**
     * Returns the description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the width of the player in pixels
     *
     * @param integer $width The width
     * @return void
     */
    public function setWidth($width)
    {
        $this->width = (integer)$width;
    }

    /**
     * Returns the width of the player in pixels
     *
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Sets the height of the player in pixels
     *
     * @param integer $height The height
     * @return void
     */
    public function setHeight($height)
    {
        $this->height = (integer)$height;
    }

    /**
     * Returns the height of the player in pixels
     *
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set the transcription text
     *
     * @param string $transcription The transcription
     * @return void
     */
    public function setTranscription($transcription)
    {
        $this->transcription = (string)$transcription;
    }

    /**
     * Return the transcription
     *
     * @return string
     */
    public function getTranscription()
    {
        return $this->transcription;
    }

    /**
     * Sets if links to videos should be shown
     *
     * @param boolean $showVideoLinks TRUE means show
     * @return void
     */
    public function setShowVideoLinks($showVideoLinks)
    {
        $this->showVideoLinks = (boolean)$showVideoLinks;
    }

    /**
     * Returns if links to videos should be shown
     *
     * @return boolean
     */
    public function getShowVideoLinks()
    {
        return $this->showVideoLinks;
    }

    /**
     * Sets if link to caption file should be shown
     *
     * @param boolean $showCaptionLink TRUE means show
     * @return void
     */
    public function setShowCaptionLink($showCaptionLink)
    {
        $this->showCaptionLink = (boolean)$showCaptionLink;
    }

    /**
     * Returns if link to caption file should be shown
     *
     * @return boolean
     */
    public function getShowCaptionLink()
    {
        return $this->showCaptionLink;
    }

    /**
     * Add a video object to the player
     *
     * @param FileReference $videoFileReference The video fileReference
     * @param string $type
     * @return void
     */
    public function addVideoFileReference(FileReference $videoFileReference, $type)
    {
        $this->videoFileReferences[$type] = $videoFileReference;
    }

    /**
     * Return a video object by type
     *
     * @param string $type
     * @return FileReference|NULL
     */
    public function getVideoByType($type)
    {
        if (array_key_exists($type, $this->videoFileReferences) && is_object($this->videoFileReferences[$type])) {
            return $this->videoFileReferences[$type];
        } else {
            return null;
        }
    }

    /**
     * Return the video objects
     *
     * @return array<FileReference>
     */
    public function getVideoFileReferences()
    {
        return $this->videoFileReferences;
    }

    /**
     * Set the image object
     *
     * @param FileReference $imageFileReference The image fileReference
     * @return void
     */
    public function setImageFileReference(FileReference $imageFileReference)
    {
        $this->imageFileReference = $imageFileReference;
    }

    /**
     * Return the image object
     *
     * @return FileReference|NULL
     */
    public function getImageFileReference()
    {
        return $this->imageFileReference;
    }

    /**
     * Add a caption object to the player
     *
     * @param FileReference $captionFileReference The caption fileReference
     * @param string $type
     * @return void
     */
    public function addCaptionFileReference(FileReference $captionFileReference, $type)
    {
        $this->captionFileReferences[$type] = $captionFileReference;
    }

    /**
     * Return a caption object by type
     *
     * @param string $type
     * @return FileReference
     */
    public function getCaptionFileReferenceByType($type)
    {
        if (array_key_exists($type, $this->captionFileReferences) && is_object($this->captionFileReferences[$type])) {
            return $this->captionFileReferences[$type];
        } else {
            return null;
        }
    }

    /**
     * Set the caption objects
     *
     * @param array <FileReference> $captions The caption fileReference
     * @return void
     */
    public function setCaptionFileReferences(array $captions)
    {
        $this->captionFileReferences = $captions;
    }

    /**
     * Return the caption objects
     *
     * @return array<FileReference>
     */
    public function getCaptionFileReferences()
    {
        return $this->captionFileReferences;
    }

    /**
     * Set the audio object
     *
     * @param FileReference $audioFileReference The audio fileReference
     * @return void
     */
    public function setAudioFileReference(FileReference $audioFileReference)
    {
        $this->audioFileReference = $audioFileReference;
    }

    /**
     * Return the audio object
     *
     * @return FileReference|NULL
     */
    public function getAudioFileReference()
    {
        return $this->audioFileReference;
    }
}

