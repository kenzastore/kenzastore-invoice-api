# Kenzastore Invoice Management API - Project Context

## Project Overview
This project is a PHP-based invoice management system designed for **Kenzastore**. It provides a full-featured RESTful API for managing invoices, generating PDFs (via `dompdf`), and handling user authentication using JWT. It supports both development and production environments with dynamic configuration.

**Tech Stack:**
*   **Language:** PHP 8.1+
*   **Database:** MySQL/MariaDB (accessed via `PDO`)
*   **Dependencies:** Managed via Composer (`dompdf/dompdf`)
*   **Authentication:** JWT (JSON Web Tokens)
*   **Frontend:** Mixed (PHP server-rendered pages + HTML/JS API consumers)

## Architecture & Structure

### Core Directories
*   **`api/`**: The heart of the application. Contains the REST API logic.
    *   `config/`: Configuration (Database, JWT secrets, Environment settings).
    *   `controllers/`: Business logic (e.g., `InvoiceController.php`, `AuthController.php`).
    *   `middleware/`: Request handling (e.g., `AuthMiddleware.php` for JWT verification).
    *   `utils/`: Helpers (e.g., `JWT.php`).
*   **`includes/`**: Shared PHP utilities.
    *   `env_loader.php`: Critical for loading `.env` variables.
*   **`db/`**: Database schemas (`blog_cms.sql`).
*   **`invoices/`**: Storage for generated PDF files.
*   **`assets/`**: Static resources (CSS, JS).
*   **`tests/`**: PHP-based test scripts.

### Key Files
*   **`index.php`**: Frontend entry point (Dashboard/UI).
*   **`api/index.php`**: API Router/Entry point.
*   **`db_connect.php`**: Database connection script (uses `EnvLoader` and `PDO`).
*   **`.env`**: Environment configuration (Database credentials, JWT Secret, Environment mode).
*   **`composer.json`**: Dependency definition.

## Setup & Development

### Installation
1.  **Dependencies:** Run `composer install` to install PHP packages.
2.  **Environment:** Copy `.env.example` to `.env` and configure:
    *   `DATABASE_*` credentials.
    *   `JWT_SECRET` (crucial for auth).
    *   `ENVIRONMENT` (`development` or `production`).
3.  **Database:** Import `db/blog_cms.sql` into your MySQL instance.

### Running
*   **Built-in Server:** `php -S localhost:8080`
*   **Base URL:**
    *   Dev: `http://localhost:8080/kenzastore.my.id/`
    *   Prod: `https://inv.kenzastore.my.id/`

### Testing
*   **Interactive:** Open `api/api_tester_dynamic.html` in a browser to test API endpoints interactively.
*   **Scripts:** Check the `tests/` directory for PHP-based test scripts (e.g., `test_api.php`).
*   **Debug:** `api/debug_jwt.php` (May be removed in production, see `walkthrough.md`).

## Development Conventions

*   **Database:** Always use `PDO` with prepared statements to prevent SQL injection.
*   **Configuration:** Never hardcode credentials. Use `EnvLoader::get('KEY')` to access values from `.env`.
*   **Authentication:** All API endpoints (except login) require a `Authorization: Bearer <token>` header.
*   **Structure:** Follow the MVC-like structure in `api/` (Controllers for logic, Middleware for checks).
*   **PDF Generation:** Handled by `InvoiceController` using `dompdf`.

## Recent Updates & Known Context (from `walkthrough.md`)

*   **JWT Auth:** Significant work was done to ensure the `Authorization` header is correctly passed to PHP in various server environments (Apache/Nginx).
*   **Environment Awareness:** The system now auto-detects `development` vs `production` to adjust CORS and Base URLs.
*   **Cleanup:** Debug files like `api/debug_jwt.php` and `api/jwt_debug_tester.html` are slated for removal in production environments.
