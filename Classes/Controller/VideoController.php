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

namespace Netcreators\Overheidsmediaplayer\Controller;

use Netcreators\Overheidsmediaplayer\Domain\Repository\VideoPlayerRepository;
use Netcreators\Overheidsmediaplayer\Sanitization\Sanitizer;
use Netcreators\Overheidsmediaplayer\View\VideoView;
use Netcreators\Overheidsmediaplayer\View\FileView;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Plugin\AbstractPlugin;

/**
 * A controller
 *
 * @author Leonie Philine Bitto <leonie@netcreators.nl>, Patrick Broens
 * @package TYPO3
 * @subpackage overheidsmediaplayer
 */
class VideoController extends AbstractPlugin
{

    const FLEXFORM_TAB_GENERAL = 'sGENERAL';
    const FLEXFORM_TAB_PLAYER = 'sPLAYER';
    const FLEXFORM_TAB_IMAGE = 'sIMAGE';
    const FLEXFORM_TAB_VIDEOS = 'sVIDEOS';
    const FLEXFORM_TAB_CAPTION = 'sCAPTION';
    const FLEXFORM_TAB_TRANSCRIPTION = 'sTRANSCRIPTION';
    const FLEXFORM_TAB_AUDIO = 'sAUDIO';

    /**
     * @var string
     */
    public $scriptRelPath = 'Classes/Controller/locallang.xml';

    /**
     * The extension key
     *
     * @var string
     */
    public $extKey = 'overheidsmediaplayer';

    /**
     * Prefix for piVars of this extension
     *
     * @var string
     */
    public $prefixId = 'Tx_Overheidsmediaplayer_Controller_VideoController';

    /**
     * Configuration
     *
     * @var array
     */
    public $configuration = [];

    /**
     * Sanitized piVars
     *
     * @var array
     */
    protected $sanitizedPiVars = [];


    /**
     * The dispatcher
     * Determines which action has to be called according to incoming variables
     *
     * @param string $content
     * @param array $configuration
     * @return string The output of the extension
     */
    public function execute($content, $configuration)
    {
        $this->setConfiguration($configuration);

        // Check on the incoming action
        switch ($this->sanitizedPiVars['action']) {
            case 'file':
                $this->fileAction();
                break;
            default:
                $content = $this->indexAction();
        }

        return $content;
    }

    /**
     * The index action
     *
     * Renders the videoplayer
     *
     * @return string The rendered view
     */
    protected function indexAction()
    {
        /** @var VideoPlayerRepository $repository */
        $repository = GeneralUtility::makeInstance(
            'Netcreators\\Overheidsmediaplayer\\Domain\\Repository\\VideoPlayerRepository'
        );
        $repository->setController($this);
        $repository->setContentObjectRenderer($this->cObj);

        /** @var VideoView $view */
        $view = GeneralUtility::makeInstance('Netcreators\\Overheidsmediaplayer\\View\\VideoView');
        $view->setContentObject($this->cObj);
        $view->setController($this);
        $view->setRepository($repository);

        return $view->render($this->configuration['layoutTemplatePartialRootPath']);
    }

    /**
     * File action
     * Checks all variables with securehash and outputs a file
     *
     * @return void
     */
    protected function fileAction()
    {
        $resourceFactory = ResourceFactory::getInstance();
        $fileReference = $resourceFactory->getFileReferenceObject($this->sanitizedPiVars['file']);

        /** @var FileView $view */
        $view = GeneralUtility::makeInstance('Netcreators\\Overheidsmediaplayer\\View\\FileView');
        $view->setController($this);
        $view->setFileReference($fileReference);
        $view->setSecureHash($this->sanitizedPiVars['sHash']);
        $view->output();

        exit();
    }


    /**
     * Set the configuration from TS and Flexform
     *
     * @param array $configuration TypoScript
     * @return void
     */
    protected function setConfiguration($configuration)
    {
        $this->configuration = $configuration;

        // Load local language
        $this->pi_loadLL();

        // Sanitize incoming values from $_POST or $_GET
        /** @var Sanitizer $sanitizer */
        $sanitizer = GeneralUtility::makeInstance('Netcreators\\Overheidsmediaplayer\\Sanitization\\Sanitizer');
        $this->sanitizedPiVars = $sanitizer->sanitize($this->piVars);
    }
}

