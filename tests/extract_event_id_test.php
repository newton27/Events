<?php
// Lightweight reference cases for the URL parser. Run inside a bootstrapped UNA test environment.
$cases = array(
    'https://www.facebook.com/events/123456789012345/' => '123456789012345',
    'https://facebook.com/gaymen.online/events/987654321098765' => '987654321098765',
);
foreach ($cases as $url => $expected) {
    if (!preg_match('~(?:^|/)events/(\d{5,32})(?:/|$)~', parse_url($url, PHP_URL_PATH), $match) || $match[1] !== $expected)
        throw new RuntimeException('Failed: ' . $url);
}
echo "URL parser cases passed.\n";

