# Implementation Plan - Recurring Invoices

This plan outlines the steps to implement the recurring invoices feature, following the project's TDD workflow.

## Phase 1: Database & Model Setup [checkpoint: e54ab5b]
- [x] Task: Create migration script for `recurring_invoices` table
    - [x] Define table structure (id, invoice_template_id, frequency, start_date, next_run_date, status, last_run_at)
    - [x] Implement the migration in `db/`
- [x] Task: Create RecurringInvoice model/helper logic 6293805
    - [x] Write tests for basic CRUD operations on recurring schedules
    - [x] Implement CRUD logic in a new controller or helper
- [x] Task: Conductor - User Manual Verification 'Phase 1: Database & Model Setup' (Protocol in workflow.md)

## Phase 2: API Endpoints (CRUD) [checkpoint: f1a19c4]
- [x] Task: Implement `POST /recurring-invoices` (Create) 6293805
    - [x] Write tests for creating a recurring schedule
    - [x] Implement endpoint in `api/index.php` and a new controller
- [x] Task: Implement `GET /recurring-invoices` (List) 6293805
    - [x] Write tests for listing active schedules
    - [x] Implement endpoint
- [x] Task: Implement `PUT /recurring-invoices/{id}` (Update/Pause) 6293805
    - [x] Write tests for updating status and frequency
    - [x] Implement endpoint
- [x] Task: Conductor - User Manual Verification 'Phase 2: API Endpoints (CRUD)' (Protocol in workflow.md)

## Phase 3: Automated Generation Logic [checkpoint: 29c013e]
- [x] Task: Create the generation script 6293805 e54c6f8
    - [x] Write tests for the logic that selects due schedules and creates invoices
    - [x] Implement the script (e.g., `api/utils/process_recurring.php`)
- [x] Task: Integration with Logs e54c6f8
    - [x] Write tests to ensure successful generations are logged
    - [x] Implement logging logic
- [x] Task: Conductor - User Manual Verification 'Phase 3: Automated Generation Logic' (Protocol in workflow.md)

## Phase 4: Finalization & Documentation
- [x] Task: Documentation update f99203e
    - [x] Update `README.md` or API docs with the new endpoints
- [~] Task: Conductor - User Manual Verification 'Phase 4: Finalization & Documentation' (Protocol in workflow.md)
