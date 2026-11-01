# Phase 7 Demonstration HTTP Response Captures

Raw HTTP responses captured during the automated local demonstration (`demo/run_demo.sh --demo-d`):

---

## 1. Directory Request on Hardened Apache
```http
GET /wp-content/upgrade/ HTTP/1.1
Host: 127.0.0.1:8081
User-Agent: curl/8.9.1
Accept: */*

HTTP/1.1 403 Forbidden
Date: Sun, 01 Nov 2026 06:10:52 GMT
Server: Apache/2.4.69 (Debian)
Content-Length: 316
Content-Type: text/html; charset=iso-8859-1

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html><head><title>403 Forbidden</title></head><body>
<h1>Forbidden</h1>
<p>You don't have permission to access this resource.</p>
</body></html>
```

---

## 2. Static File Probe on Hardened Apache
```http
GET /wp-content/upgrade/probe.txt HTTP/1.1
Host: 127.0.0.1:8081
User-Agent: curl/8.9.1
Accept: */*

HTTP/1.1 403 Forbidden
Date: Sun, 01 Nov 2026 06:10:52 GMT
Server: Apache/2.4.69 (Debian)
Content-Length: 316
Content-Type: text/html; charset=iso-8859-1

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html><head><title>403 Forbidden</title></head><body>
<h1>Forbidden</h1>
<p>You don't have permission to access this resource.</p>
</body></html>
```

---

## 3. PHP Script Probe on Hardened Apache
```http
GET /wp-content/upgrade/probe.php HTTP/1.1
Host: 127.0.0.1:8081
User-Agent: curl/8.9.1
Accept: */*

HTTP/1.1 403 Forbidden
Date: Sun, 01 Nov 2026 06:10:52 GMT
Server: Apache/2.4.69 (Debian)
Content-Length: 316
Content-Type: text/html; charset=iso-8859-1

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html><head><title>403 Forbidden</title></head><body>
<h1>Forbidden</h1>
<p>You don't have permission to access this resource.</p>
</body></html>
```

---

## 4. Normal Site Frontend Request
```http
HEAD / HTTP/1.1
Host: 127.0.0.1:8081
User-Agent: curl/8.9.1
Accept: */*

HTTP/1.1 200 OK
Date: Sun, 01 Nov 2026 06:10:52 GMT
Server: Apache/2.4.69 (Debian)
X-Powered-By: PHP/8.2.35
Link: <http://127.0.0.1:8081/index.php?rest_route=/>; rel="https://api.w.org/"
Content-Type: text/html; charset=UTF-8
```

