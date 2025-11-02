<?php
// This single line loads the entire Cloudinary library!
require_once __DIR__ . '/../vendor/autoload.php';

use Cloudinary\Configuration\Configuration;

// Configure your Cloudinary credentials
// !! IMPORTANT: Use the NEW API Secret you generated !!
Configuration::instance([
    'cloud' => [
        'cloud_name' => 'dwehpusmc',
        'api_key'    => '853675995316996',
        'api_secret' => 'CuPI0v66oGtt7gph_JYMEXGXXhI', // <-- PASTE YOUR NEW SECRET HERE
    ],
    'url' => [
        'secure' => true
    ]
]);
?>