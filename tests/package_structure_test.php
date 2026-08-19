<?php

/**
 * Verify that the repository contains a complete UNA-installable module package.
 */

$sRoot = dirname(__DIR__);
$sModule = $sRoot . '/modules/newton/gmo_fb_events';
$aRequiredFiles = array(
    'action.php',
    'classes/GmoFbEventsConfig.php',
    'classes/GmoFbEventsDb.php',
    'classes/GmoFbEventsModule.php',
    'classes/GmoFbEventsStudioPage.php',
    'classes/GmoFbEventsTemplate.php',
    'install/config.php',
    'install/installer.php',
    'install/langs/en.xml',
    'install/sql/install.sql',
    'install/sql/uninstall.sql',
    'install/sql/enable.sql',
    'install/sql/disable.sql',
    'studio-icon.svg',
    'template/images/icons/studio-icon.svg',
);

foreach ($aRequiredFiles as $sRelativePath) {
    $sPath = $sModule . '/' . $sRelativePath;
    if (!is_file($sPath) || filesize($sPath) === 0)
        throw new RuntimeException('Missing or empty module file: ' . $sRelativePath);
}

$sConfig = file_get_contents($sModule . '/install/config.php');
$aConfigMarkers = array(
    "'name' => 'gmo_fb_events'",
    "'version' => '2.0.7'",
    "'home_dir' => 'newton/gmo_fb_events/'",
    "'class_prefix' => 'GmoFbEvents'",
    "'dependencies' => array('bx_events' => '15.0.0')",
);
foreach ($aConfigMarkers as $sMarker) {
    if (strpos($sConfig, $sMarker) === false)
        throw new RuntimeException('Missing config marker: ' . $sMarker);
}

$sModuleCode = file_get_contents($sModule . '/classes/GmoFbEventsModule.php');
$sActionCode = file_get_contents($sModule . '/action.php');
if (strpos($sModuleCode . $sActionCode, 'gmo_fb_events_page_token') !== false)
    throw new RuntimeException('Obsolete Facebook Page token code is still present.');
if (strpos($sActionCode, 'UPDATE `sys_') !== false)
    throw new RuntimeException('The importer page must not modify UNA schema metadata.');

$sLanguageXml = file_get_contents($sModule . '/install/langs/en.xml');
if (function_exists('simplexml_load_string') && simplexml_load_string($sLanguageXml) === false)
    throw new RuntimeException('The English language XML is invalid.');

echo "UNA module package structure passed.\n";
