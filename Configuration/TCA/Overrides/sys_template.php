<?php

$_EXTKEY = 'overheidsmediaplayer';

// Add Default TS to Include static (from extensions)
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    $_EXTKEY,
    'Configuration/TypoScript/Video',
    'Default TS Video'
);

// Add Default CSS to Include static (from extensions)
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    $_EXTKEY,
    'Configuration/TypoScript/CSS/',
    'Default CSS'
);