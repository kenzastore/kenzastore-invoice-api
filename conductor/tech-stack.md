# Tech Stack

## Backend
- **Language:** PHP 8.1+
- **Architecture:** Custom RESTful API with an MVC-like controller-middleware structure.
- **Authentication:** JWT (JSON Web Tokens) for secure, stateless API access.
- **PDF Generation:** `dompdf/dompdf` for converting HTML/CSS to professional PDF invoices.
- **Automation:** PHP CLI scripts for recurring tasks (e.g., `process_recurring.php`).

## Database
- **Type:** MySQL/MariaDB
- **Access Layer:** PHP Data Objects (PDO) with prepared statements for security and flexibility.
- **Schema:** Includes `recurring_invoices` for automated billing schedules.

## Infrastructure & Environment
- **Environment Management:** Environment-aware configuration (dev/prod) using `.env` files and `EnvLoader`.
- **Dependency Management:** Composer for PHP package management.
