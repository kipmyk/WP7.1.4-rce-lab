# Phase 7: Automated Demonstration Script Guide

This document describes the design, execution parameters, and test routines implemented in the automated demonstration harness [`demo/run_demo.sh`](file:///Users/mac/Code/Security/WP7.1.4-rce-lab/demo/run_demo.sh).

---

## 1. Script Architecture & Options

The script `demo/run_demo.sh` provides both an interactive terminal menu and non-interactive command-line flags for reproducible execution:

```bash
# Interactive menu:
./demo/run_demo.sh

# Non-interactive executions:
./demo/run_demo.sh --all      # Executes all demonstrations A through D sequentially
./demo/run_demo.sh --demo-a   # Demonstration A: WordPress 7.1.3 vulnerable core baseline
./demo/run_demo.sh --demo-b   # Demonstration B: WordPress 7.1.4 patched protections
./demo/run_demo.sh --demo-c   # Demonstration C: Lifecycle and timing walkthrough
./demo/run_demo.sh --demo-d   # Demonstration D: Server-level hardening assessment
```

### Safety & Integrity Architecture
- **Dynamic Path Resolution:** Paths are dynamically resolved relative to the script location (`LAB_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"`), removing hardcoded system directories.
- **Unique Temporary Workspaces:** Simulations run inside unique temporary directories created via `mktemp -d "${TMPDIR:-/tmp}/wp_demo_XXXXXX"`.
- **Dedicated Signal Handlers:** Registers explicit handlers for `EXIT`, `INT`, and `TERM`:
  - `INT` and `TERM` trigger cleanup, unbind traps, and propagate the signal to exit with standard signal status codes.
- **Safe Directory Cleanup:** Before deletion, the cleanup routine verifies that temporary paths match the expected `wp_demo_` pattern and belong to the script session.
- **Fail-Safe Assertions:** Live HTTP checks in Demonstration D execute as genuine assertions. If any response code deviates from policy (`403` for upgrade paths, `200` for site root), the script logs the failure and exits with code `1`.
- **Classification Standard:** Every output section is labeled with standard evidence classifications:
  - `[LIVE]`: Executed against local containers during the run.
  - `[SIMULATED]`: Filesystem behavior reproduced using shell operations and synthetic files.
  - `[SOURCE-VERIFIED]`: Directly confirmed from WordPress core source code.
  - `[HISTORICAL]`: Historical telemetry or tests from Phases 4, 5, or 6.
  - `[STATIC ANALYSIS]`: Configuration syntax review without runtime testing.
  - `[ILLUSTRATIVE]`: Example values (such as random strings) not produced by WordPress runtime.

---

## 2. Walkthrough Routine Breakdown

### Demonstration A: WordPress 7.1.3 (Vulnerable Core Baseline)
1. **`[SOURCE-VERIFIED]` Staging Directory Naming:**
   - Evaluates `class-wp-upgrader.php` deterministic basename derivation for the tested package `wordpress.zip`:
     `$working_dir = $upgrade_folder . basename( basename( $package, ".tmp" ), ".zip" );`
   - Clarifies that the path `wp-content/upgrade/wordpress/` is predictable for the tested `wordpress.zip` package name, depending on the archive filename rather than being hardcoded.
2. **`[SIMULATED]` Pre-Unpack Guard Stripping:**
   - Simulates the 7.1.3 cleanup loop using shell operations in a unique temporary directory.
   - Demonstrates that `index.php` is deleted unconditionally, leaving the directory subject to web-server autoindexing if unhardened.
3. **`[SIMULATED]` File Promotion Boundary with Relative Path Modeling:**
   - Simulates `copy_dir()` relative-path traversal with the 7.1.3 skip list (`$skip = array('wp-content', 'wp-includes/version.php')`).
   - Accurately models nested relative path exclusions: skips `wp-content/` and `wp-includes/version.php`, while promoting `wp-config.php` into the destination root due to its absence from `$skip`.
4. **`[SOURCE-VERIFIED]` Advisory Metadata & Assessment Distinction:**
   - Details official advisory metadata:
     - WordPress GitHub Security Advisory **GHSA-4w3m-c66w-69mg** rates the issue as **Low** severity with **no CVSS score**.
     - Wordfence subsequently assigned **CVE-2026-119977** with a **CVSS 3.1 score of 8.1 (High)**, identifying **CWE-367**.
   - Emphasizes that these assessments come from different sources and are reported separately.
   - Reaffirms that source behaviors do not independently establish successful remote code execution.

### Demonstration B: WordPress 7.1.4 (Patched Core Protections)
1. **`[SOURCE-VERIFIED]` Directory Randomization:**
   - Evaluates `get_working_dir_name()`: `$name . '-' . wp_generate_password(16, false)`.
   - Displays `[ILLUSTRATIVE]` sample paths (e.g., `wp-content/upgrade/wordpress-2husYbcukFNGL92I/`).
   - Highlights that a 16-character alphanumeric suffix drastically reduces path predictability.
2. **`[SIMULATED]` Guard Preservation & Auto-Creation:**
   - Demonstrates both 7.1.4 guard-file behaviors in unique temporary directories:
     - *Case 2A (Guard present):* Existing `index.php` is preserved (`if ('index.php' === $file['name']) continue;`).
     - *Case 2B (Guard absent):* WordPress creates an empty `index.php` (`Silence is golden`) if none exists.
3. **`[SIMULATED]` File Promotion Exclusion with Relative Path Modeling:**
   - Simulates `copy_dir()` iterating over staged files with the 7.1.4 exclusion list (`$skip = array('wp-content', 'wp-includes/version.php', 'wp-config.php')`).
   - Demonstrates that source files copy across, but `wp-config.php` is strictly skipped, leaving the production configuration untouched.

### Demonstration C: Upgrade Lifecycle & Timing Walkthrough
1. **Pipeline Architecture:**
   - Walks through the 5 update stages: preparation & download, directory creation, extraction (`setup-config.php` appears at +700–800ms), file sync (`copy_dir`), and cleanup.
2. **`[HISTORICAL]` Telemetry:**
   - Reports empirical staging folder lifespans captured during Phase 5:
     - WordPress 7.1.3: ~12.33 seconds (12,330.89 ms).
     - WordPress 7.1.4: ~11.32 seconds (11,321.29 ms).
3. **Hardware & Virtualization Disclaimer:**
   - Explicitly notes that these values reflect the local macOS Apple Silicon ARM64 / Colima / VZ virtualization environment with 9p/virtiofs filesystem translation. They do not represent fixed attack windows on bare-metal production hardware.

### Demonstration D: Server-Level Hardening Assessment
1. **`[LIVE]` Active Apache Configuration Verification:**
   - Executes `apache2ctl -t -D DUMP_INCLUDES` inside the container runtime to verify that `/etc/apache2/conf-enabled/upgrade-hardening.conf` is actively loaded in Apache's configuration tree.
2. **`[LIVE]` HTTP Assertion Test:**
   - Deploys temporary `probe.txt` and `probe.php` into `vulnerable/html/wp-content/upgrade/`.
   - Issues live HTTP requests to `127.0.0.1:8081`:
     - `/wp-content/upgrade/` &rarr; asserts `HTTP 403`.
     - `/wp-content/upgrade/probe.txt` &rarr; asserts `HTTP 403`.
     - `/wp-content/upgrade/probe.php` &rarr; asserts `HTTP 403`.
     - `/` (site root) &rarr; asserts `HTTP 200`.
   - Exits non-zero if any assertion fails. Probes are cleared automatically by signal trap.
3. **`[SOURCE-VERIFIED]` Scope & Defense-in-Depth Boundary:**
   - Explains that the tests prove the tested HTTP paths are blocked under this server configuration, providing perimeter defense-in-depth without implying universal mitigation across all web architectures.
4. **`[HISTORICAL]` Nginx 1.31+ Hardening Directive:**
   - Reviews the Nginx `location ^~ /wp-content/upgrade/` directive verified during Phase 6 disposable container tests.
5. **`[STATIC ANALYSIS]` OpenLiteSpeed Security Directive Review:**
   - Reviews the OpenLiteSpeed `context /wp-content/upgrade/ { type NULL; accessible no; }` rule, explicitly noting it was evaluated via static syntax review rather than container runtime.
6. **`[HISTORICAL]` Core Update Compatibility:**
   - Confirms that local WP-CLI core updates completed without errors because WordPress core updates operate via local server filesystem I/O (`WP_Filesystem`).
