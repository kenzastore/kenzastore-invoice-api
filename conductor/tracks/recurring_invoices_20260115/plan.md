# Implementation Plan - Recurring Invoices

This plan outlines the steps to implement the recurring invoices feature, following the project's TDD workflow.

## Phase 1: Database & Model Setup
- [x] Task: Create migration script for `recurring_invoices` table
    - [x] Define table structure (id, invoice_template_id, frequency, start_date, next_run_date, status, last_run_at)
    - [x] Implement the migration in `db/`
- [x] Task: Create RecurringInvoice model/helper logic 6293805
    - [x] Write tests for basic CRUD operations on recurring schedules
    - [x] Implement CRUD logic in a new controller or helper
- [ ] Task: Conductor - User Manual Verification 'Phase 1: Database & Model Setup' (Protocol in workflow.md)

## Phase 2: API Endpoints (CRUD)
- [ ] Task: Implement `POST /recurring-invoices` (Create)
    - [ ] Write tests for creating a recurring schedule
    - [ ] Implement endpoint in `api/index.php` and a new controller
- [ ] Task: Implement `GET /recurring-invoices` (List)
    - [ ] Write tests for listing active schedules
    - [ ] Implement endpoint
- [ ] Task: Implement `PUT /recurring-invoices/{id}` (Update/Pause)
    - [ ] Write tests for updating status and frequency
    - [ ] Implement endpoint
- [ ] Task: Conductor - User Manual Verification 'Phase 2: API Endpoints (CRUD)' (Protocol in workflow.md)

## Phase 3: Automated Generation Logic
- [ ] Task: Create the generation script
    - [ ] Write tests for the logic that selects due schedules and creates invoices
    - [ ] Implement the script (e.g., `api/utils/process_recurring.php`)
- [ ] Task: Integration with Logs
    - [ ] Write tests to ensure successful generations are logged
    - [ ] Implement logging logic
- [ ] Task: Conductor - User Manual Verification 'Phase 3: Automated Generation Logic' (Protocol in workflow.md)

## Phase 4: Finalization & Documentation
- [ ] Task: Documentation update
    - [ ] Update `README.md` or API docs with the new endpoints
- [ ] Task: Conductor - User Manual Verification 'Phase 4: Finalization & Documentation' (Protocol in workflow.md)
