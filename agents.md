# AGENTS.md — Kenzastore Invoice App

> **Status:** In development (pre-alpha)  
> **Last updated:** 2025-09-21 (Asia/Jakarta)  
> **One-liner:** Lightweight PHP invoice app that supports user registration/login, creating & updating invoices, and generating PDF invoices via Dompdf.

---

## Project overview

This repository is a no-framework PHP app backed by MySQL. It uses Composer only for PDF generation via **dompdf** and its dependencies.

**Top-level files & folders (abridged):**
- `index.php` — entry/home
- `login.php`, `register.php`, `verify_email.php`, `verify_user.php`, `logout.php`
- `dashboard.php`, `dashboard2.php`
- `new_invoice.php`, `update_invoice.php`, `process_update_invoice.php`
- `generate_invoice.php`, `view_invoice.php`, `check_invoice_status.php`
- `view_logs.php`
- `db_connect.php` — database connection (PDO)
- `invoices/` — generated PDF files (e.g., `INV-*.pdf`)
- `vendor/` — Composer packages (dompdf/dompdf, php-font-lib, php-svg-lib, masterminds/html5, sabberworm/php-css-parser)
- `.well-known/` — ACME challenge (Let’s Encrypt)
- `.vscode/` — workspace hints
- `composer.json`, `composer.lock`

**Major flows**
1. **Auth**: register → (optional) email verify → login → dashboard.
2. **Invoice lifecycle**: create (`new_invoice.php`) → generate PDF (`generate_invoice.php`) → view/share (`view_invoice.php`) → update (`update_invoice.php` / `process_update_invoice.php`).
3. **Operational**: health/checks via `check_invoice_status.php`; logs via `view_logs.php`.

> **Agents:** prefer incremental edits to the existing pages over large refactors. Avoid moving files across folders unless asked.

---

## Build and test commands

### Requirements
- PHP **8.2+** with PDO/MySQLi, `mbstring`, `intl`, `gd` (for dompdf image rendering), `zip`
- Composer **2.x**
- MySQL **8.x** (or MariaDB 10.5+)
- Optional: Apache/Nginx or PHP built-in server for local dev

### Install dependencies
```bash
composer install
