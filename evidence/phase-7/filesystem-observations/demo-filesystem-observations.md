# Phase 7 Demonstration Filesystem Observations

Direct observations of filesystem mechanics captured during the repeatable demonstration harness:

---

## 1. Directory Naming Observations
* **WordPress 7.1.3 Implementation:**
  * Package: `wordpress.zip`
  * Function: `basename( basename( $package, '.tmp' ), '.zip' )`
  * Generated Directory: `wordpress`
  * Outcome: 100% deterministic across all runs.
* **WordPress 7.1.4 Implementation:**
  * Package: `wordpress.zip`
  * Function: `get_working_dir_name()`
  * Generated Directories:
    * `wordpress-Op5j77tkBxG4xPaY`
    * `wordpress-svOuD8Z7rr5yd7k1`
    * `wordpress-4zMQWYNB32Rmad33`
  * Outcome: Randomized 16-character alphanumeric suffix generated per extraction.

---

## 2. Guard File (`index.php`) Observations
* **WordPress 7.1.3 Implementation:**
  * Pre-unpack cleanup routine purged all files unconditionally.
  * Verified state: `index.php` was deleted, leaving directory bare before unzipping.
* **WordPress 7.1.4 Implementation:**
  * Pre-unpack cleanup routine explicitly bypassed `index.php`.
  * Verified state: `index.php` was preserved and auto-generated with `// Silence is golden.` before unzipping.

---

## 3. Configuration Promotion Boundary (`wp-config.php`)
* **WordPress 7.1.3 Implementation:**
  * `update-core.php` line 1549: `$skip = array( 'wp-content', 'wp-includes/version.php' );`
  * Verified state: Synthetic `wp-config.php` in staging was copied by `copy_dir()`, overwriting destination.
* **WordPress 7.1.4 Implementation:**
  * `update-core.php` line 1549: `$skip = array( 'wp-content', 'wp-includes/version.php', 'wp-config.php' );`
  * Verified state: Synthetic `wp-config.php` in staging was strictly excluded, leaving destination config untouched.

