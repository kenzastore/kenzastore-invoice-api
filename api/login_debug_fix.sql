-- Debug SQL queries to test user existence and verification status
-- Run these in your database to check user status

-- Check if the test user exists
SELECT id, email, verified, LEFT(password_hash, 20) as password_prefix 
FROM users 
WHERE email = 'apitest@example.com';

-- Check existing verified users
SELECT id, email, verified 
FROM users 
WHERE verified = 1;

-- Check test user specifically
SELECT id, email, verified 
FROM users 
WHERE email = 'test@example.com';

-- Create a verified test user (if needed)
-- This creates apitest@example.com with password 'password123' and verified=1
INSERT INTO users (email, password_hash, verified) 
VALUES (
    'apitest@example.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    1
) ON DUPLICATE KEY UPDATE verified = 1;

-- Alternative: Verify existing test user if you want to use test@example.com
-- UPDATE users SET verified = 1 WHERE email = 'test@example.com';