# Phase 7: Local Demonstration & Reproducible Security Walkthrough Guide

## Overview

Phase 7 delivers a safe, repeatable, locally hosted demonstration of the security research comparing **WordPress 7.1.3** and **WordPress 7.1.4** regarding:
- **WordPress GitHub Advisory:** [GHSA-4w3m-c66w-69mg](https://github.com/WordPress/wordpress-develop/security/advisories/GHSA-4w3m-c66w-69mg) (Low severity, no CVSS score, reported by Umut Bayram).
- **Wordfence CVE Record:** [CVE-2026-119977](https://opencve.frmgm.io/cve/CVE-2026-119977) (CVSS 3.1: 8.1 High, CWE-367).

The demonstration is organized across two interfaces:
1. **Automated Terminal Walkthrough:** [`demo/run_demo.sh`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/demo/run_demo.sh) for CLI execution, regression testing, and assertions.
2. **Local Research Dashboard:** [`demo/index.html`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/demo/index.html) for visual review and side-by-side comparative inspection.

---

## Operating Instructions

### 1. Terminal Walkthrough Execution

From the project root directory:

```bash
# Run the complete demonstration sequentially
./demo/run_demo.sh --all

# Or execute individual demonstration modules:
./demo/run_demo.sh --demo-a   # Demonstration A: WordPress 7.1.3 baseline
./demo/run_demo.sh --demo-b   # Demonstration B: WordPress 7.1.4 protections
./demo/run_demo.sh --demo-c   # Demonstration C: Lifecycle & timing walkthrough
./demo/run_demo.sh --demo-d   # Demonstration D: Server-level hardening assessment
```

### 2. Local Dashboard Review

To view the visual research dashboard locally, open `demo/index.html` in any standard browser:
```bash
open demo/index.html
```

---

## Evidence Classification Standard

Every finding, simulation, and measurement across the demonstration conforms to strict classification labels:

| Label | Definition | Scope in Demonstration |
| :--- | :--- | :--- |
| `[LIVE]` | Executed against local containers during the run | Apache runtime tree verification and HTTP assertions on port `8081` |
| `[SIMULATED]` | Filesystem behavior modeled with shell scripts | Cleanup loops and relative-path `copy_dir()` exclusion simulations |
| `[SOURCE-VERIFIED]` | Verified directly in WordPress core source code | Upgrader naming logic, exclusion arrays, and guard checks |
| `[HISTORICAL]` | Evidence collected during Phases 4, 5, or 6 | Measured staging lifespans, Nginx tests, WP-CLI update run |
| `[STATIC ANALYSIS]` | Configuration inspected without runtime tests | OpenLiteSpeed directive and rule review |
| `[ILLUSTRATIVE]` | Representative sample values | Random string samples for staging directories |

---

## Demonstration Modules & Observable Behaviors

### Demonstration A: WordPress 7.1.3 (Vulnerable Core Baseline)
* **`[SOURCE-VERIFIED]` Staging Directory Derivation:**
  * Derived deterministically from the package filename: `$working_dir = $upgrade_folder . basename( basename( $package, ".tmp" ), ".zip" );`.
  * For the tested `wordpress.zip` package, resolves to `wp-content/upgrade/wordpress/`. It is not hardcoded, but derived from the archive filename.
* **`[SIMULATED]` Pre-Unpack Guard File Stripping:**
  * Demonstrates that the 7.1.3 pre-unpack cleanup routine unconditionally purges all files in the upgrade folder, removing `index.php`.
* **`[SIMULATED]` File Promotion Boundary with Relative Path Modeling:**
  * Simulates `copy_dir()` relative-path traversal with the 7.1.3 `$skip` list (`wp-content`, `wp-includes/version.php`).
  * Shows that nested paths (`wp-includes/version.php`, `wp-content/`) are skipped, but `wp-config.php` is copied into the site root.
* **`[SOURCE-VERIFIED]` Advisory Context & Distinctions:**
  * Explicitly distinguishes the WordPress GHSA (Low, no CVSS) from Wordfence CVE-2026-119977 (CVSS 8.1 High, CWE-367).
  * Notes that these source behaviors satisfy prerequisites described in the reports, but do not provide an independent RCE without web execution and timing alignment.

### Demonstration B: WordPress 7.1.4 (Patched Core Protections)
* **`[SOURCE-VERIFIED]` Staging Directory Randomization:**
  * Demonstrates `get_working_dir_name()` appending a 16-character pseudo-random alphanumeric suffix (`wp_generate_password(16, false)`).
  * Displays `[ILLUSTRATIVE]` sample paths demonstrating unpredictable directory naming.
* **`[SIMULATED]` Guard File Preservation & Auto-Creation:**
  * Demonstrates both 7.1.4 guard-file handling cases:
    1. Existing `index.php` is preserved during pre-unpack cleanup.
    2. Missing `index.php` is auto-generated with empty guard content before package extraction.
* **`[SIMULATED]` File Promotion Exclusion with Relative Path Modeling:**
  * Simulates source-to-destination copy with 7.1.4 `$skip` list (`wp-content`, `wp-includes/version.php`, `wp-config.php`).
  * Confirms that staged `wp-config.php` is dropped while normal core files are promoted.

### Demonstration C: Core Upgrade Lifecycle & Timing Walkthrough
* **Lifecycle Architecture:**
  * Details the 5 update stages: download/signature verification, staging folder creation, extraction (`setup-config.php` appears at +700–800ms), file sync, and cleanup.
* **`[HISTORICAL]` Telemetry:**
  * Displays empirical staging folder lifespans captured during Phase 5: ~12.33s for 7.1.3 and ~11.32s for 7.1.4.
* **Environmental Notice:**
  * Explicitly states that measurements reflect the local macOS Apple Silicon ARM64 / Colima / VZ virtualization lab and should not be interpreted as universal attack windows.

### Demonstration D: Server-Level Hardening Assessment
* **`[LIVE]` Apache Active Configuration Check & Assertions:**
  * Verifies `upgrade-hardening.conf` is loaded in the active Apache runtime configuration tree (`apache2ctl -t -D DUMP_INCLUDES`).
  * Deploys temporary probe files and issues real HTTP requests to `http://127.0.0.1:8081/`.
  * Formally asserts `HTTP 403` for directory, static probe (`probe.txt`), and PHP probe (`probe.php`), and `HTTP 200` for site root. Exits non-zero if any assertion fails.
  * Ensures probe files and temporary directories are cleaned up automatically via signal traps.
* **`[SOURCE-VERIFIED]` Defense-in-Depth Scope:**
  * Clarifies that the tests prove the tested HTTP paths are blocked under this server configuration, providing perimeter defense-in-depth without implying universal application mitigation across all hosting environments.
* **`[HISTORICAL]` Nginx 1.31+ Hardening Directive:**
  * Reviews the Nginx `location ^~ /wp-content/upgrade/` directive verified during Phase 6 disposable container tests.
* **`[STATIC ANALYSIS]` OpenLiteSpeed Security Directive Review:**
  * Reaffirms that OpenLiteSpeed configuration (`type NULL`, `accessible no`) was evaluated via static syntax review rather than container runtime.
* **`[HISTORICAL]` Core Update Compatibility:**
  * Confirms that local WP-CLI core updates completed without errors because WordPress core updates operate via local server filesystem I/O (`WP_Filesystem`).

---

## Safety & Baseline Integrity

* **Unique Temporary Directories:** Simulations run inside unique temporary directories created via `mktemp -d` and are safely removed.
* **Baseline Preservation:** Both WordPress containers (`wp_vuln_app` on `8081` and `wp_patch_app` on `8082`) remain online in their baseline operational state.
* **Safe State Verification:**
  ```bash
  # Check status of both installations:
  curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8081/
  curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8082/
  ```

---

## Research Boundaries & Disclaimers

1. **Full RCE Not Reproduced:** This demonstration does not execute weaponized payloads, complete installer forms with rogue database credentials, or achieve command execution.
2. **Defense-in-Depth Distinction:** Web server HTTP restrictions provide perimeter access controls; they do not fix application code.
3. **OpenLiteSpeed Notice:** OpenLiteSpeed configuration remains documented as static configuration analysis, not runtime validation.
