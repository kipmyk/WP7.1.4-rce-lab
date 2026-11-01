# Source-Level Patch Analysis: WordPress 7.1.3 vs WordPress 7.1.4

## Advisory Context: GHSA-4w3m-c66w-69mg

* **Advisory Reference:** [GHSA-4w3m-c66w-69mg](https://github.com/WordPress/wordpress-develop/security/advisories/GHSA-4w3m-c66w-69mg)
* **Official Title:** Race condition during core updates that could lead to remote code execution
* **Official Severity:** **Low**
* **CVE / CWE:** None assigned in official documentation
* **Reporter:** Umut Bayram
* **Trac Changeset:** `64877`

---

## Source Comparison Matrix

| Field | Protection 1: Path Randomization | Protection 2: Directory Index Protection | Protection 3: Promotion Boundary Exclusion |
| :--- | :--- | :--- | :--- |
| **File** | `wp-admin/includes/class-wp-upgrader.php` | `wp-admin/includes/class-wp-upgrader.php` | `wp-admin/includes/update-core.php` |
| **Function / Method** | `get_working_dir_name( $package )` | `unpack_package( $package, $delete_package )` | `update_core( $from, $to )` |
| **Before (7.1.3)** | Staging folder derived strictly from package name: `basename( basename( $package, '.tmp' ), '.zip' )` (e.g., `upgrade/wordpress/`). | All contents of `wp-content/upgrade/` deleted before unpack, including any defensive `index.php`. | File synchronization copied all files except `wp-content` and `version.php`: `$skip = array( 'wp-content', 'wp-includes/version.php' );` |
| **After (7.1.4)** | New private method appends 16 random characters: `$name . '-' . wp_generate_password( 16, false );` | Explicitly skips deleting `index.php`. Generates an empty `index.php` guard if missing. | Explicitly adds `wp-config.php` to skip list: `$skip = array( 'wp-content', 'wp-includes/version.php', 'wp-config.php' );` |
| **Security Impact** | Reduces predictability of the staging URL. | Prevents web server directory listing from disclosing the randomized directory name. | Prevents rogue or newly written configuration files from crossing into the production root. |
| **Source Evidence** | Lines 346–359 in 7.1.4 | Lines 381–406 in 7.1.4 | Lines 1545–1550 in 7.1.4 |

---

## Detailed Source Observations

### 1. The Staging Directory Randomization
In `class-wp-upgrader.php`, WordPress 7.1.4 added:
```php
private function get_working_dir_name( $package ) {
    // Strip off any .tmp or .zip suffixes.
    $name = basename( basename( $package, '.tmp' ), '.zip' );

    return $name . '-' . wp_generate_password( 16, false );
}
```
* **Observed Mechanism:** Uses `wp_generate_password( 16, false )` to draw from alphanumeric characters `[a-zA-Z0-9]`.
* **Security Function:** By introducing $62^{16} \approx 4.76 \times 10^{28}$ possible directory names, an unauthenticated attacker cannot predict the exact URL during the transient update window.

### 2. The Directory Listing Guard (`index.php`)
In `class-wp-upgrader.php`, the pre-unpack cleanup loop was modified:
```php
foreach ( $upgrade_files as $file ) {
    if ( 'index.php' === $file['name'] ) {
        continue;
    }
    $wp_filesystem->delete( $upgrade_folder . $file['name'], true );
}
...
if ( ! $wp_filesystem->exists( $upgrade_folder . 'index.php' ) ) {
    $wp_filesystem->put_contents( $upgrade_folder . 'index.php', "<?php\n// Silence is golden.\n", FS_CHMOD_FILE );
}
```
* **Observed Mechanism:** Preserves existing `index.php` files in `wp-content/upgrade/` and writes a fresh one if missing.
* **Security Function:** Neutralizes directory listing on web servers that lack `Options -Indexes` or `autoindex off`.
* **Important Limitation:** An `index.php` file prevents directory browsing; it does not block direct HTTP requests to specific files if their paths are already known.

### 3. File Promotion Exclusion (`wp-config.php`)
In `update-core.php`, line 1549:
```php
/*
 * Never copy a wp-config.php, which could have been written to the working directory.
 */
$skip = array( 'wp-content', 'wp-includes/version.php', 'wp-config.php' );
```
* **Observed Mechanism:** Explicitly instructs `copy_dir()` to skip `wp-config.php`.
* **Security Function:** Even if an attacker succeeded in creating a `wp-config.php` inside the staging directory via `setup-config.php`, `copy_dir()` refuses to copy it into the live site root.

---

## Distinction: Verified Code Changes vs. Exploitation Assumptions

1. **Verified Code Changes:**
   * Working directory name entropy was introduced.
   * `index.php` is preserved and generated.
   * `wp-config.php` is excluded from core copy operations.

2. **Exploitation Dependencies & Assumptions:**
   * Exploitation relies on the web server permitting PHP execution inside `wp-content/upgrade/`. On servers configured with defensive rules (e.g., blocking `.php` inside `wp-content/uploads/` and `wp-content/upgrade/`), the execution of `setup-config.php` is blocked regardless of WordPress core version.
   * Exploitation requires winning a race condition during the active extraction window.
