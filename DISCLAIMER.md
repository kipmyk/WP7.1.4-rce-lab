# Security Research & Responsible Use Disclaimer

## 1. Educational and Defensive Purpose Only

This repository, including all accompanying documentation, demonstration scripts, configuration files, and telemetry logs, is published strictly for **educational, defensive, and academic security research purposes**. The primary goal is to study software upgrade mechanics, race-condition windows, and defense-in-depth web server hardening in a strictly isolated local laboratory environment.

---

## 2. Laboratory Isolation & Safe Testing

All experiments, behavioral simulations, and verification tests were performed entirely within private, loopback-bound (`127.0.0.1`) Docker containers isolated from production infrastructure and external networks. Under no circumstances should any script or technique demonstrated here be deployed against systems, servers, or networks without explicit, documented authorization from the system owners.

---

## 3. Exploit Reproduction & Weaponization Limitations

* **No Exploit Payload:** This repository **does not contain weaponized exploits**, automated attack tooling, reverse shells, or payload generators.
* **Full RCE Not Reproduced:** Controlled testing confirmed specific source-code behaviors and staging directory lifespans (~11–12 seconds in this virtualized lab); **the complete unauthenticated Remote Code Execution (RCE) chain was not reproduced**.
* **Simulation Distinction:** Staged configuration overwrites and guard-file deletions demonstrated in the harness are **filesystem simulations of underlying source logic**, not captures of active attacker exploitation.

---

## 4. No Warranty & Limitation of Liability

* **No Warranty:** The contents of this repository are provided on an "AS IS" basis, without warranty of any kind, express or implied, including but not limited to the warranties of merchantability, fitness for a particular purpose, or non-infringement.
* **Limitation of Liability:** In no event shall the authors, contributors, or copyright holders be liable for any claim, damages, or other liability, whether in an action of contract, tort, or otherwise, arising from, out of, or in connection with the software, code, configurations, or the use or other dealings in this repository.

---

## 5. Third-Party Attribution & Non-Affiliation

* WordPress is a registered trademark of the WordPress Foundation. This research project is independent and is **not affiliated with, endorsed by, or sponsored by the WordPress Foundation, Automattic, or Wordfence**.
* Security advisories referenced:
  * **GHSA-4w3m-c66w-69mg:** Official WordPress GitHub Security Advisory (Severity: Low, no CVSS score published, credited to Umut Bayram).
  * **CVE-2026-119977:** Assigned by Wordfence (CVSS 3.1: 8.1 High, CWE-367).

---

## 6. Responsible Disclosure & Ethical Conduct

This research conforms to standard industry principles of responsible vulnerability research. The investigated changes were publicly released by the WordPress project in **WordPress 7.1.4** on October 15, 2026. Security practitioners using this material are expected to act in compliance with applicable local and international computer crime laws (e.g., Computer Fraud and Abuse Act in the US, Computer Misuse Act in the UK, Computer Misuse and Cybercrimes Act in Kenya).
