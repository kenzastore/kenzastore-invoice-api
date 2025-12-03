<?php
session_start();

// Simple test to check if the download functionality works
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Mock user for testing
    $_SESSION['email'] = 'test@example.com';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Download Button Test</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Download Button Test</h1>
    
    <form id="invoiceForm" method="POST" action="process_update_invoice.php">
        <input type="hidden" name="user_id" value="1">
        <input type="hidden" name="invoice_id" value="1">
        
        <input type="text" name="invoice_number" value="TEST-001" required>
        <input type="date" name="invoice_date" value="2025-01-01" required>
        <textarea name="bill_to" required>Test Customer</textarea>
        <textarea name="ship_to" required>Test Address</textarea>
        <textarea name="items[0][description]" required>Test Item</textarea>
        <input type="number" name="items[0][quantity]" value="1" required>
        <input type="number" name="items[0][rate]" value="100" required>
        <input type="number" name="items[0][amount]" value="100" readonly>
        
        <button type="button" id="generatePdfBtn">Test Download</button>
    </form>

    <script>
        // Test the download functionality
        document.getElementById('generatePdfBtn').addEventListener('click', function () {
            console.log('Download button clicked');
            const form = document.getElementById('invoiceForm');

            if (!form) {
                console.error('Form not found');
                return;
            }

            console.log('Form found, proceeding with download');
            const targetName = 'invoice_pdf';
            const pdfWindow = window.open('', targetName);
            const originalTarget = form.getAttribute('target');
            const originalAction = form.getAttribute('action');

            console.log('Original action:', originalAction);
            console.log('Setting action to generate_invoice.php');

            form.setAttribute('target', targetName);
            form.setAttribute('action', 'generate_invoice.php');

            form.submit();

            if (originalTarget !== null) {
                form.setAttribute('target', originalTarget);
            } else {
                form.removeAttribute('target');
            }

            if (originalAction !== null) {
                form.setAttribute('action', originalAction);
            } else {
                form.removeAttribute('action');
            }

            console.log('Form submitted, starting status check');
            const checkFile = setInterval(() => {
                fetch('check_invoice_status.php')
                    .then(response => response.json())
                    .then(data => {
                        console.log('Status check:', data);
                        if (data.status === 'ready') {
                            clearInterval(checkFile);
                            console.log('Invoice ready, closing window and redirecting');

                            if (pdfWindow && !pdfWindow.closed) {
                                pdfWindow.close();
                            }

                            window.location.href = 'thanks.php';
                        }
                    })
                    .catch(error => {
                        console.error('Status check error:', error);
                    });
            }, 3000);
        });
    </script>
</body>
</html>