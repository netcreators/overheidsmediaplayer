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

namespace Netcreators\Overheidsmediaplayer\Domain\Repository;
use Netcreators\Overheidsmediaplayer\Controller\VideoController;
use Netcreators\Overheidsmediaplayer\Domain\Model\VideoPlayer;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Video Player repository
 *
 * @author Leonie Philine Bitto <leonie@netcreators.nl>, Patrick Broens
 * @package TYPO3
 * @subpackage overheidsmediaplayer
 */
class VideoPlayerRepository
{

    /**
     * The controller
     *
     * @var VideoController
     */
    protected $controller;

    /**
     * The content object
     *
     * @var ContentObjectRenderer
     */
    protected $contentObjectRenderer;

    /**
     * Build the video player model from TypoScript and FlexForm configuration
     *
     * @return VideoPlayer
     */
    public function buildModel()
    {

        // Convert FlexForm XML string to array
        $this->controller->pi_initPIflexForm();
        // Get the FlexForm data
        $piFlexForm = $this->contentObjectRenderer->data['pi_flexform'];

        /** @var VideoPlayer $videoPlayer */
        $videoPlayer = GeneralUtility::makeInstance('Netcreators\\Overheidsmediaplayer\\Domain\\Model\\VideoPlayer');
        $videoPlayer->setUid($this->contentObjectRenderer->data['uid']);
        $videoPlayer->setTitle(
            $this->controller->pi_getFFvalue($piFlexForm, 'title', VideoController::FLEXFORM_TAB_GENERAL)
        );
        $videoPlayer->setDescription(
            $this->controller->pi_getFFvalue($piFlexForm, 'description', VideoController::FLEXFORM_TAB_GENERAL)
        );
        $videoPlayer->setWidth(
            $this->controller->pi_getFFvalue($piFlexForm, 'width', VideoController::FLEXFORM_TAB_PLAYER)
        );
        $videoPlayer->setHeight(
            $this->controller->pi_getFFvalue($piFlexForm, 'height', VideoController::FLEXFORM_TAB_PLAYER)
        );
        $videoPlayer->setShowVideoLinks(
            $this->controller->pi_getFFvalue($piFlexForm, 'showVideoLinks', VideoController::FLEXFORM_TAB_PLAYER)
        );
        $videoPlayer->setShowCaptionLink(
            $this->controller->pi_getFFvalue($piFlexForm, 'showCaptionLink', VideoController::FLEXFORM_TAB_PLAYER)
        );
        $videoPlayer->setTranscription(
            $this->controller->pi_getFFvalue($piFlexForm, 'transcription', VideoController::FLEXFORM_TAB_TRANSCRIPTION)
        );

        $videoFields = ['webm', 'mp4', 'theora', 'flash', 'quicktime', 'windowsmedia'];
        foreach ($videoFields as $type) {
            $videoFileReference = $this->getFileReferenceFromFlexForm($type);
            if ($videoFileReference) {
                $videoPlayer->addVideoFileReference($videoFileReference, $type);
            }
        }

        $imageFileReference = $this->getFileReferenceFromFlexForm('image');
        if ($imageFileReference) {
            $videoPlayer->setImageFileReference($imageFileReference);
        }

        $captionFields = ['vtt']; // Used to also have 'srt', which was no longer necessary with mediaElement.js.
        foreach ($captionFields as $type) {
            $captionFileReference = $this->getFileReferenceFromFlexForm($type);
            if ($captionFileReference) {
                $videoPlayer->addCaptionFileReference($captionFileReference, $type);
            }
        }

        if (!(int)$this->controller->pi_getFFvalue(
            $piFlexForm,
            'disableAdditionalAudio',
            VideoController::FLEXFORM_TAB_PLAYER
        )
        ) {
            $audioFileReference = $this->getFileReferenceFromFlexForm('audio');
            if ($audioFileReference) {
                $videoPlayer->setAudioFileReference($audioFileReference);
            }
        }

        return $videoPlayer;
    }

    /**
     * Returns a FileReferences stored in a FlexForm field.
     * @param string $flexFormFieldName
     *
     * @return FileReference|NULL
     */
    protected function getFileReferenceFromFlexForm($flexFormFieldName)
    {
        return $this->getFileReferencesFromFlexForm($flexFormFieldName, true);
    }

    /**
     * Returns one or multiple FileReferences stored in a FlexForm field.
     * @param string $flexFormFieldName
     * @param bool $returnSingle Defaults to FALSE. If set to TRUE, then a single FileReference is returned.
     *
     * @return array<FileReference>|FileReference|NULL
     */
    protected function getFileReferencesFromFlexForm($flexFormFieldName, $returnSingle = false)
    {

        $rows = $this->getFlexFormInlineField($flexFormFieldName);

        $fileReferences = [];
        $resourceFactory = ResourceFactory::getInstance();

        foreach ($rows as $row) {
            $fileReference = $resourceFactory->createFileReferenceObject($row);

            if ($returnSingle) {
                return $fileReference;
            }

            $fileReferences[] = $fileReference;
        }

        if ($returnSingle) {
            return null;
        }

        return $fileReferences;
    }

    /**
     * Get values of a FlexForm IRRE field.
     *
     * @param string $flexFormFieldName
     * @return array
     */
    protected function getFlexFormInlineField($flexFormFieldName)
    {
        /** @var DatabaseConnection $TYPO3_DB */
        global $TYPO3_DB;

        $rows = $TYPO3_DB->exec_SELECTgetRows(
            '*',
            'sys_file_reference',
            'tablenames = \'tt_content\''
            . ' AND uid_foreign = \'' . $this->contentObjectRenderer->data['uid'] . '\''
            . ' AND fieldname = \'' . $flexFormFieldName . '\''
            . ' AND deleted = 0',
            '',
            'sorting_foreign'
        );

        return $rows;
    }

    /**
     * Set the controller
     *
     * @param VideoController $controller
     * @return void
     */
    public function setController(VideoController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Set the content object
     *
     * @param ContentObjectRenderer $contentObject
     * @return void
     */
    public function setContentObjectRenderer(ContentObjectRenderer $contentObject)
    {
        $this->contentObjectRenderer = $contentObject;
    }
}


