# Specification - Recurring Invoices

## Overview
The "Recurring Invoices" feature allows users to automate their billing cycle for subscription-based services or long-term contracts. Users can define the frequency (weekly, monthly, annually), start date, and the template invoice to be used for future generations.

## User Stories
- As a freelancer, I want to set up an invoice to be automatically generated every month so I don't have to manually create it each time.
- As a business owner, I want to see a list of all active recurring invoice schedules.
- As a user, I want to be notified (or see a log) when a recurring invoice has been successfully generated.

## Functional Requirements
- **Create Recurring Schedule:** Define `invoice_template_id`, `frequency`, `start_date`, `next_run_date`, and `status` (active/paused).
- **Automated Generation:** A background process (or triggered script) that checks `next_run_date` and generates a new invoice based on the template.
- **CRUD Operations:** Create, Read, Update, and Delete/Pause recurring schedules.
- **Logging:** Record each generation event in the system logs.

## Technical Considerations
- **Database Schema:** New table `recurring_invoices` to store schedule metadata.
- **Logic:** Reuse existing `InvoiceController` logic for creating invoices.
- **Trigger:** Since this is a PHP app, we might need a CRON job to trigger the generation script periodically.

## Success Criteria
- Users can create a recurring invoice schedule through the API.
- The system correctly identifies when an invoice is due for generation.
- A new, valid invoice is created automatically based on the schedule.
