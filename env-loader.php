<?php
/**
 * Environment Variables Loader
 * Use this at the beginning of your PHP files to load .env.local
 * 
 * Usage:
 *   require_once 'env-loader.php';
 *   echo getenv('SUPABASE_URL');
 */

function loadEnvironmentVariables() {
    $env_file = __DIR__ . '/.env.local';
    
    if (!file_exists($env_file)) {
        error_log('Warning: .env.local file not found');
        return false;
    }
    
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
                $value = substr($value, 1, -1);
            }
            
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
    
    return true;
}

// Auto-load on include
loadEnvironmentVariables();
?>
