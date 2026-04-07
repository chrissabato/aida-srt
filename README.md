# Tennis Camera SRT Control Panel

A PHP web dashboard for managing SRT (Secure Reliable Transport) video streams from tennis court cameras to a streaming provider (FloSports).

## Features

- Enable/disable SRT streaming per camera
- Bulk enable/disable all cameras at once
- Real-time status polling every 5 seconds
- Admin panel to add, edit, and remove camera configurations

## Requirements

- PHP 7.0+ with cURL extension
- Apache with `mod_rewrite` and Shibboleth authentication module
- Write permission on `cameras.json` for the web server user

## Setup

1. Clone the repo into your web root:
   ```bash
   git clone https://github.com/chrissabato/aida-srt.git
   ```

2. Create your camera configuration from the example:
   ```bash
   cp cameras.example.json cameras.json
   ```

3. Edit `cameras.json` with your camera IPs, keys, and SRT endpoints.

4. Ensure the web server can write to `cameras.json`:
   ```bash
   sudo chown www-data:www-data cameras.json
   ```

5. Add a `.htaccess` file to configure authentication for your environment.

## Camera Configuration

Each entry in `cameras.json` requires:

| Field | Description |
|-------|-------------|
| `name` | Display name (e.g. "Court 1") |
| `cam_ip` | Camera IP address |
| `cam_key` | Camera API key |
| `caller_srt` | SRT destination hostname or IP |
| `port` | SRT destination port |
| `latency` | SRT latency in milliseconds (default: 500) |

## Architecture

```
index.php       — dashboard UI; polls api.php every 5s for stream status
api.php         — proxies enable/disable/status requests to camera CGI endpoints
admin.php       — CRUD interface for cameras.json
cameras.json    — camera configuration (gitignored; copy from cameras.example.json)
```

Cameras communicate via their CGI endpoint: `http://{cam_ip}/cgi-bin/web.fcgi`
