# Phase 7.3: Accuracy and Safety Corrections Report

**Date:** 2026-11-01  
**Project:** WordPress 7.1.3 vs 7.1.4 Upgrade-Window Research Lab  
**Advisories:**  
- WordPress GitHub Advisory: [GHSA-4w3m-c66w-69mg](https://github.com/WordPress/wordpress-develop/security/advisories/GHSA-4w3m-c66w-69mg) (Low Severity, No CVSS Score, Reporter: Umut Bayram)  
- Wordfence CVE Record: [CVE-2026-119977](https://opencve.frmgm.io/cve/CVE-2026-119977) (CVSS 3.1: 8.1 High, CWE-367)  
**Status:** Verification Completed & Passed  

---

## 1. Summary of Changes

Phase 7.3 implements the required accuracy corrections, advisory metadata alignments, and safety controls across the demonstration codebase, evidentiary package, and documentation.

| Item | Prior State | Phase 7.3 Corrected State | Verification Status |
| :--- | :--- | :--- | :--- |
| **Path Resolution** | Hardcoded `/Users/mac/...` path in `run_demo.sh` | Dynamically resolved relative to script (`SCRIPT_DIR` & `LAB_DIR`) | Verified (`run_demo.sh` runs from any directory) |
| **Temporary Workspaces** | Fixed `demo/mock_*` paths in project tree | Unique temporary directories generated via `mktemp -d` in `$TMPDIR` | Verified (No persistent directories left behind) |
| **Signal Handling** | Single handler on `EXIT INT TERM` | Explicit `on_exit`, `on_int`, and `on_term` handlers with signal propagation | Verified (Safe unbinding and signal propagation) |
| **Copy Simulation** | Tested root-level basenames only | Full recursive relative-path modeling (`wp-content`, `wp-includes/version.php`, `wp-config.php`) | Verified (Accurately skips nested rules and filters config) |
| **Path Predictability** | Claimed `100% predictable across all installations` | Clarified: Predictable for the tested `wordpress.zip` package name | Verified (Reflected in script, dashboard, and docs) |
| **Apache Active Tree** | Printed directive without runtime verification | Verified loaded in runtime configuration tree via `apache2ctl -t -D DUMP_INCLUDES` | Verified (`(222) upgrade-hardening.conf` verified loaded) |
| **Defense Scope** | Implied broad vulnerability mitigation | Explicitly labeled as perimeter defense-in-depth, not universal application mitigation | Verified (Disclaimer added to script and dashboard) |
| **Advisory Attribution** | Merged GHSA with CVSS 3.7 | Separated GHSA (Low, no CVSS) from Wordfence CVE-2026-119977 (CVSS 8.1 High, CWE-367) | Verified (Script text matches required advisory statement) |
| **Screenshot 08 Badge** | Undocumented `[COMPREHENSIVE]` badge | Reclassified as `[LIVE]` (captured live via browser during run) | Verified (Updated in index, dashboard, and reports) |
| **Release Scope** | Described package as "complete" | Explicitly defined as screenshot-and-log release; video capture noted as outstanding | Verified (Updated in `evidence-index.md` and report) |
| **SHA-256 Scope** | Implied proof of research validity | Clarified: Hashes verify file integrity against manifest, not factual validity | Verified (Documented in index and manifest header) |

---

## 2. Technical Implementation Details

### A. Relative-Path Modeling in `copy_dir()` Simulation
The simulation function in [`demo/run_demo.sh`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/demo/run_demo.sh) now recurses through subdirectories and evaluates relative paths against the `$skip` array:
```bash
simulate_copy_dir() {
    local src="$1"
    local dst="$2"
    shift 2
    local skip=("$@")

    find "$src" -type f | sort | while read -r src_file; do
        rel_path="${src_file#$src/}"
        is_skipped=0
        matched_rule=""
        for skip_entry in "${skip[@]}"; do
            if [[ "$rel_path" == "$skip_entry" || "$rel_path" == "$skip_entry"/* ]]; then
                is_skipped=1
                matched_rule="$skip_entry"
                break
            fi
        done
        ...
```
* **7.1.3 Simulation Run:** Correctly skips `wp-content/` and `wp-includes/version.php`, while promoting `wp-config.php` into the destination root.
* **7.1.4 Simulation Run:** Correctly skips `wp-content/`, `wp-includes/version.php`, AND `wp-config.php`, preserving existing production configuration intact.

### B. Runtime Apache Tree Verification
Demonstration D now performs an active runtime introspection before executing live HTTP assertions:
```bash
APACHE_INCLUDES=$(docker exec wp_vuln_app apache2ctl -t -D DUMP_INCLUDES 2>&1 || true)
if echo "$APACHE_INCLUDES" | grep -F "upgrade-hardening.conf" >/dev/null; then
    echo -e "  ✔ Verified: /etc/apache2/conf-enabled/upgrade-hardening.conf is actively loaded."
```

### C. Advisory Metadata Section
The advisory presentation in [`demo/run_demo.sh`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/demo/run_demo.sh) matches the exact required wording:
```text
The WordPress GitHub Security Advisory GHSA-4w3m-c66w-69mg
classifies this issue as Low severity and does not publish a CVSS score.

Wordfence subsequently assigned CVE-2026-119977 with a
CVSS 3.1 score of 8.1 (High), identifying CWE-367.

These ratings are source-specific and should be reported separately.
The behaviours demonstrated here do not independently establish
successful remote code execution.
```

---

## 3. Execution Verification Record

* **Execution Command:** `./demo/run_demo.sh --all`
* **Exit Status:** `0` (Success)
* **Execution Timestamp:** `2026-11-01 06:51:00 UTC`
* **Assertion Pass Summary:**
  * Active Apache configuration inclusion verified: `(222) /etc/apache2/conf-enabled/upgrade-hardening.conf`
  * Directory request (`/wp-content/upgrade/`): `HTTP 403`
  * Static probe request (`/probe.txt`): `HTTP 403`
  * PHP probe request (`/probe.php`): `HTTP 403`
  * Site root request (`/`): `HTTP 200`
* **Cleanup Verification:**
  * `vulnerable/html/wp-content/upgrade/probe.*`: Removed immediately.
  * Unique `$TMPDIR/wp_demo_*` directories: Removed cleanly.
  * No untracked mock files remain in the project workspace.

---

## 4. Environment Health Status

Both test sites remain online and healthy:
* **WordPress 7.1.3 (Port 8081):** `HTTP 200 OK`
* **WordPress 7.1.4 (Port 8082):** `HTTP 200 OK`
* **Database Services:** MariaDB containers (`wp_vuln_db` and `wp_patch_db`) operational.

---

## 5. Evidence Package Status

All evidence files, transcripts, and cryptographic checksums have been regenerated and verified:
* **Index:** [`evidence/phase-7-2/evidence-index.md`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-7-2/evidence-index.md)
* **Integrity Manifest:** [`evidence/phase-7-2/logs/evidence-integrity-sha256.txt`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-7-2/logs/evidence-integrity-sha256.txt)
* **Screenshots:** 9 high-resolution images under [`evidence/phase-7-2/screenshots/`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-7-2/screenshots/)
* **Logs:** 3 operational execution logs under [`evidence/phase-7-2/logs/`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-7-2/logs/)
* **Video Notice:** Explicitly documented as outstanding in [`evidence/phase-7-2/video/recording-instructions.md`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-7-2/video/recording-instructions.md).

No git commits or external pushes have been executed. Standing by for user approval.

