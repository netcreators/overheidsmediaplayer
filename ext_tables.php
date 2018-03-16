<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

// Add icon and description to new content wizard
if (TYPO3_MODE === 'BE') {
    $GLOBALS['TBE_MODULES_EXT']['xMOD_db_new_content_el']['addElClasses']['Netcreators\\Overheidsmediaplayer\\System\\WizardIcon']
        = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath(
            $_EXTKEY
        ) . 'Classes/System/WizardIcon.php';
}
