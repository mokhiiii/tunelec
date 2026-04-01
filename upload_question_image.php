<?php
ini_set('upload_max_filesize', '64M');
ini_set('post_max_size', '64M');
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '300');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tunelec";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    try {
        // Validate file size (max 16MB)
        if ($_FILES['image']['size'] > 16777216) {
            throw new Exception('File is too large. Maximum size is 16MB.');
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPG, PNG and GIF are allowed.');
        }

        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Set MySQL session variables for larger packets
        $conn->exec("SET GLOBAL max_allowed_packet=67108864"); // 64MB
        
        $questionId = $_POST['question_id'];
        $file = $_FILES['image'];
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            // Resize image if it's too large
            $maxWidth = 1920;
            $maxHeight = 1080;
            
            list($width, $height) = getimagesize($file['tmp_name']);
            
            if ($width > $maxWidth || $height > $maxHeight) {
                // Calculate new dimensions
                $ratio = min($maxWidth / $width, $maxHeight / $height);
                $newWidth = round($width * $ratio);
                $newHeight = round($height * $ratio);
                
                // Create new image
                $src = imagecreatefromstring(file_get_contents($file['tmp_name']));
                $dst = imagecreatetruecolor($newWidth, $newHeight);
                
                // Resize
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                
                // Capture the resized image
                ob_start();
                imagejpeg($dst, null, 85);
                $imageData = ob_get_clean();
                
                // Clean up
                imagedestroy($src);
                imagedestroy($dst);
            } else {
                $imageData = file_get_contents($file['tmp_name']);
            }
            
            $imageType = $file['type'];
            
            $stmt = $conn->prepare("INSERT INTO question_images (question_id, image_data, image_type) 
                                  VALUES (?, ?, ?) 
                                  ON DUPLICATE KEY UPDATE 
                                  image_data = VALUES(image_data),
                                  image_type = VALUES(image_type)");
            
            $stmt->execute([$questionId, $imageData, $imageType]);
            
            header('Location: manage_images.html');
            exit();
        } else {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    throw new Exception('The uploaded file exceeds the upload_max_filesize directive in php.ini');
                case UPLOAD_ERR_FORM_SIZE:
                    throw new Exception('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form');
                case UPLOAD_ERR_PARTIAL:
                    throw new Exception('The uploaded file was only partially uploaded');
                case UPLOAD_ERR_NO_FILE:
                    throw new Exception('No file was uploaded');
                default:
                    throw new Exception('Unknown error occurred during file upload');
            }
        }
    } catch(Exception $e) {
        echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 40px auto; padding: 20px; background-color: #fff3f3; border: 1px solid #ff8c8c; border-radius: 5px;'>";
        echo "<h2 style='color: #d32f2f; margin-top: 0;'>Error</h2>";
        echo "<p style='color: #555;'>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<a href='manage_images.html' style='display: inline-block; margin-top: 15px; padding: 10px 20px; background-color: #2196f3; color: white; text-decoration: none; border-radius: 4px;'>Return to Upload Page</a>";
        echo "</div>";
    }
}
