# Official References: WordPress Security Advisory GHSA-4w3m-c66w-69mg

## 1. Official Security Advisory

* **Advisory Identifier:** [GHSA-4w3m-c66w-69mg](https://github.com/WordPress/wordpress-develop/security/advisories/GHSA-4w3m-c66w-69mg)
* **Official Title:** Race condition during core updates that could lead to remote code execution
* **CVE Identifier:** None assigned (Official advisory confirms no CVE is allocated).
* **Severity Rating:** **Low** (Official GitHub Advisory classification).
* **CWE Classification:** None assigned (Official advisory lists no CWE).
* **Researcher Credit:** Responsibly reported by **Umut Bayram**.
* **Release Date:** October 28, 2026.
* **Official Announcement:** [WordPress 7.1.4 Documentation](https://wordpress.org/documentation/wordpress-version/version-7-1-4/)

---

## 2. Version Support Matrix

The vulnerability affects all WordPress Core versions where full-package core update staging occurs without randomized pathing or promotion guard exclusions.

| Core Branch | Vulnerable Versions | Patched Security Release | Status |
| :--- | :--- | :--- | :--- |
| **7.1** | 7.1.0 – 7.1.3 | **7.1.4** | Latest active release |
| **7.0** | 7.0.0 – 7.0.7 | **7.0.8** | Maintained security backport |
| **6.9** | 6.9.0 – 6.9.10 | **6.9.11** | Maintained security backport |
| **6.8** | 6.8.0 – 6.8.11 | **6.8.12** | Maintained security backport |
| **6.7** | 6.7.0 – 6.7.10 | **6.7.11** | Maintained security backport |
| **6.6** | 6.6.0 – 6.6.10 | **6.6.11** | Maintained security backport |
| **6.5** | 6.5.0 – 6.5.13 | **6.5.14** | Maintained security backport |
| **6.4** | 6.4.0 – 6.4.13 | **6.4.14** | Maintained security backport |
| **6.3** | 6.3.0 – 6.3.13 | **6.3.14** | Maintained security backport |
| **6.2** | 6.2.0 – 6.2.14 | **6.2.15** | Maintained security backport |
| **6.1** | 6.1.0 – 6.1.15 | **6.1.16** | Maintained security backport |
| **6.0** | 6.0.0 – 6.0.17 | **6.0.18** | Maintained security backport |
| **5.9** | 5.9.0 – 5.9.19 | **5.9.20** | Maintained security backport |
| **5.8** | 5.8.0 – 5.8.18 | **5.8.19** | Maintained security backport |
| **5.7** | 5.7.0 – 5.7.20 | **5.7.21** | Maintained security backport |
| **5.6** | 5.6.0 – 5.6.22 | **5.6.23** | Maintained security backport |
| **5.5** | 5.5.0 – 5.5.23 | **5.5.24** | Maintained security backport |
| **5.4** | 5.4.0 – 5.4.24 | **5.4.25** | Maintained security backport |
| **5.3** | 5.3.0 – 5.3.26 | **5.3.27** | Maintained security backport |
| **5.2** | 5.2.0 – 5.2.29 | **5.2.30** | Maintained security backport |
| **5.1** | 5.1.0 – 5.1.27 | **5.1.28** | Maintained security backport |
| **5.0** | 5.0.0 – 5.0.30 | **5.0.31** | Maintained security backport |
| **4.9** | 4.9.0 – 4.9.34 | **4.9.35** | Maintained security backport |
| **4.8** | 4.8.0 – 4.8.33 | **4.8.34** | Maintained security backport |
| **4.7** | 4.7.0 – 4.7.38 | **4.7.39** | Maintained security backport |
| **<= 4.6** | All versions | End of Life | No security backports provided |

---

## 3. Official Source Revisions & Hashes

* **Trac Changeset:** `64877`
* **Direct Component Revisions:**
  * `wp-admin/includes/class-wp-upgrader.php`
  * `wp-admin/includes/update-core.php`
* **Integrity Proof:**
  * WordPress 7.1.3: `wordpress-7.1.3.zip` (SHA-256: `eb58a90ed55147f90f7bd55d0d3334960a69b7e8a4cc142c8ddfea8965218227`)
  * WordPress 7.1.4: `wordpress-7.1.4.zip` (SHA-256: `a147146c159362778ecbbbfb07bb20ddf4e85b130f971c824e656a8f938c5f87`)
