# OpenLiteSpeed / LiteSpeed Configuration Analysis

**Platform Status:** Configuration-Level Static Analysis & Architectural Review (No live runtime deployed in test matrix).

---

## 1. Native Context Directive Evaluation

OpenLiteSpeed (OLS) structures directory access controls through **Context** blocks configured within virtual host definitions (`$SERVER_ROOT/conf/vhosts/<VHost>/vhconf.conf`).

### Recommended Definition:
```
context /wp-content/upgrade/ {
  type                    NULL
  location                /var/www/html/wp-content/upgrade/
  allowBrowse             0
  accessible              no
  addDefaultCharset       off
}
```

### Architectural Properties:
* **`type NULL`:** Completely drops request processing. The server does not map the URI to filesystem handlers or static file serving.
* **`accessible no`:** Explicitly responds with HTTP 403 Forbidden to any client requesting URIs beginning with `/wp-content/upgrade/`.
* **`allowBrowse 0`:** Disables directory index generation.
* **PHP Handler Bypass:** Because `type NULL` terminates request mapping, OpenLiteSpeed's external FastCGI / LSPHP application handler is never triggered, preventing script execution.

---

## 2. Apache-Compatible Rewrite Rule Evaluation

OpenLiteSpeed supports Apache-style `.htaccess` rewrite rules when rewrite parsing is enabled (`vhconf.conf: rewrite { enable 1 }`).

### Root Rule Implementation:
```apache
<IfModule Litespeed>
RewriteEngine On
RewriteRule ^wp-content/upgrade/.* - [F,L]
</IfModule>
```

### Execution Properties:
* `[F]` instructs OpenLiteSpeed to immediately return HTTP 403 Forbidden.
* `[L]` halts rule processing, preventing subsequent WordPress rewrite rules from routing the request through `index.php`.
* **Limitation:** OLS caches rewrite rules in memory. When changes are made to `.htaccess`, a graceful restart or cache invalidation (`touch /tmp/lshttpd/.rtcache`) is required.

---

## 3. Comparison with Core Update Operations

* Because legitimate WordPress updates are executed entirely via local filesystem I/O (`WP_Filesystem` in PHP CLI or web process), blocking HTTP access in OpenLiteSpeed has **zero impact** on legitimate core update operations.

