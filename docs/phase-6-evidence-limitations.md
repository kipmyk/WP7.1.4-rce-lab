# Phase 6 Evidence and Research Limitations

This document explicitly defines the boundaries, empirical observations, analytical inferences, and limitations of the security research conducted in Phase 6.

---

## 1. Directly Observed Results (Empirical Evidence)

The following outcomes were directly observed and verified through repeatable tests in isolated local Docker runtimes:

1. **Apache HTTP Restriction:**
   * Apache 2.4.69 in container `wp_vuln_app` enforced `Require all denied` on `/var/www/html/wp-content/upgrade`, returning `HTTP/1.1 403 Forbidden` for directory requests, static probes (`probe.txt`), and PHP probes (`probe.php`).
   * Apache logged explicit authorization denials in `error.log` (`AH01630: client denied by server configuration`).
2. **Nginx HTTP Restriction:**
   * Nginx 1.31.8 in disposable container `nginx_hardening_test` enforced `location ^~ /wp-content/upgrade/ { deny all; return 403; }`, returning `HTTP/1.1 403 Forbidden` for directory requests, static probes, and PHP probes.
   * Requests to the root (`/`) were successfully proxied and returned `HTTP/1.1 200 OK`.
3. **Core Update Compatibility:**
   * A legitimate WP-CLI core update completed successfully inside `wp_vuln_app` while the Apache restriction on `/wp-content/upgrade/` was actively enforced.
4. **Transient Lifecycle Duration (Phase 5 Baseline):**
   * Staging directories existed on disk for approximately 12.33 seconds in 7.1.3 and 11.32 seconds in 7.1.4 under the tested Docker environment.

---

## 2. Results Inferred from Configuration Analysis (Not Runtime Validated)

1. **OpenLiteSpeed Context Directives:**
   * The OpenLiteSpeed configuration (`type NULL`, `accessible no`) was evaluated through architectural review and official LiteSpeed documentation.
   * **Limitation:** OpenLiteSpeed was **not executed in a live runtime container** in this lab. The conclusion that it drops requests and bypasses LSPHP is an inference based on configuration specification, subject to future runtime validation.
2. **Alternative Apache Handlers:**
   * Tests were performed using Apache's standard prefork/PHP module (`mod_php`). Environments using `mod_proxy_fcgi` or external PHP-FPM pools were not tested.

---

## 3. Behaviors Not Tested

1. **Full End-to-End RCE Attack Chain:**
   * No attempts were made to race the upgrade window with an exploit script, submit external database credentials via `setup-config.php`, or hijack the database connection.
   * Safety protocols strictly prohibited weaponized testing.
2. **Non-Standard Upgrade Methods:**
   * Update compatibility was tested exclusively via WP-CLI using a local zip package.
   * Core updates triggered via FTP/FTPS/SSH credentials in the WordPress admin UI were not evaluated.
   * Third-party plugin and theme update routines were not evaluated.
3. **Non-Standard URL Mappings:**
   * Testing was performed strictly against direct URIs (`/wp-content/upgrade/...`).
   * Configurations involving URL rewrites, symlinks, alias directives, or alternate virtual hosts mapping into `wp-content/upgrade/` were not tested.

---

## 4. Behaviors Requiring Additional Validation

1. **Cross-Platform Lifecycle Timings:**
   * The observed 11–12 second staging lifespan is specific to this lab's virtualized Docker environment on macOS (Colima/VZ filesystem bridge).
   * Servers equipped with high-speed NVMe storage, tmpfs mounts, or low-latency local drives may experience significantly shorter staging windows, while slow shared disks or network-attached storage (NFS) may experience longer windows.
2. **Distributed & Multi-Server WordPress Installations:**
   * Deployments utilizing shared NFS storage for `wp-content/`, container orchestration (Kubernetes), or object storage drop-ins require independent verification of upgrade directory behavior.
3. **PRNG Behavior across Legacy PHP Versions:**
   * The 16-character randomized suffix was tested on PHP 8.2 and PHP 8.5. Systems running legacy PHP runtimes (e.g. PHP 7.x) where `random_int()` might experience PRNG fallbacks require separate validation.

