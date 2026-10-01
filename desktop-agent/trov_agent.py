"""
TROV desktop monitoring component.

Runs on a video editor's office computer. While the editor has an open work
session in TROV, it captures the screen every 10-20 minutes and reports the
keyboard/mouse activity for that interval to the TROV server. It captures
nothing when there is no open session.

Usage:
    python trov_agent.py --email vince@kpc.test
    python trov_agent.py --email vince@kpc.test --once            # one capture, then exit
    python trov_agent.py --email vince@kpc.test --interval 20      # fixed 20s interval (testing)

The password is read from the TROV_PASSWORD environment variable, the
--password flag, or an interactive prompt.
"""

import argparse
import io
import os
import random
import sys
import threading
import time
from getpass import getpass

import requests
import mss
import mss.tools
from pynput import keyboard, mouse


class ActivityMeter:
    """Counts keyboard/mouse events and how much of each interval was active."""

    def __init__(self):
        self._lock = threading.Lock()
        self.keys = 0
        self.mouse = 0
        self._last_input = time.monotonic()
        self._active_seconds = 0
        self._stop = False

    def _touch(self):
        self._last_input = time.monotonic()

    def on_key(self, _key):
        with self._lock:
            self.keys += 1
        self._touch()

    def on_click(self, _x, _y, _button, pressed):
        if pressed:
            with self._lock:
                self.mouse += 1
        self._touch()

    def on_scroll(self, *_args):
        with self._lock:
            self.mouse += 1
        self._touch()

    def on_move(self, *_args):
        self._touch()

    def start(self):
        keyboard.Listener(on_press=self.on_key).start()
        mouse.Listener(on_click=self.on_click, on_scroll=self.on_scroll,
                       on_move=self.on_move).start()
        threading.Thread(target=self._sampler, daemon=True).start()

    def _sampler(self):
        # Each second counts as "active" if input happened in the last 2 seconds.
        while not self._stop:
            time.sleep(1)
            if time.monotonic() - self._last_input <= 2:
                self._active_seconds += 1

    def snapshot_and_reset(self, elapsed):
        with self._lock:
            keys, mouse_ = self.keys, self.mouse
            self.keys = self.mouse = 0
        active = self._active_seconds
        self._active_seconds = 0
        level = round(active / elapsed * 100) if elapsed > 0 else 0
        return keys, mouse_, max(0, min(100, level))


class TrovAgent:
    def __init__(self, server, email, password, device):
        self.server = server.rstrip("/")
        self.email = email
        self.password = password
        self.device = device
        self.http = requests.Session()
        self.http.headers["Accept"] = "application/json"
        self.token = None

    def login(self):
        r = self.http.post(f"{self.server}/api/agent/login", data={
            "email": self.email, "password": self.password, "device": self.device,
        }, timeout=20)
        if r.status_code != 200:
            raise SystemExit(f"Login failed ({r.status_code}): {r.text}")
        self.token = r.json()["token"]
        self.http.headers["Authorization"] = f"Bearer {self.token}"
        print(f"[trov] signed in as {r.json()['user']['name']}")

    def current_session(self):
        r = self.http.get(f"{self.server}/api/session/current", timeout=20)
        if r.status_code == 401:
            self.login()
            r = self.http.get(f"{self.server}/api/session/current", timeout=20)
        r.raise_for_status()
        return r.json().get("session")

    def capture_png(self):
        with mss.MSS() as sct:
            shot = sct.grab(sct.monitors[0])  # the full virtual screen
            return mss.tools.to_png(shot.rgb, shot.size)

    def upload(self, attendance_id, png, keys, mouse_, level):
        files = {"screenshot": ("capture.png", io.BytesIO(png), "image/png")}
        data = {"attendance_id": attendance_id, "keystroke_count": keys,
                "mouse_event_count": mouse_, "activity_level": level}
        r = self.http.post(f"{self.server}/api/captures", data=data, files=files, timeout=60)
        if r.status_code == 201:
            print(f"[trov] captured  keys={keys} mouse={mouse_} activity={level}%  -> #{r.json()['capture_id']}")
        else:
            print(f"[trov] upload rejected ({r.status_code}): {r.text}")


def main():
    ap = argparse.ArgumentParser(description="TROV desktop monitoring component")
    ap.add_argument("--server", default=os.environ.get("TROV_SERVER", "http://127.0.0.1:8000"))
    ap.add_argument("--email", required=True)
    ap.add_argument("--password", default=os.environ.get("TROV_PASSWORD"))
    ap.add_argument("--device", default=f"agent-{os.environ.get('COMPUTERNAME', 'pc')}")
    ap.add_argument("--interval", type=int, default=0,
                    help="fixed seconds between captures (default: random 600-1200s)")
    ap.add_argument("--once", action="store_true", help="capture once then exit")
    args = ap.parse_args()

    password = args.password or getpass("TROV password: ")

    agent = TrovAgent(args.server, args.email, password, args.device)
    agent.login()

    meter = ActivityMeter()
    meter.start()
    print("[trov] monitoring started. Captures happen only while a session is open. Ctrl+C to stop.")

    last = time.monotonic()
    first = True
    try:
        while True:
            wait = 0 if (first and args.once) else (args.interval or random.randint(600, 1200))
            first = False
            time.sleep(wait)

            elapsed = max(1, round(time.monotonic() - last))
            last = time.monotonic()

            session = agent.current_session()
            if not session:
                print("[trov] no open session - skipping capture")
                if args.once:
                    break
                continue

            keys, mouse_, level = meter.snapshot_and_reset(elapsed)
            agent.upload(session["attendance_id"], agent.capture_png(), keys, mouse_, level)

            if args.once:
                break
    except KeyboardInterrupt:
        print("\n[trov] stopped.")
        sys.exit(0)


if __name__ == "__main__":
    main()
