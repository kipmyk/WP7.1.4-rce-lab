# Phase 7.2: Video Recording Instructions & Release Notice

## 1. Video Recording Status

* **Current Package Status:** Video recording is **outstanding**. This package is released as a **screenshot-and-log evidence package**.
* **Technical Reason:** macOS Transparency, Consent, and Control (TCC) security architecture requires interactive user consent for display framebuffer recording (`AVCaptureScreenInput` / `kTCCServiceScreenCapture`). Unattended daemon/subshell execution lacks interactive display access and window server session tokens.

---

## 2. Manual Operator Recording Instructions (When Performing Video Capture)

To capture a video walkthrough of the automated demonstration manually:

### Step 1: Open macOS Native Screen Recorder
1. Press `Cmd + Shift + 5` to open the macOS Screenshot / Screen Recording toolbar (or launch QuickTime Player &rarr; **File** &rarr; **New Screen Recording**).
2. Select **Record Selected Portion** and drag the frame over your terminal window.

### Step 2: Start Terminal Execution
In your terminal, navigate to the project directory and run:
```bash
cd /path/to/WP7.1.4-rce-lab
./demo/run_demo.sh --all
```

### Step 3: Record Demonstrations
* **Demonstration A:** Highlights predictable naming (`wordpress.zip` &rarr; `wordpress/`), guard file removal, and config overwrite with relative path modeling.
* **Demonstration B:** Shows 16-character randomized suffix, guard preservation/creation, and config skipping with relative path modeling.
* **Demonstration C:** Explains 5-stage update pipeline and presents historical timing measurements.
* **Demonstration D:** Verifies active Apache configuration tree and executes live HTTP assertion tests on port `8081` returning `HTTP 403`.

### Step 4: Save Recording
1. Press `Cmd + Control + Esc` to stop recording.
2. Save the output file as:
   ```
   evidence/phase-7-2/video/phase-7-complete-walkthrough.mov
   ```
