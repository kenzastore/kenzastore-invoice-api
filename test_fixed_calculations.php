<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/db_connect.php';

$page_title = "Test Fixed Calculations";
include 'includes/header.php';
?>

<div class="container mt-4">
    <h1>Test Fixed Invoice Calculations</h1>
    <p>This page tests the calculation fixes applied to new_invoice.php and update_invoice.php</p>
    
    <div class="alert alert-info">
        <h4>Test Instructions:</h4>
        <ol>
            <li>Enter a quantity (e.g., 2)</li>
            <li>Enter a rate (e.g., 10.50)</li>
            <li>The amount should automatically calculate (2 × 10.50 = 21.00)</li>
            <li>The subtotal should update automatically</li>
            <li>Try changing discount, tax, or shipping values</li>
            <li>The total should recalculate in real-time</li>
            <li>Check the browser console for debug logs</li>
        </ol>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3>Simple Calculation Test</h3>
        </div>
        <div class="card-body">
            <table class="table" id="invoiceItems">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Rate</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="text" name="items[0][description]" value="Test Product"></td>
                        <td><input type="number" class="quantity" name="items[0][quantity]" value="1" step="any" min="0"></td>
                        <td><input type="number" class="rate" name="items[0][rate]" value="10.00" step="any" min="0"></td>
                        <td><input type="number" class="amount" name="items[0][amount]" value="0.00" readonly></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Discount:</label>
                        <input type="number" id="discount" class="form-control" value="0" step="any" min="0">
                    </div>
                    <div class="form-group">
                        <label>Tax (%):</label>
                        <input type="number" id="tax" class="form-control" value="10" step="any" min="0">
                    </div>
                    <div class="form-group">
                        <label>Shipping:</label>
                        <input type="number" id="shipping" class="form-control" value="5" step="any" min="0">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Subtotal:</label>
                        <input type="text" id="subtotal" class="form-control" value="0.00" readonly>
                    </div>
                    <div class="form-group">
                        <label>Total:</label>
                        <input type="text" id="total" class="form-control" value="0.00" readonly>
                    </div>
                    <div class="form-group">
                        <label>Amount Paid:</label>
                        <input type="number" id="amount_paid" class="form-control" value="0" step="any" min="0">
                    </div>
                    <div class="form-group">
                        <label>Balance Due:</label>
                        <input type="text" id="balance_due" class="form-control" value="0.00" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Test the calculation logic directly
function testCalculations() {
    console.log('=== Testing Calculations ===');
    
    function parseNumber(value) {
        const num = parseFloat(value);
        return isNaN(num) ? 0 : num;
    }

    function calculateTotals() {
        console.log('calculateTotals called');
        let subtotal = 0;

        document.querySelectorAll('.amount').forEach(function(amountField) {
            subtotal += parseNumber(amountField.value);
        });

        document.getElementById('subtotal').value = subtotal.toFixed(2);

        const discount = parseNumber(document.getElementById('discount').value);
        const taxRate = parseNumber(document.getElementById('tax').value);
        const shipping = parseNumber(document.getElementById('shipping').value);

        const taxAmount = subtotal * (taxRate / 100);
        const total = Math.max(subtotal - discount + taxAmount + shipping, 0);

        document.getElementById('total').value = total.toFixed(2);

        const amountPaid = parseNumber(document.getElementById('amount_paid').value);
        const balanceDue = Math.max(total - amountPaid, 0);

        document.getElementById('balance_due').value = balanceDue.toFixed(2);
        
        console.log('Test Results:', {
            subtotal: subtotal,
            discount: discount,
            taxRate: taxRate,
            shipping: shipping,
            taxAmount: taxAmount,
            total: total,
            amountPaid: amountPaid,
            balanceDue: balanceDue
        });
    }

    // Test quantity and rate changes
    document.querySelectorAll('.quantity, .rate').forEach(function(field) {
        field.addEventListener('input', function() {
            const row = this.closest('tr');
            const quantity = parseNumber(row.querySelector('.quantity').value);
            const rate = parseNumber(row.querySelector('.rate').value);
            const amount = quantity * rate;
            
            row.querySelector('.amount').value = amount.toFixed(2);
            console.log('Item amount calculated:', { quantity: quantity, rate: rate, amount: amount });
            calculateTotals();
        });
    });

    // Test summary field changes
    ['discount', 'tax', 'shipping', 'amount_paid'].forEach(function(fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', calculateTotals);
        }
    });

    // Initial calculation
    calculateTotals();
    
    return { calculateTotals: calculateTotals };
}

// Run test when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, starting calculation test');
    testCalculations();
});
</script>

<?php include 'includes/footer.php'; ?>