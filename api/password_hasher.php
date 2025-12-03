<?php
/**
 * Password Hash Testing Utility
 * Use this to generate the correct hash for test passwords
 */

echo "Password Hash Generator for API Testing\n";
echo "=====================================\n\n";

// Common test passwords and their hashes
$testPasswords = [
    'password123',
    'password',
    'test123',
    'admin123'
];

echo "Common Test Passwords and their hashes:\n\n";

foreach ($testPasswords as $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "Password: '$password'\n";
    echo "Hash: $hash\n";
    echo "Verify test: " . (password_verify($password, $hash) ? '✅ PASS' : '❌ FAIL') . "\n\n";
}

// Interactive mode
echo "Interactive Mode - Enter a password to hash:\n";
echo "Enter password (or 'quit' to exit): ";

while (true) {
    $input = trim(fgets(STDIN));
    
    if (strtolower($input) === 'quit' || strtolower($input) === 'exit') {
        break;
    }
    
    if (!empty($input)) {
        $hash = password_hash($input, PASSWORD_DEFAULT);
        echo "Password: '$input'\n";
        echo "Hash: $hash\n";
        echo "Verify test: " . (password_verify($input, $hash) ? '✅ PASS' : '❌ FAIL') . "\n\n";
        echo "Enter next password (or 'quit' to exit): ";
    }
}

echo "\nDone!\n";
?>