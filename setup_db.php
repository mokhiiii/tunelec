<?php
header('Content-Type: text/html; charset=utf-8');
$servername = "localhost";
$username = "root";
$password = "";

try {
    // First connect without specifying a database
    $conn = new PDO("mysql:host=$servername", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS tunelec";
    $conn->exec($sql);
    echo "Database 'tunelec' created successfully<br>";
    
    // Now connect to the tunelec database
    $conn = new PDO("mysql:host=$servername;dbname=tunelec", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop the table if it exists to ensure we have a fresh start
    $conn->exec("DROP TABLE IF EXISTS question_images");
      // Create table for question images
    $sql = "CREATE TABLE question_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question_id INT NOT NULL,
        image_data LONGBLOB NOT NULL,
        image_type VARCHAR(50) NOT NULL,
        upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_question (question_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "Table images_questions created successfully!";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
