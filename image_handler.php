<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tunelec";

function getConnection() {
    global $servername, $username, $password, $dbname;
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $conn;
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    try {
        $conn = getConnection();
        $questionId = $_POST['question_id'];
        $file = $_FILES['image'];
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            $imageData = file_get_contents($file['tmp_name']);
            $imageType = $file['type'];
            
            $stmt = $conn->prepare("INSERT INTO images_questions (question_id, image_data, image_type) 
                                  VALUES (?, ?, ?) 
                                  ON DUPLICATE KEY UPDATE 
                                  image_data = VALUES(image_data),
                                  image_type = VALUES(image_type)");
            
            $stmt->execute([$questionId, $imageData, $imageType]);
            echo json_encode(['success' => true, 'message' => 'Image uploaded successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error uploading file']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Handle image retrieval
else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['question_id'])) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT image_data, image_type FROM images_questions WHERE question_id = ?");
        $stmt->execute([$_GET['question_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'image' => base64_encode($result['image_data']),
                'type' => $result['image_type']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Image not found']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
