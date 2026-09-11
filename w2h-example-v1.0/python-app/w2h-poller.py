

import json
import os
import time
from typing import Optional

import requests

try:
    import serial  # pyserial - optional, only needed for a real Arduino
except ImportError:
    serial = None
	

#////////////////////////////////////////////////////////
#//////// ENTER YOUR INFO HERE //////////////////////////

YOUR_WEBSITE_URL = "https://my_website.com" 
ARDUINO_PORT = "/dev/cu.usbserial-110" # (e.g. /dev/cu.usbserial-110 on mac and COM3 on Windows)

#////////////////////////////////////////////////////////



# ─── Queue / Networking Config ─────────────────────────────────────────────

# Point this at your own deployment of api.php, e.g.
# "https://yoursite.com/api.php"

API_BASE = YOUR_WEBSITE_URL + "/api.php"

# Must match one of the tokens listed in allowed_tokens.json on the server.
ACCESS_TOKEN = "12345"

# check_status() long-polls: the server holds the connection open until
# there's something to do, or up to ~20s (STATUS_WAIT_MAX_SECONDS on the
# server) with nothing happening. POLL_RETRY_DELAY_SECONDS is just a small
# safety-net sleep so a string of empty/instant responses can't spin the
# loop hot.
POLL_RETRY_DELAY_SECONDS = 0.5

# Comfortably longer than the server's own long-poll wait window
# (STATUS_WAIT_MAX_SECONDS in api.php) plus a margin for normal network
# latency, so a slow-but-still-waiting response isn't mistaken for a hung
# connection.
STATUS_LONGPOLL_TIMEOUT_SECONDS = 35

HEADERS = {"X-Access-Token": ACCESS_TOKEN}


# ─── Local Storage (demo stand-in for the original "workspace") ───────────

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
DOWNLOADS_DIR = os.path.join(SCRIPT_DIR, "downloads")
os.makedirs(DOWNLOADS_DIR, exist_ok=True)


# ─── Arduino Config ─────────────────────────────────────────────────────────

PORT = ARDUINO_PORT
BAUD_RATE = 9600

# If pyserial isn't installed, or you just don't have a board wired up,
# the LED control functions below print what they *would* have sent
# instead of touching a real port. Flip this to False once you have a
# real Arduino to talk to.
SIMULATE_LED = serial is None


# ─── Chat (dummy reply) ─────────────────────────────────────────────────────

def generate_reply(user_text: str, image_filename: Optional[str]) -> str:
    """Replace this with a real LLM call, a rules engine, or anything
    else - this is the only function you need to touch to go from
    "echo demo" to an actual assistant."""
    reply = f"You said: {user_text}" if user_text else "You said: (empty message)"
    if image_filename:
        reply += f"\n\n(You also sent an image, saved as '{image_filename}' - this demo doesn't look at it.)"
    return reply


# ─── LED Control Functions ───────────────────────────────────────────────────
#
# Two things can drive the LED: the control panel's on/off button (a
# pending_led_command picked up in main()'s poll loop, handled below)
# and, in a fuller version of this app, a chat command. Both would
# funnel through _led_control to actually talk to the Arduino, and
# both would report the outcome via report_led_state, so
# device_state.json (and therefore the control panel) always reflects
# whichever path most recently drove the hardware.

def _led_control(command_str: str) -> dict:
    """Shared helper: open serial, send command, read response, close.
    Falls back to a printed simulation if SIMULATE_LED is set."""
    if SIMULATE_LED:
        print(f"[simulated] would send '{command_str}' to Arduino on {PORT}")
        return {"ok": True, "response": f"SIMULATED_{command_str}"}

    command = f"{command_str}\n"
    try:
        ser = serial.Serial(PORT, BAUD_RATE, timeout=2)
        time.sleep(2)  # wait for Arduino to reset
        ser.write(command.encode("utf-8"))
        print(f"Sent command: '{command.strip()}' to {PORT}")
        response = ser.readline().decode("utf-8").strip()
        ser.close()
        if response:
            return {"ok": True, "response": response}
        return {"ok": False, "error": "No response received from Arduino."}
    except serial.SerialException as e:
        return {"ok": False, "error": f"Serial error: {e}"}


def report_led_state(value: str, result: dict) -> None:
    """Reports the outcome of a LED change back to the server via
    report_state, so device_state.json reflects reality rather than
    the mere intent to change it. Only reports "on"/"off" when the
    hardware actually confirmed it (result["ok"] is True) - on
    failure, reports "unknown" rather than guessing."""
    led_value = value if result.get("ok") else "unknown"
    try:
        requests.post(
            f"{API_BASE}?action=report_state",
            data={"state": json.dumps({"led": led_value})},
            headers=HEADERS,
            timeout=10,
        )
    except requests.RequestException as e:
        print(f"Warning: failed to report LED state: {e}")


def handle_pending_led_command(command: dict) -> None:
    """Handles a pending_led_command surfaced by check_status() - i.e.
    a desired change queued by the Remote Control Panel's button.
    Runs on every poll cycle regardless of chat status."""
    value = command.get("led")
    if value not in ("on", "off"):
        print(f"Warning: ignoring malformed pending_led_command: {command!r}")
        return

    print(f"Control panel requested LED {value}...")
    result = _led_control("LED_ON" if value == "on" else "LED_OFF")
    print(f"Status: {result}\n")
    report_led_state(value, result)


# ─── Queue Polling / HTTP Plumbing ───────────────────────────────────────────

def check_status() -> dict:
    """Returns the full status payload - handle_status() on the server
    also folds in pending_uploads and pending_led_command, so main()
    can handle the chat queue and the control panel's LED requests
    from this one poll instead of separate HTTP round trips.

    Long-polls: passes wait=pending so the server itself holds the
    connection open (up to STATUS_WAIT_MAX_SECONDS server-side) until
    there's actually something to do."""
    resp = requests.get(
        f"{API_BASE}?action=status",
        params={"wait": "pending"},
        headers=HEADERS,
        timeout=STATUS_LONGPOLL_TIMEOUT_SECONDS,
    )
    resp.raise_for_status()
    return resp.json()


def get_incoming_message() -> dict:
    resp = requests.get(f"{API_BASE}?action=get_incoming", headers=HEADERS, timeout=10)
    resp.raise_for_status()
    return resp.json()


def download_image(filename: str) -> str:
    """Downloads a chat-attached image into DOWNLOADS_DIR. Returns the
    local path."""
    local_path = os.path.join(DOWNLOADS_DIR, filename)
    resp = requests.get(
        f"{API_BASE}?action=get_image",
        params={"filename": filename},
        headers=HEADERS,
        timeout=30,
    )
    resp.raise_for_status()
    with open(local_path, "wb") as f:
        f.write(resp.content)
    return local_path


def delete_remote_image(filename: str) -> None:
    """Tells the server it can delete the uploaded image now that it's
    been downloaded, so uploads don't accumulate server-side."""
    resp = requests.post(
        f"{API_BASE}?action=delete_image",
        data={"filename": filename},
        headers=HEADERS,
        timeout=10,
    )
    resp.raise_for_status()


def post_reply(reply_text: str, image_filename: Optional[str] = None) -> None:
    data = {"text": reply_text}
    if image_filename:
        data["image"] = image_filename
    resp = requests.post(
        f"{API_BASE}?action=write_reply",
        data=data,
        headers=HEADERS,
        timeout=10,
    )
    resp.raise_for_status()


# ─── Generic File Sync (the UI's separate "Upload" button) ─────────────────

def _dedupe_downloads_filename(filename: str) -> str:
    """If `filename` already exists in DOWNLOADS_DIR, appends a numeric
    suffix so a synced upload never clobbers something already there."""
    if not os.path.exists(os.path.join(DOWNLOADS_DIR, filename)):
        return filename
    base, ext = os.path.splitext(filename)
    counter = 1
    candidate = f"{base}-{counter}{ext}"
    while os.path.exists(os.path.join(DOWNLOADS_DIR, candidate)):
        counter += 1
        candidate = f"{base}-{counter}{ext}"
    return candidate


def download_uploaded_file(filename: str) -> str:
    """Downloads a file uploaded via the UI's "Upload" button straight
    into DOWNLOADS_DIR. Returns the local path."""
    local_filename = _dedupe_downloads_filename(filename)
    local_path = os.path.join(DOWNLOADS_DIR, local_filename)
    resp = requests.get(
        f"{API_BASE}?action=get_uploaded_file",
        params={"filename": filename},
        headers=HEADERS,
        timeout=30,
    )
    resp.raise_for_status()
    with open(local_path, "wb") as f:
        f.write(resp.content)
    return local_path


def delete_remote_uploaded_file(filename: str) -> None:
    """Tells the server it can delete the uploaded file now that it's
    been downloaded, so uploads don't accumulate server-side."""
    resp = requests.post(
        f"{API_BASE}?action=delete_uploaded_file",
        data={"filename": filename},
        headers=HEADERS,
        timeout=10,
    )
    resp.raise_for_status()


def sync_pending_uploads(filenames: list) -> None:
    """Pulls down each file the server listed as waiting, saves it into
    DOWNLOADS_DIR, then tells the server to delete its copy. Runs on
    every poll cycle regardless of chat status. A failure on one file
    is logged and skipped rather than blocking the rest of the loop;
    the file stays listed server-side and gets retried next cycle."""
    for filename in filenames:
        try:
            local_path = download_uploaded_file(filename)
            print(f"Synced uploaded file: {local_path}")
            delete_remote_uploaded_file(filename)
        except requests.RequestException as e:
            print(f"Warning: failed to sync uploaded file {filename!r}: {e}")


# ─── Main Loop ────────────────────────────────────────────────────────────

def main() -> None:
    print(f"Long-polling {API_BASE} (server holds each request until there's "
          f"something to do, or ~{STATUS_LONGPOLL_TIMEOUT_SECONDS}s client-side "
          f"timeout). Ctrl+C to stop.")
    if SIMULATE_LED:
        print("pyserial not installed / no Arduino configured - LED commands will be simulated.\n")

    while True:
        try:
            status_data = check_status()

            # Silent side channel: sync any files dropped via the UI's
            # Upload button. Independent of chat status - runs every
            # cycle, pending or not.
            pending_uploads = status_data.get("pending_uploads") or []
            if pending_uploads:
                sync_pending_uploads(pending_uploads)

            # Also independent of chat status - the control panel's LED
            # button bypasses chat entirely.
            pending_led_command = status_data.get("pending_led_command")
            if pending_led_command:
                handle_pending_led_command(pending_led_command)

            status = status_data.get("status", "idle")

            if status == "pending":
                message = get_incoming_message()
                user_text = message.get("text", "")
                incoming_image_filename = message.get("image")

                print(f"Received: {user_text!r}" + (f" + image {incoming_image_filename}" if incoming_image_filename else ""))

                if incoming_image_filename:
                    local_image_path = download_image(incoming_image_filename)
                    print(f"Saved image: {local_image_path}")

                try:
                    reply_text = generate_reply(user_text, incoming_image_filename)
                except Exception as e:
                    # A bug in generate_reply() shouldn't take the whole
                    # poller down with it - report it and keep polling.
                    print(f"Warning: generate_reply failed: {type(e).__name__}: {e}")
                    reply_text = "Sorry, something went wrong on my end while working on that. Please try again."

                post_reply(reply_text)

                if incoming_image_filename:
                    delete_remote_image(incoming_image_filename)

                print(f"Replied: {reply_text}")

        except requests.RequestException as e:
            print(f"Network error, will retry: {e}")
            # A real network failure (as opposed to the server simply
            # finishing its long-poll wait with nothing to report) -
            # back off briefly so a persistent outage doesn't spin the
            # loop hot.
            time.sleep(POLL_RETRY_DELAY_SECONDS)
            continue

        # check_status() has already waited server-side (up to ~20s) for
        # something to happen. If we get here with nothing to do, loop
        # straight back into the next long-poll rather than sleeping on
        # top of it. The tiny delay is only a safety net against a
        # pathological instant-response loop.
        time.sleep(POLL_RETRY_DELAY_SECONDS)


if __name__ == "__main__":
    main()
