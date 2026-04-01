<?php
// Simple admin script to upload an Excel template to the database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['template'])) {
    $mysqli = new mysqli("localhost", "root", "", "excel_data");
    if ($mysqli->connect_error) {
        die("Database connection failed: " . $mysqli->connect_error);
    }

    // Create table with LONGBLOB for binary data
    $mysqli->query("CREATE TABLE IF NOT EXISTS templates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255),
        filedata LONGBLOB,
        upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Clear existing templates
    $mysqli->query("DELETE FROM templates");

    $filename = $_FILES['template']['name'];
    // Read file as binary
    $filedata = file_get_contents($_FILES['template']['tmp_name']);
    
    // Prepare statement to prevent corruption of binary data
    $stmt = $mysqli->prepare("INSERT INTO templates (filename, filedata) VALUES (?, ?)");
    $null = NULL;  // Need to bind as null first for BLOB
    $stmt->bind_param("sb", $filename, $null);  // 'b' for BLOB
    $stmt->send_long_data(1, $filedata);  // Properly send BLOB data
    
    if ($stmt->execute()) {
        echo "<div style='color: green; margin: 20px 0;'>Template uploaded successfully.</div>";
    } else {
        echo "<div style='color: red; margin: 20px 0;'>Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Excel Template</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
        }
        form {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
        }
        input[type="file"] {
            margin: 10px 0;
            padding: 10px;
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <h2>Upload Excel Template to Database</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="template" accept=".xlsx,.xls" required>
        <button type="submit">Upload</button>
    </form>
</body>
</html>
