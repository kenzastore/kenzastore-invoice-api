# Implementation Plan: Default Kenza Store Logo

## Phase 1: Asset Preparation & Setup
- [x] Task: Prepare default logo asset in `assets/images/`
    - [x] Create directory `assets/images/` if it does not exist
    - [x] Copy `conductor/reference/logo kenza store.png` to `assets/images/logo-kenza-store.png`
    - [x] Verify image permissions and readability
- [x] Task: Conductor - User Manual Verification 'Phase 1: Asset Preparation & Setup' (Protocol in workflow.md)

## Phase 2: Tests for Default Logo Handling (TDD)
- [x] Task: Write automated test suite for logo resolution & fallback
    - [x] Create test script `tests/test_default_logo.php`
    - [x] Test case: Fresh session resolution defaults to `assets/images/logo-kenza-store.png`
    - [x] Test case: Explicit 'removed' action leaves logo empty
    - [x] Test case: Custom upload overrides default logo
    - [x] Confirm tests fail (Red Phase) before updating application logic
- [x] Task: Conductor - User Manual Verification 'Phase 2: Tests for Default Logo Handling (TDD)' (Protocol in workflow.md)

## Phase 3: Application UI & Logic Integration
- [x] Task: Implement default logo prefill in `new_invoice.php`
    - [x] Update PHP logo initialization logic to fallback to `assets/images/logo-kenza-store.png`
    - [x] Ensure frontend state and controls (`Choose Logo`, `Remove`) reflect the active default logo
- [x] Task: Implement default logo prefill in `update_invoice.php`
    - [x] Update fallback logo resolution to use `assets/images/logo-kenza-store.png` when no custom logo exists
    - [x] Ensure frontend state and controls operate identically
- [x] Task: Update invoice backend handlers (`generate_invoice.php` & `process_update_invoice.php`)
    - [x] Ensure PDF generation accommodates `assets/images/logo-kenza-store.png` for Dompdf rendering
    - [x] Handle session retention when default logo is chosen/kept
- [x] Task: Execute tests to verify Green phase (`tests/test_default_logo.php`)
- [ ] Task: Conductor - User Manual Verification 'Phase 3: Application UI & Logic Integration' (Protocol in workflow.md)

## Phase 4: End-to-End Verification & Quality Assurance
- [ ] Task: Verify PDF generation and UI responsiveness
    - [ ] Test creating a new invoice with default logo and check generated PDF
    - [ ] Test removing logo and check generated PDF has no logo
    - [ ] Test updating invoice with custom logo override
- [ ] Task: Conductor - User Manual Verification 'Phase 4: End-to-End Verification & Quality Assurance' (Protocol in workflow.md)
