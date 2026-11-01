# Phase 5 Filesystem Observations: Upgrade Lifecycle Comparison

## Empirical Comparison: WordPress 7.1.3 vs WordPress 7.1.4

During non-invasive lifecycle observation of legitimate core update operations, the following concrete filesystem states were recorded:

| Parameter | WordPress 7.1.3 Run | WordPress 7.1.4 Run | Security Significance |
| :--- | :--- | :--- | :--- |
| **Package Processed** | `test-update.zip` | `test-update.zip` | Identical inputs |
| **Staging Directory Path** | `wp-content/upgrade/test-update` | `wp-content/upgrade/test-update-weIdh9kF3Kd71Nkj` | **Divergence:** 7.1.3 is 100% deterministic; 7.1.4 appends 16 random characters. |
| **Elapsed to Dir Creation** | 4,275.83 ms | 5,057.93 ms | Unpack preparation time |
| **`setup-config.php` Appears** | +816.61 ms after dir creation | +705.30 ms after dir creation | Archive extraction duration |
| **Total Staging Lifespan** | **12,330.89 ms** (~12.33 s) | **11,321.29 ms** (~11.32 s) | Active transient window |
| **Installer Exposure Window** | **11,514.28 ms** (~11.51 s) | **10,615.99 ms** (~10.62 s) | Window where `setup-config.php` is physically on disk |
| **Pre-Unpack Guard File** | Stripped/Deleted | Preserved and written (`// Silence is golden.`) | Prevents directory index leakage |
| **File Sync Exclusion** | `wp-config.php` **not** excluded | `wp-config.php` **strictly** excluded | Prevents promotion of staged config |

---

## Detailed Event Transcripts

### WordPress 7.1.3 Lifecycle Transcript
```json
[
    {
        "event": "OBSERVER_STARTED",
        "upgrade_dir": "/var/www/html/wp-content/upgrade"
    },
    {
        "event": "STAGING_DIR_CREATED",
        "dir_name": "test-update",
        "full_path": "/var/www/html/wp-content/upgrade/test-update",
        "elapsed_since_start_ms": 4275.83
    },
    {
        "event": "SETUP_CONFIG_DETECTED",
        "path": "/var/www/html/wp-content/upgrade/test-update/wordpress/wp-admin/setup-config.php",
        "elapsed_since_dir_created_ms": 816.61
    },
    {
        "event": "STAGING_DIR_DELETED",
        "dir_name": "test-update",
        "total_lifespan_ms": 12330.89
    }
]
```

### WordPress 7.1.4 Lifecycle Transcript
```json
[
    {
        "event": "OBSERVER_STARTED",
        "upgrade_dir": "/var/www/html/wp-content/upgrade"
    },
    {
        "event": "STAGING_DIR_CREATED",
        "dir_name": "test-update-weIdh9kF3Kd71Nkj",
        "full_path": "/var/www/html/wp-content/upgrade/test-update-weIdh9kF3Kd71Nkj",
        "elapsed_since_start_ms": 5057.93
    },
    {
        "event": "SETUP_CONFIG_DETECTED",
        "path": "/var/www/html/wp-content/upgrade/test-update-weIdh9kF3Kd71Nkj/wordpress/wp-admin/setup-config.php",
        "elapsed_since_dir_created_ms": 705.3
    },
    {
        "event": "STAGING_DIR_DELETED",
        "dir_name": "test-update-weIdh9kF3Kd71Nkj",
        "total_lifespan_ms": 11321.29
    }
]
```

