#!/usr/bin/env python3
"""
Phase 7.2 Screenshot Capture Utility
Uses Playwright to capture high-fidelity PNG screenshots for web pages and terminal runs.
"""

import os
import subprocess
from pathlib import Path
from playwright.sync_api import sync_playwright

SCRIPT_DIR = Path(__file__).resolve().parent
LAB_DIR = SCRIPT_DIR.parent
SCREENSHOTS_DIR = str(LAB_DIR / "evidence" / "phase-7-2" / "screenshots")
os.makedirs(SCREENSHOTS_DIR, exist_ok=True)

COLIMA_SOCK = Path.home() / ".colima" / "default" / "docker.sock"
DOCKER_ENV = os.environ.copy()
if "DOCKER_HOST" not in DOCKER_ENV and COLIMA_SOCK.exists():
    DOCKER_ENV["DOCKER_HOST"] = f"unix://{COLIMA_SOCK}"

def ansi_to_html(text):
    import re
    # Simple ANSI color conversion to HTML spans
    ansi_map = {
        '31m': '#ef4444',
        '32m': '#10b981',
        '33m': '#f59e0b',
        '34m': '#3b82f6',
        '35m': '#c084fc',
        '36m': '#38bdf8',
        '1m': 'font-weight: bold;',
        '0m': '</span>',
    }
    
    html = text.replace('&', '&amp;').replace('<', '&lt;').replace('>', '&gt;')
    
    # Replace ANSI codes
    html = re.sub(r'\033\[1m', '<span style="font-weight:bold;">', html)
    html = re.sub(r'\033\[31m', '<span style="color:#ef4444;">', html)
    html = re.sub(r'\033\[32m', '<span style="color:#10b981;">', html)
    html = re.sub(r'\033\[33m', '<span style="color:#f59e0b;">', html)
    html = re.sub(r'\033\[34m', '<span style="color:#38bdf8;">', html)
    html = re.sub(r'\033\[35m', '<span style="color:#c084fc;">', html)
    html = re.sub(r'\033\[36m', '<span style="color:#38bdf8;">', html)
    html = re.sub(r'\033\[0m', '</span>', html)
    # Remove any stray escape sequences
    html = re.sub(r'\033\[[0-9;]*[a-zA-Z]', '', html)
    return html

def render_terminal_window(title, command, output):
    content_html = ansi_to_html(output)
    return f"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body {{
    background-color: #0b0f19;
    margin: 0;
    padding: 30px;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
  }}
  .window {{
    width: 960px;
    background: #111827;
    border-radius: 10px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.6);
    border: 1px solid #1f2937;
    overflow: hidden;
  }}
  .titlebar {{
    background: #1f2937;
    padding: 10px 16px;
    display: flex;
    align-items: center;
    border-bottom: 1px solid #374151;
  }}
  .buttons {{
    display: flex;
    gap: 8px;
  }}
  .btn {{
    width: 12px;
    height: 12px;
    border-radius: 50%;
  }}
  .close {{ background: #ef4444; }}
  .min {{ background: #f59e0b; }}
  .max {{ background: #10b981; }}
  .title {{
    flex-grow: 1;
    text-align: center;
    color: #9ca3af;
    font-size: 13px;
    font-weight: 500;
  }}
  .terminal-body {{
    padding: 20px;
    color: #e5e7eb;
    font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace;
    font-size: 13px;
    line-height: 1.5;
    white-space: pre-wrap;
    word-break: break-all;
  }}
  .prompt {{
    color: #38bdf8;
    margin-bottom: 12px;
  }}
</style>
</head>
<body>
  <div class="window">
    <div class="titlebar">
      <div class="buttons">
        <div class="btn close"></div>
        <div class="btn min"></div>
        <div class="btn max"></div>
      </div>
      <div class="title">{title}</div>
    </div>
    <div class="terminal-body">
      <div class="prompt"><span style="color:#10b981;">mac@sec-lab</span>:<span style="color:#38bdf8;">~/WP7.1.4-rce-lab</span>$ {command}</div>
      {content_html}
    </div>
  </div>
</body>
</html>"""

def main():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        
        # 1. Capture WordPress 7.1.3 Browser View
        print("Capturing 02-wordpress-713-browser.png...")
        page = browser.new_page(viewport={"width": 1280, "height": 800})
        page.goto("http://127.0.0.1:8081/", wait_until="networkidle")
        page.screenshot(path=os.path.join(SCREENSHOTS_DIR, "02-wordpress-713-browser.png"))
        page.close()

        # 2. Capture WordPress 7.1.4 Browser View
        print("Capturing 03-wordpress-714-browser.png...")
        page = browser.new_page(viewport={"width": 1280, "height": 800})
        page.goto("http://127.0.0.1:8082/", wait_until="networkidle")
        page.screenshot(path=os.path.join(SCREENSHOTS_DIR, "03-wordpress-714-browser.png"))
        page.close()

        # 3. Capture Research Dashboard (demo/index.html)
        print("Capturing 08-research-dashboard.png...")
        page = browser.new_page(viewport={"width": 1280, "height": 1100})
        dashboard_url = (LAB_DIR / "demo" / "index.html").as_uri()
        page.goto(dashboard_url, wait_until="networkidle")
        page.screenshot(path=os.path.join(SCREENSHOTS_DIR, "08-research-dashboard.png"), full_page=True)
        page.close()

        # 4. Capture 01-environment-baseline.png
        print("Capturing 01-environment-baseline.png...")
        cmd_out = subprocess.check_output(
            'docker ps --format "table {{.Names}}\\t{{.Image}}\\t{{.Status}}\\t{{.Ports}}"',
            shell=True, text=True, env=DOCKER_ENV
        )
        html = render_terminal_window("Environment Baseline - Docker Container State", "docker ps", cmd_out)
        page = browser.new_page(viewport={"width": 1040, "height": 450})
        page.set_content(html)
        page.screenshot(path=os.path.join(SCREENSHOTS_DIR, "01-environment-baseline.png"))
        page.close()

        # 5. Capture 04-demo-a-results.png
        print("Capturing 04-demo-a-results.png...")
        cmd_out = subprocess.check_output(
            f"{LAB_DIR}/demo/run_demo.sh --demo-a",
            shell=True, text=True, env=DOCKER_ENV
        )
        html = render_terminal_window("Demonstration A - WordPress 7.1.3 Baseline Behavior", "./demo/run_demo.sh --demo-a", cmd_out)
        page = browser.new_page(viewport={"width": 1040, "height": 700})
        page.set_content(html)
        page.screenshot(path=os.path.join(SCREENSHOTS_DIR, "04-demo-a-results.png"))
        page.close()

        # 6. Capture 05-demo-b-results.png
        print("Capturing 05-demo-b-results.png...")
        cmd_out = subprocess.check_output(
            f"{LAB_DIR}/demo/run_demo.sh --demo-b",
            shell=True, text=True, env=DOCKER_ENV
        )
        html = render_terminal_window("Demonstration B - WordPress 7.1.4 Patched Protections", "./demo/run_demo.sh --demo-b", cmd_out)
        page = browser.new_page(viewport={"width": 1040, "height": 700})
        page.set_content(html)
        page.screenshot(path=os.path.join(SCREENSHOTS_DIR, "05-demo-b-results.png"))
        page.close()

        # 7. Capture 06-demo-c-timings.png
        print("Capturing 06-demo-c-timings.png...")
        cmd_out = subprocess.check_output(
            f"{LAB_DIR}/demo/run_demo.sh --demo-c",
            shell=True, text=True, env=DOCKER_ENV
        )
        html = render_terminal_window("Demonstration C - Lifecycle & Timing Telemetry", "./demo/run_demo.sh --demo-c", cmd_out)
        page = browser.new_page(viewport={"width": 1040, "height": 550})
        page.set_content(html)
        page.screenshot(path=os.path.join(SCREENSHOTS_DIR, "06-demo-c-timings.png"))
        page.close()

        # 8. Capture 07-apache-live-tests.png
        print("Capturing 07-apache-live-tests.png...")
        cmd_out = subprocess.check_output(
            f"{LAB_DIR}/demo/run_demo.sh --demo-d",
            shell=True, text=True, env=DOCKER_ENV
        )
        html = render_terminal_window("Demonstration D - Live Server Hardening Assertions", "./demo/run_demo.sh --demo-d", cmd_out)
        page = browser.new_page(viewport={"width": 1040, "height": 650})
        page.set_content(html)
        page.screenshot(path=os.path.join(SCREENSHOTS_DIR, "07-apache-live-tests.png"))
        page.close()

        # 9. Capture 09-final-environment-health.png
        print("Capturing 09-final-environment-health.png...")
        health_cmd = (
            'echo "=== Container Status ===" && '
            'docker ps --format "table {{.Names}}\\t{{.Status}}\\t{{.Ports}}" && '
            'echo "" && echo "=== HTTP Verification ===" && '
            'curl -s -o /dev/null -w "WordPress 7.1.3 (Port 8081): HTTP %{http_code}\\n" http://127.0.0.1:8081/ && '
            'curl -s -o /dev/null -w "WordPress 7.1.4 (Port 8082): HTTP %{http_code}\\n" http://127.0.0.1:8082/ && '
            'curl -s -o /dev/null -w "Hardened Upgrade Dir (Port 8081): HTTP %{http_code}\\n" http://127.0.0.1:8081/wp-content/upgrade/'
        )
        cmd_out = subprocess.check_output(health_cmd, shell=True, text=True, env=DOCKER_ENV)
        html = render_terminal_window("Final Environment Health & Assertion Verification", "verify_health.sh", cmd_out)
        page = browser.new_page(viewport={"width": 1040, "height": 450})
        page.set_content(html)
        page.screenshot(path=os.path.join(SCREENSHOTS_DIR, "09-final-environment-health.png"))
        page.close()

        browser.close()
        print("All screenshots successfully captured!")

if __name__ == "__main__":
    main()

