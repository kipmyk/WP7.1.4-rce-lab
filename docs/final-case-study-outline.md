# Proposed Case Study Outline
## WordPress Upgrade-Window Vulnerability: Source Analysis, Local Validation and Server-Level Defense-in-Depth

**Document Purpose:** Proposed editorial structure for the upcoming engineering case study.  
**Publication Status:** Outline draft for review. (No full article written or published at this stage).

---

### Section 1: Introduction & Official Advisory Context
* **Advisory Reference:** GitHub Advisory **GHSA-4w3m-c66w-69mg** (published October 28, 2026).
* **Official Classification:** Low severity, no CVE or CWE assigned.
* **Researcher Credit:** Responsibly reported by Umut Bayram.
* **Affected Versions:** WordPress Core versions 4.7 through 7.1.3 across maintained release branches.
* **Patched Releases:** WordPress 7.1.4 (with backports across 24 legacy branches down to 4.7.39).

### Section 2: Architecture of the WordPress Core Upgrade Workflow
* Overview of the two-stage upgrade pipeline: `class-wp-upgrader.php` (unpacking) and `update-core.php` (synchronization).
* The role of `wp-content/upgrade/` as the default staging folder.
* Why distribution packages contain unconfigured installer files (`wp-admin/setup-config.php`) without `wp-config.php`.
* The filesystem transition: how files move from staging into the production root.

### Section 3: Source-Level Code Comparison (7.1.3 vs. 7.1.4)
* **Path Generation:** From deterministic `basename()` derivation to `get_working_dir_name()` with a 16-character pseudo-random alphanumeric suffix.
* **Guard File Handling:** Pre-unpack cleanup loops in 7.1.3 (which stripped `index.php`) vs. 7.1.4 (which preserves and auto-generates `index.php`).
* **File Promotion Boundary:** The update-core copy loop in 7.1.3 (`$skip = array('wp-content', 'wp-includes/version.php')`) vs. 7.1.4 (`$skip = array('wp-content', 'wp-includes/version.php', 'wp-config.php')`).

### Section 4: Phase 4 Controlled Security Property Testing
* Methodology: Non-destructive testing across isolated local Docker environments.
* Summary of the 8 property tests (directory listing, guard preservation, naming predictability, static file access, PHP execution, config exclusion, cleanup, upgrade integrity).
* Core findings: Confirmation that standard Apache/PHP runtimes serve static files and execute PHP inside `wp-content/upgrade/` by default.

### Section 5: Phase 5 Real-Time Upgrade Lifecycle Observations
* Implementation of the non-invasive 10ms filesystem observer harness.
* Direct empirical observations:
  * Staging directory lifetime measured at ~12.33s (7.1.3) and ~11.32s (7.1.4) in the tested Docker lab.
  * `setup-config.php` detected on disk within ~700–800ms of folder creation.
* Analysis: Why the patch does not eliminate the physical timing window, but alters path predictability and file promotion rules.

### Section 6: Phase 6 Web Server Hardening & Defense-in-Depth
* Comparative evaluation across three server architectures:
  * **Apache 2.4.69:** Runtime testing of VirtualHost `<Directory>` blocks with `Require all denied` and `Options -Indexes`.
  * **Nginx 1.31.8:** Runtime testing of `location ^~ /wp-content/upgrade/` with precedence modifiers to bypass PHP-FPM.
  * **OpenLiteSpeed:** Architectural static analysis of `context /wp-content/upgrade/` (`type NULL`, `accessible no`).
* Regression testing: Empirical proof that blocking HTTP access to `/wp-content/upgrade/` has zero impact on legitimate WP-CLI core updates.

### Section 7: What Was Successfully Demonstrated
* Confirmed source-level mechanics in WordPress 7.1.3 and 7.1.4.
* Confirmed presence of an observable ~11–12 second filesystem staging window during local updates.
* Confirmed web accessibility and PHP script execution in unhardened upgrade folders.
* Confirmed that 7.1.4 successfully excludes `wp-config.php` from file promotion.
* Confirmed that server-level HTTP restrictions effectively block external requests without breaking local core updates.

### Section 8: What Was Not Demonstrated (Explicit Scope Disclosure)
* **Full End-to-End RCE Was Not Reproduced:** No weaponized exploits were created, no database hijacking was performed, and no unauthorized command execution was attempted.
* Environmental timing specificity: The 11–12 second window is specific to the tested Docker/macOS environment and is not a universal metric.
* OpenLiteSpeed evaluation was limited to configuration static analysis, not live runtime validation.

### Section 9: Practical Engineering Recommendations
* **Primary Action:** Upgrade immediately to WordPress 7.1.4 (or respective branch backport).
* **Defense-in-Depth:** Apply web server perimeter rules (Apache `<Directory>` / Nginx `^~` location) to deny direct HTTP access to `/wp-content/upgrade/`.
* **Hosting Configuration Guidance:** Ensure web servers do not enable directory indexing (`Options -Indexes`, `autoindex off`) and do not route scripts inside upload or upgrade directories to PHP engines.

### Section 10: Research Limitations & Conclusions
* Summary of findings.
* Reflection on the value of combining source-code auditing, non-destructive experimental validation, and server-level defense-in-depth.

