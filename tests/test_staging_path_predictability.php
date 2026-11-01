<?php
/**
 * Test 3: Staging directory path predictability
 * Compares directory naming in WordPress 7.1.3 vs 7.1.4
 */

$package = 'wordpress.zip';

// 7.1.3 Implementation:
function get_working_dir_713($package) {
    return basename(basename($package, '.tmp'), '.zip');
}

// 7.1.4 Implementation:
function get_working_dir_714($package) {
    $name = basename(basename($package, '.tmp'), '.zip');
    // Emulates wp_generate_password(16, false) using random_int as in wp_rand
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $suffix = '';
    for ($i = 0; $i < 16; $i++) {
        $suffix .= substr($chars, random_int(0, strlen($chars) - 1), 1);
    }
    return $name . '-' . $suffix;
}

$samples_713 = [];
for ($i = 0; $i < 10; $i++) {
    $samples_713[] = get_working_dir_713($package);
}

$samples_714 = [];
for ($i = 0; $i < 10; $i++) {
    $samples_714[] = get_working_dir_714($package);
}

$unique_713 = count(array_unique($samples_713));
$unique_714 = count(array_unique($samples_714));

$output = [
    'test' => 'Staging directory predictability',
    'timestamp' => date('c'),
    'package_tested' => $package,
    '7.1.3_sample_runs' => $samples_713,
    '7.1.3_unique_count' => $unique_713,
    '7.1.3_evaluation' => ($unique_713 === 1) ? 'Deterministic (Predictable)' : 'Random',
    '7.1.4_sample_runs' => $samples_714,
    '7.1.4_unique_count' => $unique_714,
    '7.1.4_evaluation' => ($unique_714 === count($samples_714)) ? 'Randomized (16-char alphanumeric suffix)' : 'Collision observed',
    'source_analysis' => [
        'function' => 'get_working_dir_name($package)',
        'generator' => 'wp_generate_password(16, false)',
        'prng_call' => 'wp_rand() -> random_int() with fallback to md5/sha1 hash chain',
        'entropy_basis' => '62 characters (a-z, A-Z, 0-9) ^ 16 positions = ~4.76e28 theoretical possibilities',
        'limitations' => 'Actual unpredictability depends on PHP random_int entropy source and CSPRNG availability on the host OS; observation of 16 characters does not itself guarantee absence of PRNG bias or seed weaknesses.'
    ]
];

echo json_encode($output, JSON_PRETTY_PRINT) . "\n";

