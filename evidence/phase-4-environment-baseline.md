# Phase 4 Environment Baseline & Pre-Test State

* **Timestamp:** 2026-11-01T08:23:00+03:00
* **Host OS:** macOS 27.0.1 (Darwin 27.0.0, ARM64 Apple Silicon)
* **Runtime:** Colima VM (v0.6+) / Docker Engine 29.9.2
* **Project Directory:** `/Users/mac/Code/Security/WP7.1.4-rce-lab/`

---

## 1. Container Topology & Status

```
NAMES          STATUS         PORTS                    NETWORKS
wp_vuln_app    Up 15 minutes  127.0.0.1:8081->80/tcp   wp714-rce-lab_vuln_net
wp_patch_app   Up 15 minutes  127.0.0.1:8082->80/tcp   wp714-rce-lab_patch_net
wp_patch_db    Up 15 minutes  3306/tcp                 wp714-rce-lab_patch_net
wp_vuln_db     Up 15 minutes  3306/tcp                 wp714-rce-lab_vuln_net
```

### Network & Port Isolation Proof:
* All published ports are strictly bound to loopback `127.0.0.1` (`8081` and `8082`).
* Zero host services listen on `0.0.0.0`.
* Database port `3306` is internal to each Docker bridge network (`vuln_net` and `patch_net`) and is not published to the host.
* No connectivity exists between `vuln_net` and `patch_net`.

---

## 2. WordPress Installation State

### Vulnerable Instance (7.1.3)
* **Local URL:** `http://127.0.0.1:8081/`
* **Core Version:** `7.1.3` (verified in `wp-includes/version.php`)
* **Database Connection:** Verified (`wp_vulnerable` via `wp_vuln_db`)
* **Initial HTTP Baseline:**
  ```http
  HTTP/1.1 200 OK
  Server: Apache/2.4.69 (Debian)
  X-Powered-By: PHP/8.2.35
  Link: <http://127.0.0.1:8081/index.php?rest_route=/>; rel="https://api.w.org/"
  Content-Type: text/html; charset=UTF-8
  ```

### Patched Instance (7.1.4)
* **Local URL:** `http://127.0.0.1:8082/`
* **Core Version:** `7.1.4` (verified in `wp-includes/version.php`)
* **Database Connection:** Verified (`wp_patched` via `wp_patch_db`)
* **Initial HTTP Baseline:**
  ```http
  HTTP/1.1 200 OK
  Server: Apache/2.4.69 (Debian)
  X-Powered-By: PHP/8.2.35
  Link: <http://127.0.0.1:8082/index.php?rest_route=/>; rel="https://api.w.org/"
  Content-Type: text/html; charset=UTF-8
  ```

---

## 3. Snapshot & Recovery Assets

Prior to executing any test operations, complete full-stack backups were generated:

| Asset | Path | Description |
| :--- | :--- | :--- |
| **WP 7.1.3 Filesystem** | `backups/vulnerable-html-backup.tar.gz` | Gzipped archive of `vulnerable/html` |
| **WP 7.1.4 Filesystem** | `backups/patched-html-backup.tar.gz` | Gzipped archive of `patched/html` |
| **WP 7.1.3 Database** | `backups/vulnerable-db-backup.sql` | SQL dump of database `wp_vulnerable` |
| **WP 7.1.4 Database** | `backups/patched-db-backup.sql` | SQL dump of database `wp_patched` |

