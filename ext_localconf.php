<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

// Avoid webm being indexed as 'application/octet-stream'
$GLOBALS['TYPO3_CONF_VARS']['SYS']['FileInfo']['fileExtensionToMimeType']['webm'] = 'video/webm';