<?php

$_EXTKEY = 'overheidsmediaplayer';

// Add the plugin to the list of plugins in content elements of type 'Insert plugin'
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    [
        'LLL:EXT:overheidsmediaplayer/Resources/Private/Language/locallang_wizicon.xml:wizicon_title_video',
        'overheidsmediaplayer_videocontroller'
    ],
    'list_type',
    $_EXTKEY
);

// Exclude fields from displaying and add FlexForm content
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['overheidsmediaplayer_videocontroller']
    = 'layout,select_key,pages,recursive';

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['overheidsmediaplayer_videocontroller'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'overheidsmediaplayer_videocontroller',
    'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/Video.xml'
);