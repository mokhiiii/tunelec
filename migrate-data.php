<?php
/**
 * Data Migration Script
 * Migrates data from MySQL to Supabase
 * 
 * This script reads your existing MySQL data and inserts it into Supabase
 * Run this ONCE to migrate all your data
 * 
 * Usage:
 *   1. Update MySQL connection details below
 *   2. Load in browser: http://localhost/tunelec/migrate-data.php
 *   3. Check output for success/errors
 */

require_once 'env-loader.php';
require_once 'supabase.php';

// MySQL connection (update these with your credentials)
$mysql_host = "localhost";
$mysql_user = "root";
$mysql_password = "";
$mysql_db = "tunelec";

echo "<h1>TunElec Data Migration</h1>";
echo "<p>Migrating data from MySQL to Supabase...</p>";
echo "<hr>";

try {
    // Connect to MySQL
    $mysql_conn = new PDO("mysql:host=$mysql_host;dbname=$mysql_db", $mysql_user, $mysql_password);
    $mysql_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to MySQL<br>";
} catch (Exception $e) {
    echo "❌ MySQL Connection Error: " . $e->getMessage() . "<br>";
    die();
}

try {
    // 1. Migrate Users
    echo "<h2>Migrating Users...</h2>";
    
    $stmt = $mysql_conn->prepare("SELECT id, username, password, full_name FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) > 0) {
        $supabase = new Supabase('users');
        $migrated = 0;
        
        foreach ($users as $user) {
            try {
                $supabase->insert([
                    'username' => $user['username'],
                    'password' => $user['password'], // Already hashed in MySQL
                    'full_name' => $user['full_name']
                ]);
                $migrated++;
            } catch (Exception $e) {
                echo "⚠️ Could not migrate user '{$user['username']}': " . $e->getMessage() . "<br>";
            }
        }
        
        echo "✅ Migrated $migrated users<br>";
    } else {
        echo "ℹ️ No users to migrate<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Users Migration Error: " . $e->getMessage() . "<br>";
}

try {
    // 2. Migrate Questions (if they exist in database)
    echo "<h2>Migrating Questions...</h2>";
    
    // Check if questions table exists in MySQL
    $check = $mysql_conn->query("SHOW TABLES LIKE 'questions'");
    $table_exists = $check->rowCount() > 0;
    
    if ($table_exists) {
        $stmt = $mysql_conn->prepare("SELECT id, question_text, row_number FROM questions");
        $stmt->execute();
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($questions) > 0) {
            $supabase = new Supabase('questions');
            $migrated = 0;
            
            foreach ($questions as $question) {
                try {
                    $supabase->insert([
                        'question_text' => $question['question_text'],
                        'row_number' => $question['row_number'] ?? 0
                    ]);
                    $migrated++;
                } catch (Exception $e) {
                    echo "⚠️ Could not migrate question: " . $e->getMessage() . "<br>";
                }
            }
            
            echo "✅ Migrated $migrated questions<br>";
        } else {
            echo "ℹ️ No questions to migrate<br>";
        }
    } else {
        echo "ℹ️ Questions table not found in MySQL (loading from CSV instead)<br>";
        
        // Try to load from questions.csv
        $csv_file = __DIR__ . '/questions.csv';
        if (file_exists($csv_file)) {
            $supabase = new Supabase('questions');
            $migrated = 0;
            
            if (($handle = fopen($csv_file, 'r')) !== false) {
                while (($row = fgetcsv($handle)) !== false) {
                    if (!empty($row[0])) {
                        try {
                            $supabase->insert([
                                'question_text' => trim($row[0]),
                                'row_number' => isset($row[1]) ? intval($row[1]) : 0
                            ]);
                            $migrated++;
                        } catch (Exception $e) {
                            echo "⚠️ Could not migrate question from CSV: " . $e->getMessage() . "<br>";
                        }
                    }
                }
                fclose($handle);
            }
            
            echo "✅ Migrated $migrated questions from CSV<br>";
        } else {
            echo "ℹ️ No questions.csv file found<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Questions Migration Error: " . $e->getMessage() . "<br>";
}

try {
    // 3. Migrate Question Images
    echo "<h2>Migrating Question Images...</h2>";
    
    $check = $mysql_conn->query("SHOW TABLES LIKE 'question_images'");
    $table_exists = $check->rowCount() > 0;
    
    if ($table_exists) {
        $stmt = $mysql_conn->prepare("SELECT id, question_id, image_type, upload_date FROM question_images");
        $stmt->execute();
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($images) > 0) {
            $supabase = new Supabase('question_images');
            $migrated = 0;
            
            foreach ($images as $image) {
                try {
                    $supabase->insert([
                        'question_id' => $image['question_id'],
                        'image_type' => $image['image_type'],
                        'image_data' => "Image stored in Supabase Storage"
                    ]);
                    $migrated++;
                } catch (Exception $e) {
                    echo "⚠️ Could not migrate image {$image['id']}: " . $e->getMessage() . "<br>";
                }
            }
            
            echo "✅ Migrated $migrated image references<br>";
            echo "ℹ️ Note: Upload actual image files to Supabase Storage manually<br>";
        } else {
            echo "ℹ️ No images to migrate<br>";
        }
    } else {
        echo "ℹ️ Questions images table not found<br>";
    }
    
} catch (Exception $e) {
    echo "⚠️ Images Migration Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>✅ Migration Complete!</h2>";
echo "<p>Your data has been migrated to Supabase. You can now:</p>";
echo "<ol>";
echo "<li>Verify your data in the Supabase dashboard</li>";
echo "<li>Upload images to Supabase Storage</li>";
echo "<li>Test your API endpoints</li>";
echo "<li>Deploy to production</li>";
echo "</ol>";
echo "<p><strong>Next Step:</strong> Delete this file (migrate-data.php) before deploying to production!</p>";

?>
