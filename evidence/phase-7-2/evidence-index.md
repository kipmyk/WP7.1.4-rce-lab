# Phase 7.2: Evidence Package Index

This index catalogs all evidence captured during testing and verification of the WordPress 7.1.3 vs 7.1.4 upgrade-window security research lab.

> [!NOTE]
> **Integrity Notice:** SHA-256 hashes recorded in this package verify file integrity against the recorded manifest. They do not independently prove factual correctness, accuracy, or authenticity of underlying research hypotheses.
> **Release Scope:** Video recording is currently **outstanding** due to headless macOS TCC display capture boundaries. This release is delivered as an inspected **screenshot-and-log evidence package**.

---

## 1. Screenshots Directory (`screenshots/`)

| File Name | Test / Target | Evidence Classification | Purpose & Description |
| :--- | :--- | :--- | :--- |
| [`01-environment-baseline.png`](screenshots/01-environment-baseline.png) | Docker Containers | `[LIVE]` | Terminal view of active Docker container table verifying `wp_vuln_app` (8081) and `wp_patch_app` (8082) are UP. |
| [`02-wordpress-713-browser.png`](screenshots/02-wordpress-713-browser.png) | `http://127.0.0.1:8081/` | `[LIVE]` | Full browser render of the baseline WordPress 7.1.3 site front page. |
| [`03-wordpress-714-browser.png`](screenshots/03-wordpress-714-browser.png) | `http://127.0.0.1:8082/` | `[LIVE]` | Full browser render of the baseline WordPress 7.1.4 site front page. |
| [`04-demo-a-results.png`](screenshots/04-demo-a-results.png) | Demonstration A | `[SIMULATED]` / `[SOURCE-VERIFIED]` | Terminal walkthrough of 7.1.3 naming predictable for `wordpress.zip`, pre-unpack guard stripping simulation, and relative-path config overwrite simulation. |
| [`05-demo-b-results.png`](screenshots/05-demo-b-results.png) | Demonstration B | `[SIMULATED]` / `[SOURCE-VERIFIED]` | Terminal walkthrough of 7.1.4 16-char suffix derivation, guard preservation/creation simulation, and relative-path config exclusion simulation. |
| [`06-demo-c-timings.png`](screenshots/06-demo-c-timings.png) | Demonstration C | `[HISTORICAL]` | Terminal view of update pipeline stages and Phase 5 empirical staging lifespan telemetry (~12.33s vs ~11.32s). |
| [`07-apache-live-tests.png`](screenshots/07-apache-live-tests.png) | Demonstration D | `[LIVE]` | Terminal view verifying active Apache configuration tree and live HTTP assertions on port 8081 (asserting HTTP 403 on upgrade paths). |
| [`08-research-dashboard.png`](screenshots/08-research-dashboard.png) | `demo/index.html` | `[LIVE]` | Browser render of the complete research dashboard displaying side-by-side comparisons, timeline, and defense table with badges. |
| [`09-final-environment-health.png`](screenshots/09-final-environment-health.png) | Health Verification | `[LIVE]` | Terminal view verifying container status, HTTP 200 on both sites, and HTTP 403 enforcement on the hardened upgrade path. |

---

## 2. Logs Directory (`logs/`)

| File Name | Evidence Classification | Purpose & Contents |
| :--- | :--- | :--- |
| [`execution-transcript.txt`](logs/execution-transcript.txt) | `[LIVE EXECUTION]` | Complete plaintext terminal transcript of running `./demo/run_demo.sh --all`. |
| [`environment-baseline.txt`](logs/environment-baseline.txt) | `[ENVIRONMENT STATE]` | Detailed host OS, Docker container table, WP-CLI version checks, active Apache configuration tree verification, and listening port bindings. |
| [`http-verification.txt`](logs/http-verification.txt) | `[NETWORK PERIMETER]` | Full HTTP response headers, status codes, and probe request logs verifying 403 enforcement and 200 site availability. |
| [`evidence-integrity-sha256.txt`](logs/evidence-integrity-sha256.txt) | `[INTEGRITY MANIFEST]` | Cryptographic SHA-256 hash checksums of all evidence files in this package for integrity verification against tampering. |

---

## 3. Video Directory (`video/`)

| File Name | Purpose & Contents |
| :--- | :--- |
| [`recording-instructions.md`](video/recording-instructions.md) | Release notice documenting that video capture is outstanding due to headless macOS TCC security isolation, accompanied by step-by-step instructions for operator manual video recording using QuickTime / macOS Screenshot toolbar (`Cmd+Shift+5`). |
