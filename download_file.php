<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Validate and sanitize the filename
$requestedFile = isset($_GET['file']) ? $_GET['file'] : '';
$filename = basename($requestedFile); // Remove any directory components

// Validate that the file exists and is within the Desktop Audits directory
$filePath = 'C:/Users/Administrateur.TUNELEC/Desktop/Audits_Tunelec/' . $filename;
if (!file_exists($filePath) || !is_file($filePath) || pathinfo($filePath, PATHINFO_EXTENSION) !== 'xlsx') {
    header('HTTP/1.0 404 Not Found');
    die('File not found');
}

// Set headers for file download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: max-age=0');

// Send the file
readfile($filePath);
exit;
