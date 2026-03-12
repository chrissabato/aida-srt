# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PHP web application for managing SRT (Secure Reliable Transport) video streams from tennis court cameras to FloSports. Administrators can enable/disable SRT streaming per camera or in bulk via a web dashboard.

## Stack

- **Backend**: PHP (procedural, no framework)
- **Frontend**: HTML + vanilla JavaScript + Tailwind CSS (CDN)
- **Config**: `cameras.json` (flat file, no database)
- **Auth**: Shibboleth SSO + Apache `.htaccess` (IP-based campus bypass for `158.104.114.0/24`)

No build process — PHP files are served directly by Apache.

## Architecture

```
index.php   → dashboard UI; polls api.php every 5s for status
api.php     → SRT control API; proxies HTTP requests to camera CGI endpoints
admin.php   → CRUD for cameras.json configuration
cameras.json → camera config (name, cam_ip, cam_key, caller_srt, port)
.htaccess   → Shibboleth auth; allows campus IPs without SSO
```

### Camera API Integration

Cameras expose a CGI endpoint: `http://{cam_ip}/cgi-bin/web.fcgi`

- **Enable/Disable SRT**: POST with `?func=set` — sets `SRT.main caller.*` and `SRT.sub caller.*` fields
- **Check status**: POST with `?func=get` — reads `SRT.main caller.enable` from response

The camera acts as SRT **caller** (initiates connection to FloSports servers).

### Data Flow

```
Browser → index.php (renders camera grid from cameras.json)
       → JS polls api.php?check_status=1 every 5s
api.php → HTTP POST to camera CGI → FloSports SRT server
```

## Key Implementation Notes

- Camera IPs and keys are embedded in client-side HTML (inline JS in `index.php`)
- `api.php` uses cURL for all outbound HTTP; no external PHP libraries
- `admin.php` reads/writes `cameras.json` directly — ensure web server has write permissions on that file
- Status values: `enabled` (green), `disabled` (gray), `null`/unknown (yellow)
- Authorized users defined in `.htaccess`: `csabato`, `mdrader`
