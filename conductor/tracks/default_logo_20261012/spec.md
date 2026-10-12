# Specification: Default Kenza Store Logo for Invoices

## 1. Overview
Ensure that `conductor/reference/logo kenza store.png` is integrated as the system default logo across invoice creation (`new_invoice.php`), invoice editing (`update_invoice.php`), and corresponding invoice generation / update processing (`generate_invoice.php`, `process_update_invoice.php`). When a user accesses invoice creation or editing without an explicit custom logo or prior removal, the Kenza Store logo is pre-selected and previewed by default. Users retain full capability to replace it via "Choose Logo" or remove it via "Remove".

## 2. Functional Requirements
1. **Asset Placement**:
   - Copy `conductor/reference/logo kenza store.png` to a clean web-accessible location, such as `assets/images/logo-kenza-store.png` (and keep compatibility with `invoices/logos/` if needed).
2. **Default Logo Detection & Prefill in `new_invoice.php`**:
   - If `$_SESSION['brand_logo_path']` is not set and no logo removal has occurred in the current session, prefill the default logo path (`assets/images/logo-kenza-store.png`).
   - The brand preview box initializes showing the default Kenza Store logo image instead of "No logo selected".
   - The "Remove" button is visible and active.
3. **Default Logo Detection & Prefill in `update_invoice.php`**:
   - When loading an invoice, check if an existing custom invoice/session logo exists. If none is found, fallback to the default Kenza Store logo path.
   - The preview box displays the default logo if no custom logo was stored for that invoice.
4. **Interactive Controls & Actions**:
   - **Choose Logo**: Allows users to select their own custom logo image from their device; previews immediately and sets `brand_logo_action = 'uploaded'`.
   - **Remove Logo**: Clears the preview, sets the container state to empty ("No logo selected"), hides the "Remove" button, and sets `brand_logo_action = 'removed'`.
   - **Default State**: When submitting with the default logo, action is treated as `default` or `keep` pointing to the default logo asset.
5. **Invoice Processing & PDF Generation**:
   - `generate_invoice.php` and `process_update_invoice.php` support the default logo path for Dompdf rendering when no custom logo is uploaded and the action is not `removed`.
   - Stored invoice or session paths cleanly reference the default logo without broken image paths.

## 3. Acceptance Criteria
- Loading `new_invoice.php` in a fresh session displays the Kenza Store logo in the preview box by default.
- Clicking "Choose Logo" and uploading a new file updates the preview to the uploaded file.
- Clicking "Remove" removes the logo, shows "No logo selected", and removes the logo from the invoice generation.
- Loading `update_invoice.php` for an invoice without a custom logo shows the default Kenza Store logo.
- Generated PDFs render the Kenza Store logo cleanly when the default is kept.

## 4. Out of Scope
- Dynamic multi-brand logo management interface / admin logo upload dashboard.
- Modifying database schema unless strictly necessary (existing schema accommodates paths / base64).
