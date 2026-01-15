<?php

// List of Province IDs (BPS Standard)
$provinceIds = [
    11,
    12,
    13,
    14,
    15,
    16,
    17,
    18,
    19,
    21, // Sumatera
    31,
    32,
    33,
    34,
    35,
    36, // Jawa
    51,
    52,
    53, // Bali & Nusa Tenggara
    61,
    62,
    63,
    64,
    65, // Kalimantan
    71,
    72,
    73,
    74,
    75,
    76, // Sulawesi
    81,
    82, // Maluku
    91,
    92,
    93,
    94,
    95,
    96 // Papua
];

$allCities = [];

echo "Starting download of cities...\n";

$context = stream_context_create([
    "ssl" => [
        "verify_peer" => false,
        "verify_peer_name" => false,
    ],
    "http" => [
        "user_agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
        "timeout" => 10
    ]
]);

foreach ($provinceIds as $id) {
    $url = "https://www.emsifa.com/api-wilayah-indonesia/api/regencies/{$id}.json";
    echo "Fetching: {$url} ... ";

    try {
        $data = @file_get_contents($url, false, $context);

        if ($data === false) {
            echo "FAILED.\n";
            continue;
        }

        $cities = json_decode($data, true);
        if ($cities) {
            $allCities = array_merge($allCities, $cities);
            echo "OK (" . count($cities) . " cities)\n";
        } else {
            echo "INVALID JSON.\n";
        }
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

$savePath = __DIR__ . '/public/cities.json';
// Using public path temporarily or database path?
// Only app folders are guaranteed writable?
// Let's use the location we decided: database/seeders/data/cities.json
$savePath = __DIR__ . '/database/seeders/data/cities.json';

if (empty($allCities)) {
    echo "No cities downloaded. Check your internet connection.\n";
    exit(1);
}

$bytes = file_put_contents($savePath, json_encode($allCities, JSON_PRETTY_PRINT));

echo "\nDownload Complete!\n";
echo "Total Cities: " . count($allCities) . "\n";
echo "Saved to: {$savePath}\n";
