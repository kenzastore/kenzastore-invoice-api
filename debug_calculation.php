<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/db_connect.php';

$page_title = "Debug Calculation";
include 'includes/header.php';
?>

<div class="container mt-4">
    <h1>Invoice Calculation Debug</h1>
    
    <div class="card">
        <div class="card-header">
            <h3>Test Calculation Form</h3>
        </div>
        <div class="card-body">
            <form id="invoiceForm">
                <table class="table" id="invoiceItems">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Rate</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" name="items[0][description]" value="Test Item 1"></td>
                            <td><input type="number" class="quantity" name="items[0][quantity]" value="2"></td>
                            <td><input type="number" class="rate" name="items[0][rate]" value="10"></td>
                            <td><input type="number" class="amount" name="items[0][amount]" value="0.00" readonly></td>
                            <td><button type="button" class="btn btn-sm btn-danger remove-item">Remove</button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" id="addItem" class="btn btn-success">Add Line Item</button>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <h4>Summary</h4>
                        <div class="form-group">
                            <label>Subtotal:</label>
                            <input type="text" id="subtotal" class="form-control" value="0.00" readonly>
                        </div>
                        <div class="form-group">
                            <label>Discount:</label>
                            <input type="number" id="discount" class="form-control" value="0">
                        </div>
                        <div class="form-group">
                            <label>Tax (%):</label>
                            <input type="number" id="tax" class="form-control" value="0">
                        </div>
                        <div class="form-group">
                            <label>Shipping:</label>
                            <input type="number" id="shipping" class="form-control" value="0">
                        </div>
                        <div class="form-group">
                            <label>Total:</label>
                            <input type="text" id="total" class="form-control" value="0.00" readonly>
                        </div>
                        <div class="form-group">
                            <label>Amount Paid:</label>
                            <input type="number" id="amount_paid" class="form-control" value="0">
                        </div>
                        <div class="form-group">
                            <label>Balance Due:</label>
                            <input type="text" id="balance_due" class="form-control" value="0.00" readonly>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Debug version with extensive logging
    jQuery(document).ready(function($) {
        console.log('=== DEBUG: jQuery ready ===');
        console.log('jQuery version:', $.fn.jquery);
        console.log('DOM loaded');
        
        // Test if jQuery selectors work
        console.log('Testing selectors:');
        console.log('$("#subtotal"):', $("#subtotal").length > 0 ? 'FOUND' : 'NOT FOUND');
        console.log('$("#total"):', $("#total").length > 0 ? 'FOUND' : 'NOT FOUND');
        console.log('$("#invoiceItems"):', $("#invoiceItems").length > 0 ? 'FOUND' : 'NOT FOUND');
        console.log('$(".quantity"):', $(".quantity").length);
        console.log('$(".amount"):', $(".amount").length);

        function parseNumber(value) {
            const result = parseFloat(value) || 0;
            console.log('parseNumber("' + value + '") =', result);
            return result;
        }

        function calculateTotals() {
            console.log('\n=== calculateTotals() called ===');
            
            let subtotal = 0;
            $('.amount').each(function(index) {
                const val = parseNumber($(this).val());
                console.log('Amount field', index + ':', val);
                subtotal += val;
            });
            
            console.log('Subtotal calculated:', subtotal);
            $('#subtotal').val(subtotal.toFixed(2));

            const discount = parseNumber($('#discount').val());
            const taxRate = parseNumber($('#tax').val());
            const shipping = parseNumber($('#shipping').val());

            console.log('Discount:', discount);
            console.log('Tax Rate:', taxRate + '%');
            console.log('Shipping:', shipping);

            const taxAmount = subtotal * (taxRate / 100);
            const total = Math.max(subtotal - discount + taxAmount + shipping, 0);

            console.log('Tax amount:', taxAmount);
            console.log('Final total:', total);

            $('#total').val(total.toFixed(2));

            const amountPaid = parseNumber($('#amount_paid').val());
            const balanceDue = Math.max(total - amountPaid, 0);

            console.log('Amount paid:', amountPaid);
            console.log('Balance due:', balanceDue);

            $('#balance_due').val(balanceDue.toFixed(2));
            console.log('=== calculateTotals() complete ===\n');
        }

        // Test calculation immediately
        console.log('Initial calculation:');
        calculateTotals();

        $(document).on('click', '#addItem', function () {
            console.log('\n=== Add item clicked ===');
            const index = $('#invoiceItems tbody tr').length;
            console.log('Adding item at index:', index);

            $('#invoiceItems tbody').append(`
                <tr>
                    <td><input type="text" name="items[${index}][description]" value="Item ${index + 1}"></td>
                    <td><input type="number" class="quantity" name="items[${index}][quantity]" value="1"></td>
                    <td><input type="number" class="rate" name="items[${index}][rate]" value="5"></td>
                    <td><input type="number" class="amount" name="items[${index}][amount]" value="0.00" readonly></td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-item">Remove</button></td>
                </tr>
            `);

            console.log('Item added, recalculating...');
            calculateTotals();
        });

        $(document).on('click', '.remove-item', function () {
            const rows = $('#invoiceItems tbody tr').length;
            console.log('Remove clicked, current rows:', rows);
            
            if (rows > 1) {
                $(this).closest('tr').remove();
                console.log('Row removed, recalculating...');
                calculateTotals();
            } else {
                console.log('Cannot remove - only one row left');
            }
        });

        $(document).on('input', '.quantity, .rate', function () {
            const row = $(this).closest('tr');
            const quantity = parseNumber(row.find('.quantity').val());
            const rate = parseNumber(row.find('.rate').val());
            const amount = quantity * rate;
            
            console.log('Quantity/Rate changed:', 'Q:', quantity, 'R:', rate, 'A:', amount);
            
            row.find('.amount').val(amount.toFixed(2));
            calculateTotals();
        });

        $('#discount, #tax, #shipping, #amount_paid').on('input', function () {
            console.log('Summary field changed:', $(this).attr('id'));
            calculateTotals();
        });
    });
</script>

<?php include 'includes/footer.php'; ?>