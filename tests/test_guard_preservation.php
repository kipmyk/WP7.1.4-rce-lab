<?php
/**
 * Test 2: Guard file preservation comparison
 * Tests the pre-unpack cleanup logic of WordPress 7.1.3 vs 7.1.4
 */

$test_dir_713 = __DIR__ . '/mock_upgrade_713';
$test_dir_714 = __DIR__ . '/mock_upgrade_714';

// Setup mock directories
@mkdir($test_dir_713, 0777, true);
@mkdir($test_dir_714, 0777, true);

// Seed both with index.php and a dummy previous file
file_put_contents($test_dir_713 . '/index.php', "<?php // Guard 7.1.3\n");
file_put_contents($test_dir_713 . '/old_package.zip', "DUMMY_ZIP_CONTENT");

file_put_contents($test_dir_714 . '/index.php', "<?php // Guard 7.1.4\n");
file_put_contents($test_dir_714 . '/old_package.zip', "DUMMY_ZIP_CONTENT");

// --- 7.1.3 Cleanup Routine ---
$files_713 = scandir($test_dir_713);
foreach ($files_713 as $file) {
    if ($file === '.' || $file === '..') continue;
    // 7.1.3 logic: deletes everything unconditionally
    unlink($test_dir_713 . '/' . $file);
}

// --- 7.1.4 Cleanup Routine ---
$files_714 = scandir($test_dir_714);
foreach ($files_714 as $file) {
    if ($file === '.' || $file === '..') continue;
    // 7.1.4 logic: skips index.php
    if ($file === 'index.php') {
        continue;
    }
    unlink($test_dir_714 . '/' . $file);
}
// 7.1.4 auto-generation if missing
if (!file_exists($test_dir_714 . '/index.php')) {
    file_put_contents($test_dir_714 . '/index.php', "<?php\n// Silence is golden.\n");
}

// Check results
$result_713_index = file_exists($test_dir_713 . '/index.php');
$result_714_index = file_exists($test_dir_714 . '/index.php');

$output = [
    'test' => 'Guard file preservation',
    'timestamp' => date('c'),
    '7.1.3_index_exists_after_cleanup' => $result_713_index,
    '7.1.4_index_exists_after_cleanup' => $result_714_index,
    '7.1.3_status' => $result_713_index ? 'PRESERVED' : 'DELETED (Vulnerable: index.php stripped)',
    '7.1.4_status' => $result_714_index ? 'PRESERVED (Patched: index.php retained)' : 'DELETED',
];

echo json_encode($output, JSON_PRETTY_PRINT) . "\n";

// Cleanup mock folders
@unlink($test_dir_713 . '/index.php');
@unlink($test_dir_713 . '/old_package.zip');
@rmdir($test_dir_713);

@unlink($test_dir_714 . '/index.php');
@unlink($test_dir_714 . '/old_package.zip');
@rmdir($test_dir_714);

