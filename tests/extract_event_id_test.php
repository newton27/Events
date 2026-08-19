<?php

if (!defined('BX_DOL'))
    define('BX_DOL', true);
if (!class_exists('BxDolModule'))
    class BxDolModule {}

require_once dirname(__DIR__) . '/modules/newton/gmo_fb_events/classes/GmoFbEventsModule.php';

$oModule = new GmoFbEventsModule();
$aValidCases = array(
    'https://www.facebook.com/events/123456789012345/' => '123456789012345',
    'https://facebook.com/gaymen.online/events/987654321098765' => '987654321098765',
    'https://m.facebook.com/events/12345?ref=share' => '12345',
);
foreach ($aValidCases as $sUrl => $sExpected) {
    if ($oModule->extractEventId($sUrl) !== $sExpected)
        throw new RuntimeException('Failed valid URL: ' . $sUrl);
}

$aInvalidCases = array(
    'https://example.com/events/1234567890/',
    'https://facebook.com/events/not-a-number/',
    'https://facebook.com.evil.example/events/1234567890/',
    'not a URL',
);
foreach ($aInvalidCases as $sUrl) {
    try {
        $oModule->extractEventId($sUrl);
        throw new RuntimeException('Accepted invalid URL: ' . $sUrl);
    } catch (InvalidArgumentException $oException) {
        // Expected.
    }
}

echo "URL parser cases passed.\n";
