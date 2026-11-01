<?php
/**
 * Test 6: wp-config.php exclusion during file synchronization
 * Tests the copy_dir logic comparing 7.1.3 vs 7.1.4 $skip configurations
 */

// Simulated copy_dir logic from wp-admin/includes/class-wp-filesystem-base.php
function mock_copy_dir($from, $to, $skip_list = []) {
    @mkdir($to, 0777, true);
    $dir = opendir($from);
    while (false !== ($file = readdir($dir))) {
        if ($file === '.' || $file === '..') continue;
        if (in_array($file, $skip_list, true)) {
            continue; // Skipped per exclusion policy
        }
        $src = $from . '/' . $file;
        $dst = $to . '/' . $file;
        if (is_dir($src)) {
            mock_copy_dir($src, $dst, $skip_list);
        } else {
            copy($src, $dst);
        }
    }
    closedir($dir);
}

$mock_staging = __DIR__ . '/mock_staging';
$mock_dest_713 = __DIR__ . '/mock_dest_713';
$mock_dest_714 = __DIR__ . '/mock_dest_714';

// Setup directories
@mkdir($mock_staging, 0777, true);
@mkdir($mock_dest_713, 0777, true);
@mkdir($mock_dest_714, 0777, true);

// Seed staging with core file and a benign dummy wp-config.php
file_put_contents($mock_staging . '/index.php', "<?php // Staged core file\n");
file_put_contents($mock_staging . '/wp-config.php', "<?php // BENIGN_TEST_DUMMY_CONFIG\n");

// Seed destinations with legitimate existing configuration
file_put_contents($mock_dest_713 . '/wp-config.php', "<?php // LEGITIMATE_PRODUCTION_CONFIG\n");
file_put_contents($mock_dest_714 . '/wp-config.php', "<?php // LEGITIMATE_PRODUCTION_CONFIG\n");

// 7.1.3 Skip policy (update-core.php line 1549):
$skip_713 = ['wp-content', 'wp-includes/version.php'];

// 7.1.4 Skip policy (update-core.php line 1549):
$skip_714 = ['wp-content', 'wp-includes/version.php', 'wp-config.php'];

// Execute synchronization
mock_copy_dir($mock_staging, $mock_dest_713, $skip_713);
mock_copy_dir($mock_staging, $mock_dest_714, $skip_714);

$dest_713_content = file_get_contents($mock_dest_713 . '/wp-config.php');
$dest_714_content = file_get_contents($mock_dest_714 . '/wp-config.php');

$overwritten_713 = str_contains($dest_713_content, 'BENIGN_TEST_DUMMY_CONFIG');
$overwritten_714 = str_contains($dest_714_content, 'BENIGN_TEST_DUMMY_CONFIG');

$output = [
    'test' => 'wp-config.php exclusion during file synchronization',
    'timestamp' => date('c'),
    '7.1.3_skip_list' => $skip_713,
    '7.1.3_wp_config_overwritten' => $overwritten_713,
    '7.1.3_result' => $overwritten_713 ? 'OVERWRITTEN (Vulnerable: Staged config copied into target)' : 'PRESERVED',
    '7.1.4_skip_list' => $skip_714,
    '7.1.4_wp_config_overwritten' => $overwritten_714,
    '7.1.4_result' => $overwritten_714 ? 'OVERWRITTEN' : 'PROTECTED (Patched: wp-config.php excluded from copy)',
    'final_dest_713_content' => trim($dest_713_content),
    'final_dest_714_content' => trim($dest_714_content),
];

echo json_encode($output, JSON_PRETTY_PRINT) . "\n";

// Cleanup mock directories
@unlink($mock_staging . '/index.php');
@unlink($mock_staging . '/wp-config.php');
@rmdir($mock_staging);

@unlink($mock_dest_713 . '/index.php');
@unlink($mock_dest_713 . '/wp-config.php');
@rmdir($mock_dest_713);

@unlink($mock_dest_714 . '/index.php');
@unlink($mock_dest_714 . '/wp-config.php');
@rmdir($mock_dest_714);

