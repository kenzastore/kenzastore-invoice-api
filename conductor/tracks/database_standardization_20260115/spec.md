# Specification - Database Standardization

## Overview
Standardize the project's base database schema by replacing the outdated `blog_cms.sql` with `invoicing_system.sql`. This ensures that new installations have the correct table structure and initial data for the invoice management system.

## Functional Requirements
- **Primary Schema:** Use `db/invoicing_system.sql` as the source of truth for the database structure.
- **Setup Automation:** Update `run_setup.php` to import `invoicing_system.sql` instead of `blog_cms.sql`.

## Acceptance Criteria
- Running `php run_setup.php` successfully creates the `invoicing_system` database (if needed) and imports all tables from `db/invoicing_system.sql`.
