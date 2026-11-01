# Phase 5: WordPress Upgrade-Window Security Validation Report
## Comparative Analysis of WordPress 7.1.3 vs. WordPress 7.1.4

**Investigation Date:** November 1, 2026  
**Advisory Reference:** [GHSA-4w3m-c66w-69mg](https://github.com/WordPress/wordpress-develop/security/advisories/GHSA-4w3m-c66w-69mg) (Severity: Low / No CVE assigned)  
**Target Environments:** Isolated Docker Containers (`127.0.0.1:8081` & `127.0.0.1:8082`)

---

## 1. Research Question & Purpose

> **Central Research Question:** What does the WordPress 7.1.3 upgrade workflow expose during a legitimate core update, and how do the WordPress 7.1.4 changes alter those observable security properties?

This investigation conducted non-invasive instrumentation of the real core update workflow in isolated local environments to independently evaluate the security conditions documented in GHSA-4w3m-c66w-69mg.

---

## 2. Findings Classification Matrix

| Investigated Property | WordPress 7.1.3 | WordPress 7.1.4 | Finding Classification | Evidence Reference |
| :--- | :--- | :--- | :--- | :--- |
| **1. Staging Directory Naming** | Deterministic: `upgrade/test-update/` (100% predictable from archive name). | Randomized: `upgrade/test-update-weIdh9kF3Kd71Nkj/` (16-char suffix). | **Confirmed Source & Empirical Observation** | [`713-upgrade-lifecycle.json`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-5/timing-results/713-upgrade-lifecycle.json) vs [`714-upgrade-lifecycle.json`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-5/timing-results/714-upgrade-lifecycle.json) |
| **2. Guard File Handling** | Strips `index.php` during pre-unpack cleanup loop. | Skips and preserves `index.php`; auto-generates if missing. | **Confirmed Source Behavior** | [`class-wp-upgrader.diff`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/class-wp-upgrader.diff) & [`02-guard-file-preservation.json`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-4-test-results/02-guard-file-preservation.json) |
| **3. Transient Window Duration** | Staging folder existed for **12,330.89 ms** (~12.3 s). | Staging folder existed for **11,321.29 ms** (~11.3 s). | **Confirmed Empirical Observation** | [`lifecycle-comparison.md`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-5/filesystem-observations/lifecycle-comparison.md) |
| **4. Installer File Lifespan** | `setup-config.php` present for **11,514.28 ms** (~11.5 s). | `setup-config.php` present for **10,615.99 ms** (~10.6 s). | **Confirmed Empirical Observation** | [`lifecycle-comparison.md`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-5/filesystem-observations/lifecycle-comparison.md) |
| **5. Direct HTTP Accessibility** | Accessible: HTTP `200 OK` for static probes in upgrade path. | Accessible: HTTP `200 OK` for static probes in upgrade path. | **Confirmed Environmental Observation** | [`http-responses.md`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-5/http-captures/http-responses.md) |
| **6. PHP Script Execution** | Active: Executed PHP probe (`200 OK`, marker rendered). | Active: Executed PHP probe (`200 OK`, marker rendered). | **Confirmed Environmental Observation** | [`http-responses.md`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-5/http-captures/http-responses.md) |
| **7. Directory Listing (Server-Level)** | Returns `403 Forbidden` without index due to Apache `Options -Indexes`. | Returns `403 Forbidden` without index due to Apache `Options -Indexes`. | **Confirmed Environmental Observation** | [`http-responses.md`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-5/http-captures/http-responses.md) |
| **8. Config File Promotion Boundary** | Overwrote target: `copy_dir()` lacked `wp-config.php` exclusion. | Protected: `copy_dir()` strictly skipped `wp-config.php`. | **Confirmed Source Behavior** | [`06-wp-config-exclusion.json`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-4-test-results/06-wp-config-exclusion.json) |
| **9. End-to-End RCE Reproduction** | Not attempted (restricted by research safety protocol). | Not attempted (restricted by research safety protocol). | **Not Reproduced / Excluded by Scope** | Scope constraint: non-destructive testing only. |

---

## 3. Detailed Experimental Analysis

### A. Staging Directory Lifecycle & Exposure
Using our non-invasive observer harness during legitimate updates:
1. **In WordPress 7.1.3:**
   - Package `test-update.zip` was uncompressed into `/wp-content/upgrade/test-update/`.
   - The directory name matched `basename( basename( $package, '.tmp' ), '.zip' )` exactly.
   - The extraction folder existed on disk for **12,330.89 ms** (~12.33 seconds).
   - Within this window, the full WordPress distribution including `wp-admin/setup-config.php` resided on disk for **11,514.28 ms** (~11.51 seconds).
2. **In WordPress 7.1.4:**
   - Package `test-update.zip` was uncompressed into `/wp-content/upgrade/test-update-weIdh9kF3Kd71Nkj/`.
   - The directory name appended a 16-character pseudo-random alphanumeric suffix generated by `get_working_dir_name()`.
   - The extraction folder existed for **11,321.29 ms** (~11.32 seconds).
   - `setup-config.php` resided on disk for **10,615.99 ms** (~10.62 seconds).

> **Core Analytical Insight:** The transient window itself was **not eliminated** by the patch (both versions required ~11–12 seconds to unpack and synchronize files on the tested Docker volume). Rather, the patch transformed the staging path from a deterministic, discoverable location into an unguessable location.

---

### B. Directory Listing vs. Direct File Access
1. **Server-Level Directory Indexing:**
   - When querying `/wp-content/upgrade/` without an `index.php`, Apache returned `HTTP/1.1 403 Forbidden`.
   - This proves that in default Debian Apache configurations, directory listing is already disabled by server policy (`Options -Indexes`).
   - The addition of `index.php` in 7.1.4 acts as application-level defense for environments where web servers have `Options +Indexes` or Nginx `autoindex on` accidentally enabled.
2. **Direct File Access:**
   - Direct HTTP requests to known files inside `wp-content/upgrade/` returned `HTTP/1.1 200 OK` on both versions.
   - This proves that `index.php` prevents directory indexing, but does **not** prevent direct access if an attacker already knows or guesses the file path.
   - Path randomization in 7.1.4 is therefore the operative control preventing direct URL access.

---

### C. File Promotion Boundary (`wp-config.php`)
1. **In WordPress 7.1.3:**
   - Line 1549 of `wp-admin/includes/update-core.php`:
     ```php
     $skip = array( 'wp-content', 'wp-includes/version.php' );
     ```
   - When tested with a staged dummy configuration file, `copy_dir()` copied the file into the destination root, overwriting existing files.
2. **In WordPress 7.1.4:**
   - Line 1549 of `wp-admin/includes/update-core.php`:
     ```php
     $skip = array( 'wp-content', 'wp-includes/version.php', 'wp-config.php' );
     ```
   - When tested under identical conditions, `copy_dir()` refused to copy `wp-config.php`, preserving the destination configuration.

---

## 4. Rigorous Analytical Distinctions

To ensure engineering and scientific integrity for future technical reporting:

### 1. What the Official Advisory (GHSA-4w3m-c66w-69mg) States
* Classifies the vulnerability as **Low severity**.
* Identifies a race condition during complete-package core updates that could lead to remote code execution.
* Credits researcher **Umut Bayram**.
* Notes no assigned CVE or CWE.

### 2. What the Source Code Establishes
* WordPress <= 7.1.3 derived upgrade directories deterministically from the package name.
* WordPress <= 7.1.3 purged all files in `wp-content/upgrade/`, including `index.php`.
* WordPress <= 7.1.3 lacked `'wp-config.php'` in the `copy_dir()` skip list.
* WordPress 7.1.4 introduced 16-character directory entropy, preserved/generated `index.php`, and excluded `wp-config.php`.

### 3. What the Local Experiments Confirmed
* The staging directory and `setup-config.php` exist for an observed duration of ~11.3 to 12.3 seconds during legitimate core updates.
* The web server executes PHP files located inside `wp-content/upgrade/` by default.
* The 7.1.3 upgrader predictably creates `upgrade/test-update/`, whereas 7.1.4 creates `upgrade/test-update-weIdh9kF3Kd71Nkj/`.
* The 7.1.3 upgrader syncs staged `wp-config.php` files into the root, whereas 7.1.4 strictly drops them.

### 4. What Remains an Inference / Unreproduced
* **Full End-to-End RCE:** We did not attempt to interactively submit database credentials to `setup-config.php` during the 11-second window to overwrite production credentials, as this violates non-destructive research constraints.
* Therefore, while the underlying prerequisites (path predictability, web accessibility, PHP execution, and promotion failure) were verified, the full unauthenticated RCE chain remains a documented advisory risk rather than a locally executed exploit.

---

## 5. Technical Deliverables Summary

All experimental logs, source traces, and timing records are permanently archived in the repository:
* Source Traces: [`evidence/phase-5/source-traces/workflow-trace.md`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-5/source-traces/workflow-trace.md)
* HTTP Responses: [`evidence/phase-5/http-captures/http-responses.md`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-5/http-captures/http-responses.md)
* Filesystem Transcripts: [`evidence/phase-5/filesystem-observations/lifecycle-comparison.md`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-5/filesystem-observations/lifecycle-comparison.md)
* Timing Telemetry: [`evidence/phase-5/timing-results/713-upgrade-lifecycle.json`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-5/timing-results/713-upgrade-lifecycle.json) & [`714-upgrade-lifecycle.json`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-5/timing-results/714-upgrade-lifecycle.json)

