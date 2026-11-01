# Phase 4: Controlled Security Testing Results Report

## Executive Summary
This report documents the results of 8 controlled, non-destructive security property tests conducted on **November 1, 2026**, comparing **WordPress 7.1.3 (vulnerable)** and **WordPress 7.1.4 (patched)** in isolated Docker environments.

The objective was to validate observable behavioral and source-level mechanics associated with advisory **[GHSA-4w3m-c66w-69mg](https://github.com/WordPress/wordpress-develop/security/advisories/GHSA-4w3m-c66w-69mg)**, establishing which security properties changed and whether they behave as expected. 

> **Important Analytical Boundary:** None of these tests attempted to execute an unauthenticated exploit chain, complete an installer wizard, connect to an external database, or achieve remote code execution. A pass in an individual defensive property test validates a specific hardening control; it does not claim end-to-end vulnerability reproduction or total threat elimination.

---

## Comparative Test Results Matrix

| Test ID | Security Property Tested | WordPress 7.1.3 Result | WordPress 7.1.4 Result | Property Status | Finding Classification |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **01** | **Directory Listing** | `403 Forbidden` without index; `200 OK` (0 B) with index. | `403 Forbidden` without index; `200 OK` (0 B) with index. | Neutral (Server-Level) | Confirmed Observation: 403 without `index.php` is enforced by Apache `Options -Indexes`, not core. |
| **02** | **Guard File Preservation** | `index.php` is deleted during `clean_working_dir()`. | `index.php` is explicitly preserved and auto-generated. | **Divergent** | Confirmed Code Mechanic: 7.1.4 prevents stripping guard files during cleanup. |
| **03** | **Staging Path Predictability** | Deterministic: `upgrade/wordpress/` on all runs. | Randomized: 16-character alphanumeric suffix generated per run. | **Divergent** | Confirmed Code Mechanic: 7.1.4 eliminates static path enumeration via CSPRNG suffix. |
| **04** | **Direct Static File Access** | Accessible: HTTP `200 OK` returned for known probe file. | Accessible: HTTP `200 OK` returned for known probe file. | Identical | Confirmed Observation: Direct HTTP file access in `wp-content/upgrade/` is permitted by default. |
| **05** | **PHP Execution Behavior** | Active: Executed PHP probe (`200 OK`, marker rendered). | Active: Executed PHP probe (`200 OK`, marker rendered). | Identical | Confirmed Observation: Apache/PHP executes scripts inside `wp-content/upgrade/` by default. |
| **06** | **`wp-config.php` Exclusion** | Staged `wp-config.php` copied to destination root. | Staged `wp-config.php` explicitly skipped by `copy_dir()`. | **Divergent** | Confirmed Code Mechanic: 7.1.4 enforces strict file promotion boundary. |
| **07** | **Temporary File Cleanup** | Archive unlinked post-unpack; folder deleted post-sync. | Archive unlinked post-unpack; folder deleted post-sync. | Identical | Confirmed Code Mechanic: Temporary folder remains exposed during unpack-to-sync window. |
| **08** | **Upgrade Integrity** | Pre-upgrade version `7.1.3` | Successfully upgraded to `7.1.4` (`200 OK`, no regressions). | **Verified** | Confirmed Operational Integrity: Patched upgrader completes legitimate update cleanly. |

---

## Detailed Test Case Records

### Test 1: Directory Listing & Index Guarding
* **Test ID:** `01-directory-listing`
* **Objective:** Determine whether `wp-content/upgrade/` permits directory browsing with and without an `index.php` guard.
* **Preconditions:** Containers active; `wp-content/upgrade/` created.
* **Procedure:**
  1. Remove `index.php` from `wp-content/upgrade/` and issue `GET /wp-content/upgrade/`.
  2. Place `index.php` (`<?php // Silence is golden.`) in `wp-content/upgrade/` and issue `GET /wp-content/upgrade/`.
* **Observed HTTP Responses:**
  * **Without `index.php`:** Both instances returned `HTTP/1.1 403 Forbidden` (`Server: Apache/2.4.69 (Debian)`).
  * **With `index.php`:** Both instances returned `HTTP/1.1 200 OK` (`Content-Length: 0`, empty body).
* **Evaluation:** **Pass / Contextual**.
* **Distinction & Limitation:** The `403 Forbidden` response in absence of `index.php` is driven by Apache’s default `Options -Indexes` configuration in Debian, **not** by WordPress application logic. On servers where `Options +Indexes` or Nginx `autoindex on` is enabled, an absent `index.php` would expose the directory contents.

---

### Test 2: Guard File Preservation During Cleanup
* **Test ID:** `02-guard-file-preservation`
* **Objective:** Verify whether the pre-unpack cleanup routine in `unpack_package()` preserves or purges `index.php`.
* **Preconditions:** Seeded mock upgrade directory with `index.php` and dummy previous upgrade files.
* **Procedure:** Executed the exact cleanup loops from 7.1.3 and 7.1.4:
  * 7.1.3: Unconditional `unlink` for all entries in directory.
  * 7.1.4: Conditional check `if ( 'index.php' === $file['name'] ) continue;` followed by `!exists('index.php')` auto-creation.
* **Observed Results:**
  * **WordPress 7.1.3:** `index.php` was **deleted** (`file_exists: false`).
  * **WordPress 7.1.4:** `index.php` was **preserved** (`file_exists: true`).
* **Evaluation:** **Pass / Hardening Confirmed**.
* **Raw Evidence:** [`evidence/phase-4-test-results/02-guard-file-preservation.json`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-4-test-results/02-guard-file-preservation.json).

---

### Test 3: Staging Directory Naming Predictability
* **Test ID:** `03-staging-path-predictability`
* **Objective:** Compare the predictability of the working directory name generated during package extraction.
* **Preconditions:** Input archive `wordpress.zip`.
* **Procedure:** Executed 10 sampling iterations of the naming functions from 7.1.3 and 7.1.4.
* **Observed Results:**
  * **WordPress 7.1.3:** Generated `wordpress` on 10 out of 10 runs (100% deterministic).
  * **WordPress 7.1.4:** Generated 10 unique directory names with 16-character alphanumeric suffixes:
    * `wordpress-tl3YaIVvhkobyJVv`
    * `wordpress-3vhfGEIWHERcDqUc`
    * `wordpress-dOPEdnIhKav6HKyU`
    * ... (10/10 unique).
* **PRNG Source Inspection:**
  * `get_working_dir_name()` calls `wp_generate_password(16, false)`.
  * `wp_generate_password()` invokes `wp_rand(0, 61)`.
  * `wp_rand()` calls PHP's cryptographically secure pseudo-random number generator `random_int()`, falling back to an internal hash chain only if `random_int()` throws an exception.
* **Evaluation:** **Pass / Entropy Introduced**.
* **Limitation:** Observation of 16 random characters demonstrates path entropy; it does not constitute mathematical proof of CSPRNG perfection across all possible host platforms or legacy PHP runtimes.
* **Raw Evidence:** [`evidence/phase-4-test-results/03-staging-path-predictability.json`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-4-test-results/03-staging-path-predictability.json).

---

### Test 4: Direct Static File Access
* **Test ID:** `04-direct-file-access`
* **Objective:** Verify whether known static files inside `wp-content/upgrade/` are directly accessible over HTTP.
* **Preconditions:** Harmless probe file `probe.txt` placed in `wp-content/upgrade/`.
* **Procedure:** Requested `http://127.0.0.1:8081/wp-content/upgrade/probe.txt` and `http://127.0.0.1:8082/wp-content/upgrade/probe.txt`.
* **Observed Results:** Both returned `HTTP/1.1 200 OK` (`Content-Type: text/plain`) serving the probe string. The probe was removed immediately.
* **Evaluation:** **Confirmed Condition**.
* **Significance:** Demonstrates that `wp-content/upgrade/` is served directly by the web server. Guarding `index.php` prevents listing, but does not prevent direct access to known file paths.
* **Raw Evidence:** [`evidence/phase-4-test-results/04-direct-file-access-vulnerable.txt`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-4-test-results/04-direct-file-access-vulnerable.txt).

---

### Test 5: PHP Script Execution Behavior
* **Test ID:** `05-php-execution`
* **Objective:** Determine whether the web server executes PHP scripts located within `wp-content/upgrade/`.
* **Preconditions:** Harmless test probe `probe.php` containing only:
  ```php
  <?php echo 'PROBE_PHP_EXEC_ACTIVE_713'; ?>
  ```
* **Procedure:** Requested `http://127.0.0.1:8081/wp-content/upgrade/probe.php` and `http://127.0.0.1:8082/wp-content/upgrade/probe.php`.
* **Observed Results:**
  * Both instances returned `HTTP/1.1 200 OK` (`Server: Apache/2.4.69`, `X-Powered-By: PHP/8.2.35`).
  * Body returned: `PROBE_PHP_EXEC_ACTIVE_713` and `PROBE_PHP_EXEC_ACTIVE_714`.
  * The probe file was immediately deleted after recording.
* **Evaluation:** **Confirmed Condition**.
* **Significance:** Default Apache configurations execute PHP in `wp-content/upgrade/`. This confirms that server-level execution defense (Phase 5) is a vital layer of defense-in-depth.
* **Raw Evidence:** [`evidence/phase-4-test-results/05-php-execution-vulnerable.txt`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-4-test-results/05-php-execution-vulnerable.txt).

---

### Test 6: `wp-config.php` File Exclusion
* **Test ID:** `06-wp-config-exclusion`
* **Objective:** Validate that `copy_dir()` in 7.1.4 refuses to copy `wp-config.php` from a staging directory into the site root.
* **Preconditions:** Seeded mock staging folder with a benign dummy `wp-config.php` (`// BENIGN_TEST_DUMMY_CONFIG`) and seeded destination root with an existing legitimate configuration (`// LEGITIMATE_PRODUCTION_CONFIG`).
* **Procedure:** Executed `copy_dir()` with the 7.1.3 skip array (`wp-content`, `version.php`) vs the 7.1.4 skip array (`wp-content`, `version.php`, `wp-config.php`).
* **Observed Results:**
  * **WordPress 7.1.3:** Destination `wp-config.php` was **overwritten** with the dummy staging file.
  * **WordPress 7.1.4:** Destination `wp-config.php` was **protected**; `copy_dir()` skipped the file, leaving the legitimate config intact.
* **Evaluation:** **Pass / Security Boundary Enforced**.
* **Raw Evidence:** [`evidence/phase-4-test-results/06-wp-config-exclusion.json`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-4-test-results/06-wp-config-exclusion.json).

---

### Test 7: Temporary File Cleanup Behavior
* **Test ID:** `07-temporary-cleanup`
* **Objective:** Validate the lifecycle of temporary upgrade files and directories.
* **Preconditions:** Mock upgrade archive and extraction folder.
* **Procedure:** Traced cleanup logic across unpack and finalize stages:
  1. Unpack stage: `unlink( $package )` when `$delete_package = true`.
  2. Finalize stage: `$wp_filesystem->delete( $from, true )` in `update-core.php`.
* **Observed Results:**
  * Package archive is unlinked immediately upon extraction.
  * Working directory remains alive while files are copied to the root.
  * Working directory is deleted upon finalization.
* **Evaluation:** **Pass / Race Window Confirmed**.
* **Significance:** Confirms that a transient window of exposure naturally exists between extraction and synchronization finalization.
* **Raw Evidence:** [`evidence/phase-4-test-results/07-temporary-cleanup.json`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-4-test-results/07-temporary-cleanup.json).

---

### Test 8: Legitimate Core Upgrade Integrity
* **Test ID:** `08-upgrade-integrity`
* **Objective:** Confirm that legitimate core update operations complete successfully under the patched logic.
* **Preconditions:** WordPress 7.1.3 running at `http://127.0.0.1:8081/`; full snapshot taken.
* **Procedure:**
  1. Executed `wp core update` using the local 7.1.4 release package via WP-CLI inside the isolated container network.
  2. Verified exit code, version string, database schema, and site availability.
  3. Restored vulnerable environment from snapshot back to 7.1.3 baseline.
* **Observed Results:**
  * `Unpacking the update... Cleaning up files... Success: WordPress updated successfully.`
  * New reported version: `7.1.4`.
  * HTTP root response: `HTTP/1.1 200 OK`.
  * Vulnerable container was restored cleanly to `7.1.3` baseline (`$wp_version = '7.1.3'`).
* **Evaluation:** **Pass / Zero Functional Regressions**.
* **Raw Evidence:** [`evidence/phase-4-test-results/08-upgrade-integrity.txt`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-4-test-results/08-upgrade-integrity.txt).

---

## Technical Summary of Findings

1. **What Changed in 7.1.4:**
   * Working directory name entropy was added (16-char random token).
   * Guard `index.php` files are preserved and created.
   * `wp-config.php` is strictly excluded from core file synchronization.
2. **What Remained Unchanged:**
   * In standard Apache/PHP environments, `wp-content/upgrade/` remains web-accessible.
   * PHP execution inside `wp-content/upgrade/` is permitted by default.
3. **Defensive Conclusion:**
   The patches in WordPress 7.1.4 successfully address path predictability and file promotion vulnerabilities within WordPress core. However, server-level protections (Phase 5)—such as blocking direct web access and disabling PHP execution in `wp-content/upgrade/`—remain critical layers of defense.

