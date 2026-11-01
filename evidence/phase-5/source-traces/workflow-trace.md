# Phase 5 Source-Level Execution Trace: Core Upgrade Workflow

This document records the exact source code trace of the WordPress core update workflow across **WordPress 7.1.3** and **WordPress 7.1.4**.

---

## Component Trace Matrix

| Stage | Operation | WordPress 7.1.3 Source Location | WordPress 7.1.4 Source Location | Security Relevance |
| :--- | :--- | :--- | :--- | :--- |
| **1** | **Update Initiation** | `wp-admin/includes/class-core-upgrader.php`<br>`Core_Upgrader::upgrade()` (L60–150) | `wp-admin/includes/class-core-upgrader.php`<br>`Core_Upgrader::upgrade()` (L60–150) | Orchestrates download, maintenance mode, unpack, and sync. |
| **2** | **Package Download** | `wp-admin/includes/class-wp-upgrader.php`<br>`WP_Upgrader::download_package()` (L315–344) | `wp-admin/includes/class-wp-upgrader.php`<br>`WP_Upgrader::download_package()` (L315–344) | Retrieves release archive to temporary location. |
| **3** | **Pre-Unpack Cleanup** | `wp-admin/includes/class-wp-upgrader.php`<br>`WP_Upgrader::unpack_package()` (L369–375) | `wp-admin/includes/class-wp-upgrader.php`<br>`WP_Upgrader::unpack_package()` (L384–394) | **7.1.3:** Deletes all files including `index.php`.<br>**7.1.4:** Preserves and generates `index.php`. |
| **4** | **Staging Path Generation** | `wp-admin/includes/class-wp-upgrader.php`<br>`WP_Upgrader::unpack_package()` (L378) | `wp-admin/includes/class-wp-upgrader.php`<br>`WP_Upgrader::get_working_dir_name()` (L354–359, L409) | **7.1.3:** Deterministic (`upgrade/wordpress/`).<br>**7.1.4:** Randomized with 16 alphanumeric characters. |
| **5** | **Archive Extraction** | `wp-admin/includes/class-wp-upgrader.php`<br>`unzip_file( $package, $working_dir )` (L386) | `wp-admin/includes/class-wp-upgrader.php`<br>`unzip_file( $package, $working_dir )` (L417) | Unpacks full archive into working folder. Opens the transient race window. |
| **6** | **Package Unlink** | `wp-admin/includes/class-wp-upgrader.php`<br>`unlink( $package )` (L390) | `wp-admin/includes/class-wp-upgrader.php`<br>`unlink( $package )` (L421) | Deletes temporary zip file after extraction. |
| **7** | **Maintenance Mode** | `wp-admin/includes/update-core.php`<br>`update_core()` (L1630–1633) | `wp-admin/includes/update-core.php`<br>`update_core()` (L1630–1633) | Writes `.maintenance` file to site root. |
| **8** | **File Sync Exclusions** | `wp-admin/includes/update-core.php`<br>`update_core()` (L1549) | `wp-admin/includes/update-core.php`<br>`update_core()` (L1549) | **7.1.3:** `$skip = array( 'wp-content', 'wp-includes/version.php' );`<br>**7.1.4:** Added `'wp-config.php'` to `$skip`. |
| **9** | **Core File Sync** | `wp-admin/includes/update-core.php`<br>`copy_dir( $from . $distro, $to, $skip )` (L1639) | `wp-admin/includes/update-core.php`<br>`copy_dir( $from . $distro, $to, $skip )` (L1639) | Synchronizes unzipped files into production root. |
| **10** | **Staging Directory Purge** | `wp-admin/includes/update-core.php`<br>`$wp_filesystem->delete( $from, true )` (L1785) | `wp-admin/includes/update-core.php`<br>`$wp_filesystem->delete( $from, true )` (L1785) | Closes the transient window by deleting the extraction directory. |

