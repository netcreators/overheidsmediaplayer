<?php
/*  Copyright notice
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

use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The file view.
 *
 * @author Leonie Philine Bitto <leonie@netcreators.nl>, Patrick Broens
 */
class FileView extends AbstractView
{

    /**
     * If TRUE, the posted hash is corresponding the variables
     * so we can send the file, because the post is trusted
     *
     * @var boolean
     */
    private $secureHash = false;

    /**
     * The FAL FileReference
     *
     * @var FileReference
     */
    protected $fileReference = null;

    /**
     * Outputs the file to the browser using http header
     * Only when secure hash is trusted and the file exists
     *
     * @return void
     */
    public function output()
    {
        if (!$this->fileReference) {
            exit($this->controller->pi_getLL('no_file'));
        }

        $this->assertValidFile($this->fileReference);

        switch ($this->fileReference->getExtension()) {
            case 'asf':
                $contentType = 'video/x-ms-asf';
                break;
            case 'avi':
                $contentType = 'video/avi';
                break;
            case 'doc':
                $contentType = 'application/msword';
                break;
            case 'zip':
                $contentType = 'application/zip';
                break;
            case 'xls':
                $contentType = 'application/vnd.ms-excel';
                break;
            case 'gif':
                $contentType = 'image/gif';
                break;
            case 'jpg':
            case 'jpeg':
                $contentType = 'image/jpeg';
                break;
            case 'wav':
                $contentType = 'audio/wav';
                break;
            case 'mp3':
                $contentType = 'audio/mpeg3';
                break;
            case 'mpg':
            case 'mpeg':
                $contentType = 'video/mpeg';
                break;
            case 'rtf':
                $contentType = 'application/rtf';
                break;
            case 'htm':
            case 'html':
                $contentType = 'text/html';
                break;
            case 'asp':
                $contentType = 'text/asp';
                break;
            case 'wmv':
                $contentType = 'video/x-ms-wmv';
                break;
            case 'webm':
                $contentType = 'video/webm';
                break;
            case 'mp4':
            case 'm4v':
                $contentType = 'video/mp4';
                break;
            case 'ogg':
            case 'ogv':
                $contentType = 'video/ogg';
                break;
            /* case '.ogg': */
            case 'oga':
                $contentType = 'audio/ogg';
                break;
            case 'mov':
                $contentType = 'video/quicktime';
                break;
            case 'srt':
                $contentType = 'text/plain';
                break;
            case 'vtt':
                $contentType = 'text/vtt';
                break;
            default:
                $contentType = 'application/force-download';
        }

        try {
            $this->fileReference->getPublicUrl();
        } // File was deleted.
        catch (\RuntimeException $runtimeException) {
            exit($this->controller->pi_getLL('no_file'));
        }

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $this->fileReference->getName() . '";');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . $this->fileReference->getSize());
        set_time_limit(0);

        echo $this->fileReference->getContents();

        exit();
    }

    /**
     * Check if file has a valid secure hash
     * If not, exit
     *
     * @param FileReference $fileReference
     *
     * @return void
     */
    private function assertValidFile(FileReference $fileReference)
    {
        if (!$this->secureHash) {

            GeneralUtility::devLog(
                'An unallowed attempt has been made to download a file. File: '
                    . $fileReference->getCombinedIdentifier(),
                'tx_overheidsmediaplayer',
                3
            );

            exit('Unallowed attempt to download a file');
        }
    }

    /**
     * Set the secure hash
     *
     * @param boolean $secureHash TRUE if validated
     * @return void
     */
    public function setSecureHash($secureHash)
    {
        $this->secureHash = (boolean)$secureHash;
    }

    /**
     * Set the FileReference
     *
     * @param FileReference $fileReference
     * @return void
     */
    public function setFileReference($fileReference)
    {
        $this->fileReference = $fileReference;
    }
}

