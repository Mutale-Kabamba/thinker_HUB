"""Capture think.er HUB screenshots via Kimi WebBridge for the user manual.

Starts a temporary artisan server, logs into each Filament panel through the
user's real browser, screenshots key pages, then shuts everything down.
Usage: python capture_screenshots.py <role>   where role = student|instructor|admin
"""
import json
import subprocess
import sys
import time
import urllib.request
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
OUT = ROOT / "user-manual" / "screenshots"
OUT.mkdir(parents=True, exist_ok=True)
DAEMON = "http://127.0.0.1:10086/command"
PORT = 8123
BASE = f"http://127.0.0.1:{PORT}"
PHP = r"C:\xampp\php\php.exe"

ROLE = sys.argv[1]

CREDS = {
    "student": ("learn", "learn/login", "digitalskills@play-itforward.org"),
    "instructor": ("teach", "teach/login", "oristudio.mgt@gmail.com"),
    "admin": ("manage", "login", "oristudio.01@gmail.com"),
}

PAGES = {
    "student": [
        ("overview", "/learn/overview"),
        ("courses", "/learn/courses"),
        ("learning-resources", "/learn/learning-resources"),
        ("materials", "/learn/materials"),
        ("schedule", "/learn/schedule"),
        ("quizzes", "/learn/quizzes"),
        ("community", "/learn/community"),
        ("certificates", "/learn/certificates"),
        ("opportunities", "/learn/opportunities"),
    ],
    "instructor": [
        ("overview", "/teach/instructor-overview"),
        ("analytics", "/teach/analytics"),
        ("broadcasts", "/teach/broadcasts"),
        ("schedule", "/teach/schedule"),
        ("courses", "/teach/course-resource/courses"),
        ("sessions", "/teach/course-session-resource/course-sessions"),
        ("materials", "/teach/learning-material-resource/learning-materials"),
        ("videos", "/teach/resource-video-resource/resource-videos"),
        ("students", "/teach/students"),
    ],
    "admin": [
        ("dashboard", "/manage"),
        ("students", "/manage/students"),
        ("courses", "/manage/courses"),
        ("sessions", "/manage/course-sessions"),
        ("videos", "/manage/resource-videos"),
        ("materials", "/manage/learning-materials"),
    ],
}


def cmd(action, args, timeout=60, retries=4):
    data = json.dumps({"action": action, "args": args, "session": "user-manual"}).encode()
    last = None
    for attempt in range(retries):
        try:
            req = urllib.request.Request(DAEMON, data=data, headers={"Content-Type": "application/json"})
            with urllib.request.urlopen(req, timeout=timeout) as r:
                return json.loads(r.read())
        except Exception as e:
            last = e
            time.sleep(2 + attempt * 2)
    print(f"WARN {action}: {last}", flush=True)
    return {"ok": False, "error": str(last)}


def shot(name):
    path = str(OUT / f"{ROLE}-{name}.png")
    res = cmd("screenshot", {"format": "png", "path": path}, timeout=90)
    ok = res.get("ok") and res.get("data", {}).get("sizeBytes", 0) > 10000
    print(("OK  " if ok else "FAIL") + f" {ROLE}-{name}.png", flush=True)
    return ok


def main():
    prefix, login_path, email = CREDS[ROLE]
    server = subprocess.Popen(
        [PHP, "artisan", "serve", f"--port={PORT}", "--no-reload"],
        cwd=ROOT, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL,
    )
    try:
        time.sleep(4)
        # Open a throwaway tab first so cdp has a target, then clear cookies
        cmd("navigate", {"url": "about:blank", "newTab": True,
                         "group_title": "User manual screenshots"})
        time.sleep(2)
        cmd("cdp", {"method": "Network.clearBrowserCookies", "params": {}})
        time.sleep(1)

        # Login
        cmd("navigate", {"url": f"{BASE}/{login_path}"})
        time.sleep(4)
        cmd("fill", {"selector": 'input[type="email"]', "value": email})
        cmd("fill", {"selector": 'input[type="password"]', "value": "password"})
        cmd("click", {"selector": 'button[type="submit"]'})
        time.sleep(5)
        snap = cmd("snapshot", {})
        url = snap.get("data", {}).get("url", "")
        print(f"after-login url: {url}", flush=True)
        if "/login" in url:
            print("LOGIN FAILED", flush=True)
            sys.exit(1)

        for name, path in PAGES[ROLE]:
            cmd("navigate", {"url": BASE + path})
            time.sleep(4)
            loc = cmd("evaluate", {"code": "location.href"})
            href = loc.get("data", {}).get("value", "")
            if "/login" in str(href):
                print(f"FAIL {ROLE}-{name}.png (redirected to login)", flush=True)
                continue
            shot(name)
    finally:
        server.terminate()
        try:
            server.wait(timeout=10)
        except subprocess.TimeoutExpired:
            server.kill()
        print("server stopped", flush=True)


if __name__ == "__main__":
    main()
