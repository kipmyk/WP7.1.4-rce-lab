<?php
/**
 * Upgrade Lifecycle Observer Harness
 * Non-invasive observation of filesystem events in wp-content/upgrade/
 */

$wp_content_dir = $argv[1] ?? '';
if (empty($wp_content_dir) || !is_dir($wp_content_dir)) {
    fwrite(STDERR, "Usage: php observe_upgrade_lifecycle.php <path_to_wp_content> [log_file]\n");
    exit(1);
}

$upgrade_dir = rtrim($wp_content_dir, '/') . '/upgrade';
$log_file = $argv[2] ?? 'upgrade_lifecycle_events.json';
$timeout_seconds = 60;
$start_time = microtime(true);

$events = [];
$observed_dirs = [];
$active_working_dir = null;
$created_at = null;
$deleted_at = null;
$files_detected = [];

$events[] = [
    'time' => microtime(true),
    'iso' => date('c'),
    'event' => 'OBSERVER_STARTED',
    'upgrade_dir' => $upgrade_dir
];

while ((microtime(true) - $start_time) < $timeout_seconds) {
    if (file_exists($upgrade_dir) && is_dir($upgrade_dir)) {
        $items = scandir($upgrade_dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $full_path = $upgrade_dir . '/' . $item;
            
            if (is_dir($full_path) && !isset($observed_dirs[$item])) {
                $now = microtime(true);
                $observed_dirs[$item] = $now;
                $active_working_dir = $item;
                $created_at = $now;
                
                $events[] = [
                    'time' => $now,
                    'iso' => date('c'),
                    'event' => 'STAGING_DIR_CREATED',
                    'dir_name' => $item,
                    'full_path' => $full_path,
                    'elapsed_since_start_ms' => round(($now - $start_time) * 1000, 2)
                ];
            }
        }
        
        // If a directory was created, check for its contents and eventual deletion
        if ($active_working_dir) {
            $current_full_path = $upgrade_dir . '/' . $active_working_dir;
            if (file_exists($current_full_path) && is_dir($current_full_path)) {
                // Check for setup-config.php or key files
                $installer_path = $current_full_path . '/wordpress/wp-admin/setup-config.php';
                if (!isset($files_detected['setup-config']) && file_exists($installer_path)) {
                    $now = microtime(true);
                    $files_detected['setup-config'] = $now;
                    $events[] = [
                        'time' => $now,
                        'iso' => date('c'),
                        'event' => 'SETUP_CONFIG_DETECTED',
                        'path' => $installer_path,
                        'elapsed_since_dir_created_ms' => round(($now - $created_at) * 1000, 2)
                    ];
                }
            } else {
                // Directory has been deleted
                $now = microtime(true);
                $deleted_at = $now;
                $events[] = [
                    'time' => $now,
                    'iso' => date('c'),
                    'event' => 'STAGING_DIR_DELETED',
                    'dir_name' => $active_working_dir,
                    'total_lifespan_ms' => round(($deleted_at - $created_at) * 1000, 2)
                ];
                break; // Update complete
            }
        }
    }
    usleep(10000); // Poll every 10ms
}

$events[] = [
    'time' => microtime(true),
    'iso' => date('c'),
    'event' => 'OBSERVER_FINISHED',
    'total_observed_lifespan_ms' => ($created_at && $deleted_at) ? round(($deleted_at - $created_at) * 1000, 2) : null
];

file_put_contents($log_file, json_encode($events, JSON_PRETTY_PRINT));
echo json_encode($events, JSON_PRETTY_PRINT) . "\n";
