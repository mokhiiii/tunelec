<?php
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tunelec";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question_id'])) {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $questionId = $_POST['question_id'];
        
        // Validate question_id is numeric
        if (!is_numeric($questionId)) {
            throw new Exception('Invalid question ID');
        }
        
        $stmt = $conn->prepare("DELETE FROM question_images WHERE question_id = ?");
        $result = $stmt->execute([$questionId]);
        
        if ($result) {
            $rowCount = $stmt->rowCount();
            if ($rowCount > 0) {
                echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 40px auto; padding: 20px; background-color: #e8f5e9; border: 1px solid #66bb6a; border-radius: 5px;'>";
                echo "<h2 style='color: #2e7d32; margin-top: 0;'>Success</h2>";
                echo "<p style='color: #555;'>Successfully deleted image for question " . htmlspecialchars($questionId) . "</p>";
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'manage_images.php';
                    }, 1500);
                </script>";
                echo "</div>";
            } else {
                throw new Exception('No image found for question ' . htmlspecialchars($questionId));
            }
        } else {
            throw new Exception('Delete operation failed');
        }
    } catch(Exception $e) {
        echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 40px auto; padding: 20px; background-color: #fff3f3; border: 1px solid #ff8c8c; border-radius: 5px;'>";
        echo "<h2 style='color: #d32f2f; margin-top: 0;'>Error</h2>";
        echo "<p style='color: #555;'>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<a href='manage_images.php' style='display: inline-block; margin-top: 15px; padding: 10px 20px; background-color: #2196f3; color: white; text-decoration: none; border-radius: 4px;'>Return to Images Page</a>";
        echo "</div>";
    }
} else {
    // Redirect to manage_images.php if accessed directly
    header('Location: manage_images.php');
    exit;
}
