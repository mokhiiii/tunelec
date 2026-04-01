<?php
/**
 * TunElec Configuration & Initialization
 * 
 * Include this file at the top of all PHP files:
 *   require_once 'config.php';
 * 
 * This automatically:
 * - Loads environment variables from .env.local
 * - Loads Supabase helper class
 * - Sets up error handling
 * - Handles CORS headers if API request
 */

// Enable error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load environment variables
require_once __DIR__ . '/env-loader.php';

// Load Supabase helper
require_once __DIR__ . '/supabase.php';

// Handle CORS for API requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    http_response_code(200);
    exit;
}

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Check required environment variables
if (!getenv('SUPABASE_URL') || !getenv('SUPABASE_ANON_KEY')) {
    error_log('ERROR: Missing Supabase credentials in .env.local');
    if (php_sapi_name() === 'cli') {
        // CLI mode - just warn
        echo "WARNING: Missing Supabase credentials\n";
    }
    // Don't exit - might be testing without Supabase
}

/**
 * Helper function to get Supabase instance
 * Usage: $users = db('users')->select();
 */
function db($table) {
    try {
        return new Supabase($table);
    } catch (Exception $e) {
        error_log('Database Error: ' . $e->getMessage());
        throw $e;
    }
}

/**
 * Helper function for API responses
 */
function api_response($success, $data = null, $message = '') {
    header('Content-Type: application/json');
    
    $response = [
        'success' => $success,
        'timestamp' => date('c')
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    if ($message) {
        $response['message'] = $message;
    }
    
    return json_encode($response);
}

/**
 * Helper function for error responses
 */
function api_error($code, $message, $details = null) {
    header('Content-Type: application/json');
    http_response_code($code);
    
    $response = [
        'success' => false,
        'error' => $message,
        'code' => $code,
        'timestamp' => date('c')
    ];
    
    if ($details) {
        $response['details'] = $details;
    }
    
    return json_encode($response);
}

/**
 * Debug function (only works in development)
 */
function debug($data) {
    if (getenv('ENVIRONMENT') !== 'production') {
        error_log('DEBUG: ' . json_encode($data));
        echo '<pre>' . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</pre>';
    }
}

?>
