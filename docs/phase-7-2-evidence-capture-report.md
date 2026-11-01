# Phase 7.2: Evidence Capture & Demonstration Integrity Report

**Date:** 2026-11-01  
**Project:** WordPress 7.1.3 vs 7.1.4 Upgrade-Window Research Lab  
**Advisories:**  
- WordPress Security Advisory: [GHSA-4w3m-c66w-69mg](https://github.com/WordPress/wordpress-develop/security/advisories/GHSA-4w3m-c66w-69mg) (Low Severity, No CVSS Score, Reporter: Umut Bayram)  
- Wordfence CVE Record: [CVE-2026-119977](https://opencve.frmgm.io/cve/CVE-2026-119977) (CVSS 3.1: 8.1 High, CWE-367)  
**Research Scope:** Local Isolated Docker Environment (`127.0.0.1:8081` & `127.0.0.1:8082`)  
**Package Scope:** Screenshot and Operational Log Evidence Package (Video capture remains outstanding)

---

## 1. Executive Summary

Phase 7.2 captures and catalogs the evidentiary records supporting the Phase 7 demonstration suite. All demonstration modules, source-code analysis claims, timing observations, and server-level hardening controls have been audited, classified, and verified.

### Core Audit & Evidence Integrity Points
1. **Advisory Attribution Separated:** Distinguishes the official WordPress GitHub Advisory GHSA-4w3m-c66w-69mg (Low severity, no CVSS score) from Wordfence's subsequent CVE-2026-119977 entry (CVSS 8.1 High, CWE-367).
2. **Evidence Classification Integrity:** Applied standardized labels across all components:
   - `[LIVE]`: Executed against local containers during the run.
   - `[SIMULATED]`: Shell/filesystem modeling of underlying loop logic and file synchronization.
   - `[SOURCE-VERIFIED]`: Confirmed directly in WordPress core source code.
   - `[HISTORICAL]`: Phase 4/5/6 empirical telemetry and tests.
   - `[STATIC ANALYSIS]`: Configuration syntax review without runtime testing.
   - `[ILLUSTRATIVE]`: Example values (such as random strings) not produced by WordPress runtime.
3. **Automated Assertion Hardening:** Upgraded the Apache hardening tests in Demonstration D to genuine assertions testing runtime configuration tree inclusion (`apache2ctl -t -D DUMP_INCLUDES`) and HTTP response codes (`403` on directory and probes, `200` on site root).
4. **Signal Trap Cleanup:** Implemented dedicated signal handlers for `EXIT`, `INT`, and `TERM` ensuring probes and unique temporary directories are removed even on aborts or assertion failures.
5. **Release Scope:** Delivers 9 high-resolution screenshot artifacts, 3 operational execution logs plus a cryptographic checksum manifest, and operator video recording instructions. Video recording remains outstanding due to headless macOS display capture boundaries.

---

## 2. Evidence Package Structure

The evidence package is archived under [`evidence/phase-7-2/`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-7-2/):

```
evidence/phase-7-2/
├── evidence-index.md
├── screenshots/
│   ├── 01-environment-baseline.png
│   ├── 02-wordpress-713-browser.png
│   ├── 03-wordpress-714-browser.png
│   ├── 04-demo-a-results.png
│   ├── 05-demo-b-results.png
│   ├── 06-demo-c-timings.png
│   ├── 07-apache-live-tests.png
│   ├── 08-research-dashboard.png
│   └── 09-final-environment-health.png
├── logs/
│   ├── environment-baseline.txt
│   ├── execution-transcript.txt
│   ├── http-verification.txt
│   └── evidence-integrity-sha256.txt
└── video/
    └── recording-instructions.md
```

---

## 3. Evidence Catalog & Verification Mapping

### Screenshots
* [`01-environment-baseline.png`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-7-2/screenshots/01-environment-baseline.png) `[LIVE]`: Verifies active container status for `wp_vuln_app` (7.1.3), `wp_patch_app` (7.1.4), and MariaDB databases.
* [`02-wordpress-713-browser.png`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-7-2/screenshots/02-wordpress-713-browser.png) `[LIVE]`: Direct browser render of `http://127.0.0.1:8081/` showing WordPress 7.1.3 baseline front page.
* [`03-wordpress-714-browser.png`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-7-2/screenshots/03-wordpress-714-browser.png) `[LIVE]`: Direct browser render of `http://127.0.0.1:8082/` showing WordPress 7.1.4 baseline front page.
* [`04-demo-a-results.png`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-7-2/screenshots/04-demo-a-results.png) `[SIMULATED / SOURCE-VERIFIED]`: Terminal walkthrough of 7.1.3 naming predictable for `wordpress.zip`, pre-unpack cleanup simulation, and relative-path synthetic `wp-config.php` overwrite.
* [`05-demo-b-results.png`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-7-2/screenshots/05-demo-b-results.png) `[SIMULATED / SOURCE-VERIFIED]`: Terminal walkthrough of 7.1.4 16-character pseudo-random directory suffix, guard preservation/creation, and relative-path file copy exclusion.
* [`06-demo-c-timings.png`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-7-2/screenshots/06-demo-c-timings.png) `[HISTORICAL]`: Terminal walkthrough of the 5-stage core upgrade pipeline and empirical staging lifespans (~12.33s vs ~11.32s).
* [`07-apache-live-tests.png`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-7-2/screenshots/07-apache-live-tests.png) `[LIVE]`: Terminal execution of active Apache configuration tree check and live HTTP assertions, confirming HTTP 403 on directory, static probe, and PHP probe, with HTTP 200 on site root.
* [`08-research-dashboard.png`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-7-2/screenshots/08-research-dashboard.png) `[LIVE]`: Full-page browser render of the revised research dashboard [`demo/index.html`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/demo/index.html) displaying side-by-side analysis and badge classifications.
* [`09-final-environment-health.png`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-7-2/screenshots/09-final-environment-health.png) `[LIVE]`: Final post-execution health verification proving both sites and database services remain operational.

### Operational Logs & Checksums
* [`logs/execution-transcript.txt`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-7-2/logs/execution-transcript.txt): Plaintext capture of `./demo/run_demo.sh --all`.
* [`logs/environment-baseline.txt`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-7-2/logs/environment-baseline.txt): Verification of container IDs, WP-CLI version checks, active Apache configuration tree inclusion (`apache2ctl -t -D DUMP_INCLUDES`), and listening port bindings.
* [`logs/http-verification.txt`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-7-2/logs/http-verification.txt): HTTP response headers, redirect targets, and probe status logs for both installations.
* [`logs/evidence-integrity-sha256.txt`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-7-2/logs/evidence-integrity-sha256.txt): SHA-256 hash manifest covering all artifacts in the package. Hashes verify file integrity against the manifest, not the truth value or authenticity of underlying research claims.

---

## 4. Video Recording Status & Rationale

* **Headless Capture Limitation:** macOS TCC security controls prohibit unattended background processes from recording screen contents without interactive user consent. Headless execution of `screencapture` or `ffmpeg -f avfoundation` produces display device errors or hangs.
* **Release Scope Notice:** Video capture remains **outstanding**. This package is released as an inspected **screenshot-and-log evidence package**.
* **Operator Instructions:** Clear, step-by-step instructions for manual operator recording using QuickTime Player or macOS native screen recording (`Cmd+Shift+5`) are provided in [`recording-instructions.md`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/evidence/phase-7-2/video/recording-instructions.md).

---

## 5. Final Health Verification & Lab State

The research lab remains healthy, stable, and fully isolated on loopback interfaces:

| Container | Image | Port Mapping | Health / HTTP Status | Role |
| :--- | :--- | :--- | :--- | :--- |
| `wp_vuln_app` | `wordpress:php8.2-apache` | `127.0.0.1:8081->80` | `HTTP 200 OK` | Vulnerable Baseline (WordPress 7.1.3) |
| `wp_patch_app` | `wordpress:php8.2-apache` | `127.0.0.1:8082->80` | `HTTP 200 OK` | Patched Protection (WordPress 7.1.4) |
| `wp_vuln_db` | `mariadb:10.11` | `3306` (Internal) | `Operational` | Isolated Database for 7.1.3 |
| `wp_patch_db` | `mariadb:10.11` | `3306` (Internal) | `Operational` | Isolated Database for 7.1.4 |

Both sites remain fully accessible locally. No weaponized payloads or unauthorized exploits were executed.
