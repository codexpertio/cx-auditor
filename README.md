# CX Auditor

A WordPress plugin that generates a preliminary site audit report for any WordPress site using just its URL — no login or site credentials required. Designed for agencies to quickly assess a client's site health before requesting access for the full WP Auditor report.

> **Target audience:** agencies and site maintainers who want a quick, no-login health check to share with clients before deeper audits.

---

## Features

- **URL-only auditing** — enter any WordPress site URL, get a preliminary report instantly.
- **4 audit modules** — WordPress Detection, SSL Certificate, Basic Security, Basic SEO.
- **Scored reports** — each module returns a 0–100 score with pass/warning/fail statuses; overall score is a simple average.
- **PDF export** — Dompdf-powered professional report for client sharing.
- **No build step** — admin UI is jQuery + CSS. Drop the plugin in, run `composer install`, activate.
- **White-label** — customize brand name, accent color, logo URL for PDF reports.

---

## Requirements

| | |
|---|---|
| **WordPress** | 6.0+ |
| **PHP**       | 8.0+ |
| **Composer**  | For installing Dompdf |
| **License**   | GPL v2 or later |

---

## Installation

### From source

```bash
cd wp-content/plugins/
git clone https://github.com/codexpert/cx-auditor.git
cd cx-auditor
composer install --no-dev
```

Then **Plugins → Activate** in `wp-admin`.

---

## Usage

1. Go to **CX Auditor** in wp-admin.
2. Enter a client site URL (e.g., `https://example.com`).
3. Click **Run Quick Audit**.
4. View the preliminary report with overall score.
5. Click **Download PDF** to generate a shareable report.

### What's checked

| Module | Checks |
|--------|--------|
| **WordPress Detection** | Site reachable, Is WordPress, WP version |
| **SSL Certificate** | HTTPS enabled, SSL validity |
| **Basic Security** | XML-RPC, default login URL, security headers (X-Frame-Options, X-Content-Type-Options, HSTS) |
| **Basic SEO** | Title tag, meta description, robots.txt, XML sitemap, search visibility |

---

## Settings

- **White-label:** brand name, accent color, logo URL

---

## Architecture

```
cx-auditor/
├── cx-auditor.php              # Plugin header
├── src/
│   ├── Plugin.php              # Bootstrap
│   ├── Auditor.php             # Runs checks + AJAX handler
│   ├── Checkers/
│   │   ├── Checker_Interface.php
│   │   ├── Abstract_Checker.php
│   │   ├── WordPress_Detection.php
│   │   ├── SSL_Check.php
│   │   ├── Basic_Security.php
│   │   └── Basic_SEO.php
│   ├── Admin/
│   │   ├── Menu.php            # Menu + PDF download handler
│   │   ├── Assets.php          # Enqueue CSS + jQuery
│   │   └── views/dashboard.php
│   └── Report/
│       └── PDF.php             # Dompdf renderer, white-label aware
├── assets/
│   ├── css/admin.css
│   └── js/admin.js             # jQuery: run audit, render results
└── composer.json               # codexpert/cx-auditor, PSR-4
```

---

## How it works

1. **HTTP requests** — the plugin makes outbound HTTP requests to the target site (no authentication needed).
2. **Public data collection** — scans homepage HTML, checks for common WordPress indicators, tests SSL, probes for XML-RPC, reads HTTP headers.
3. **Scoring** — each check is scored as pass/warning/fail; module score is calculated from pass/fail counts.
4. **PDF generation** — Dompdf renders the results into a professional PDF.

---

## Use case

| Step | Tool | What you get |
|------|------|--------------|
| 1 | **CX Auditor** (this plugin) | Quick URL-only check, shareable PDF, no client credentials needed |
| 2 | **WP Auditor** (full plugin) | Deep audit with admin access, all 10 modules, AI recommendations, scheduled monitoring |

---

## Free external services

| Service | Purpose | Auth |
|---------|---------|------|
| Target site | HTML scraping, header inspection | None (public pages only) |

---

## Security notes

- PDF downloads require `manage_options` capability and a valid nonce.
- All data is passed via URL parameters (base64 encoded); no sensitive data is stored.
- No external API keys required.

---

## Development

```bash
composer install
# Lint
find src -name '*.php' -print0 | xargs -0 -n1 php -l
```

---

## License

GPL v2 or later.

---

## Credits

- [Dompdf](https://github.com/dompdf/dompdf) — PDF generation (bundled via Composer)