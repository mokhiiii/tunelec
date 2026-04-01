<?php
require 'vendor/autoload.php'; // Or path to PhpSpreadsheet if manual

use PhpOffice\PhpSpreadsheet\IOFactory;

$spreadsheet = IOFactory::load('template.xlsx'); // Change to your filename
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray();

$mysqli = new mysqli("localhost", "root", "", "excel_data");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Optional: recreate table
$mysqli->query("DROP TABLE IF EXISTS items");
$mysqli->query("CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    value VARCHAR(100)
)");

for ($i = 1; $i < count($data); $i++) { // skip first row (header)
    $name = $mysqli->real_escape_string($data[$i][0]);
    $value = $mysqli->real_escape_string($data[$i][1]);
    $mysqli->query("INSERT INTO items (name, value) VALUES ('$name', '$value')");
}

echo "Excel imported successfully!";
?>
