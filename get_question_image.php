<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tunelec";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get image for a specific question
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['question_id'])) {
        $stmt = $conn->prepare("SELECT image_data, image_type FROM question_images WHERE question_id = ?");
        $stmt->execute([$_GET['question_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'image' => base64_encode($result['image_data']),
                'type' => $result['image_type']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No image found for this question']);
        }
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
