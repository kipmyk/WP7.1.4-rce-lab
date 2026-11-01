# Phase 4 Test Plan: Controlled Security Property Verification

This test plan defines 8 non-destructive verification tests to evaluate the individual security properties identified in **GHSA-4w3m-c66w-69mg**, comparing **WordPress 7.1.3** and **WordPress 7.1.4**.

---

## Operating Principles
* **Non-Destructive:** No RCE payloads, no shell generation, no weaponized exploits.
* **Isolated Environment:** Executed strictly against `127.0.0.1:8081` (vulnerable) and `127.0.0.1:8082` (patched).
* **Benign Test Probes:** All file creation tests use harmless string markers (e.g., `// BENIGN_TEST_MARKER`).
* **Controlled Evaluation:** Tests evaluate individual security boundaries rather than attempting to execute a complete attack chain.

---

## Test Specifications

### Test 1: Upgrade Directory Listing
* **Objective:** Determine whether `wp-content/upgrade/` exposes its contents to unauthenticated visitors.
* **Preconditions:** Containers running, `wp-content/upgrade/` exists on both sites.
* **Procedure:** Issue `GET /wp-content/upgrade/` to both instances without an `index.php` present, and record the HTTP status code and response body.
* **Expected Result:** On default Apache installations without an `index.php` guard, directory listing or a 403 Forbidden is returned depending on `Options -Indexes`.

### Test 2: Guard File Preservation
* **Objective:** Verify whether the upgrader cleanup routine strips or preserves `index.php` in `wp-content/upgrade/`.
* **Preconditions:** Place a test `index.php` inside `wp-content/upgrade/`.
* **Procedure:** Simulate `WP_Upgrader::unpack_package()` cleanup logic on both 7.1.3 and 7.1.4 codebases.
* **Expected Result:** 7.1.3 removes `index.php`; 7.1.4 explicitly skips and preserves `index.php`.

### Test 3: Staging Directory Naming Predictability
* **Objective:** Verify whether the temporary extraction directory is deterministic or randomized.
* **Preconditions:** Package name `wordpress.zip`.
* **Procedure:** Evaluate the directory derivation logic across 1,000 iterations for both 7.1.3 and 7.1.4.
* **Expected Result:** 7.1.3 yields identical directory names every time (`upgrade/wordpress/`); 7.1.4 yields 1,000 unique 16-character random names.

### Test 4: Direct File Access in Staging Directory
* **Objective:** Establish whether known files within `wp-content/upgrade/` can be retrieved directly via HTTP.
* **Preconditions:** Harmless probe file `probe.txt` created in `wp-content/upgrade/`.
* **Procedure:** Issue `GET /wp-content/upgrade/probe.txt` to both instances.
* **Expected Result:** Unless web server rules block access to the folder, `probe.txt` is served with HTTP 200.

### Test 5: PHP Script Execution in Staging Directory
* **Objective:** Verify whether the web server executes PHP scripts located within `wp-content/upgrade/`.
* **Preconditions:** Harmless probe file `probe.php` containing `<?php echo "PROBE_PHP_EXEC_OK"; ?>` placed in `wp-content/upgrade/`.
* **Procedure:** Issue `GET /wp-content/upgrade/probe.php`.
* **Expected Result:** If PHP handler is active in `wp-content/`, the string `PROBE_PHP_EXEC_OK` is returned; if blocked, raw source or 403 Forbidden is returned.

### Test 6: Configuration Exclusion (`wp-config.php`)
* **Objective:** Verify that `copy_dir()` in 7.1.4 refuses to copy a `wp-config.php` file from a staging folder into the target directory.
* **Preconditions:** Isolated test directory containing a harmless dummy `wp-config.php` (`// BENIGN_DUMMY_CONFIG`).
* **Procedure:** Execute `copy_dir()` using the 7.1.3 `$skip` list vs. the 7.1.4 `$skip` list.
* **Expected Result:** In 7.1.3, the dummy config is copied; in 7.1.4, it is explicitly excluded.

### Test 7: Temporary File Cleanup
* **Objective:** Confirm whether update archives and staging folders are removed after extraction.
* **Preconditions:** Mock upgrade archive in `wp-content/upgrade/`.
* **Procedure:** Execute unpack routine and verify deletion of package archive.
* **Expected Result:** Archive file is unlinked upon completion.

### Test 8: Legitimate Upgrade Workflow Integrity
* **Objective:** Confirm that legitimate core updates function correctly under the patched implementation.
* **Preconditions:** Patched 7.1.4 installation with complete database configuration.
* **Procedure:** Verify that `Core_Upgrader` methods complete without syntax or runtime exceptions.
* **Expected Result:** Core upgrader completes successfully with zero regressions.

