<?php
/**
 * API Configuration for different environments
 * 
 * This file handles configuration for development (localhost) and production (inv.kenzastore.my.id)
 */

// Load environment variables
require_once __DIR__ . '/../../includes/env_loader.php';
EnvLoader::load();

class Config {
    private static $config = null;
    
    public static function getConfig() {
        if (self::$config === null) {
            self::initConfig();
        }
        return self::$config;
    }
    
    private static function initConfig() {
        // Detect environment
        $isProduction = false;
        
        // Check if running on production domain
        if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'inv.kenzastore.my.id') !== false) {
            $isProduction = true;
        }
        
        // Alternative: check for production environment variable
        if (getenv('ENVIRONMENT') === 'production') {
            $isProduction = true;
        }
        
        if ($isProduction) {
            // Production Configuration
            self::$config = [
                'environment' => 'production',
                'api_base_url' => EnvLoader::get('API_BASE_URL', 'https://inv.kenzastore.my.id/api/index.php'),
                'invoice_base_url' => EnvLoader::get('INVOICE_BASE_URL', 'https://inv.kenzastore.my.id/invoices/'),
                'allow_cors' => false, // CORS should be disabled in production for security
                'debug_mode' => EnvLoader::get('DEBUG_MODE', 'false') === 'true',
                'log_errors' => EnvLoader::get('LOG_ERRORS', 'true') === 'true'
            ];
        } else {
            // Development Configuration
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
            
            // Determine document root dynamically or use fixed path for this specific setup
            // For http://localhost:8080/kenzastore.my.id/
            $documentRoot = '/inv.kenzastore.my.id'; 
            
            // Use environment variables if available, otherwise fallback to computed URLs
            $apiBaseUrl = EnvLoader::get('API_BASE_URL');
            $invoiceBaseUrl = EnvLoader::get('INVOICE_BASE_URL');
            
            if (!$apiBaseUrl) {
                $apiBaseUrl = $protocol . '://' . $host . $documentRoot . '/api/index.php';
            }
            if (!$invoiceBaseUrl) {
                $invoiceBaseUrl = $protocol . '://' . $host . $documentRoot . '/invoices/';
            }
            
            self::$config = [
                'environment' => 'development',
                'api_base_url' => $apiBaseUrl,
                'invoice_base_url' => $invoiceBaseUrl,
                'allow_cors' => true,
                'debug_mode' => EnvLoader::get('DEBUG_MODE', 'true') === 'true',
                'log_errors' => EnvLoader::get('LOG_ERRORS', 'true') === 'true'
            ];
        }
    }
    
    public static function get($key, $default = null) {
        $config = self::getConfig();
        return $config[$key] ?? $default;
    }
    
    public static function isProduction() {
        return self::get('environment') === 'production';
    }
    
    public static function getApiBaseUrl() {
        return self::get('api_base_url');
    }
    
    public static function getInvoiceBaseUrl() {
        return self::get('invoice_base_url');
    }
    
    public static function allowCORS() {
        return self::get('allow_cors', false);
    }
    
    public static function isDebugMode() {
        return self::get('debug_mode', false);
    }
}
?>