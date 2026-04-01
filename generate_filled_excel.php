<?php
/**
 * Generate Excel Report from Audit Data
 * Simplified version that works with Supabase
 */

require 'vendor/autoload.php';
require 'config.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Get input
    $input = file_get_contents('php://input');
    if (empty($input)) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['error' => 'No data received']);
        exit;
    }

    $data = json_decode($input, true);
    if (!$data) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }

    // Create new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Audit Report');

    // Add headers
    $sheet->setCellValue('A1', 'TunElec Audit Report');
    $sheet->mergeCells('A1:D1');
    $sheet->getStyle('A1')->getFont()->setBold(true);

    // Add audit info
    $auditInfo = $data['auditInfo'] ?? [];
    $row = 3;
    $sheet->setCellValue("A$row", 'Auditeur:');
    $sheet->setCellValue("B$row", $auditInfo['auditeur'] ?? '');
    $row++;
    $sheet->setCellValue("A$row", 'Audité:');
    $sheet->setCellValue("B$row", $auditInfo['audite'] ?? '');
    $row++;
    $sheet->setCellValue("A$row", 'Zone:');
    $sheet->setCellValue("B$row", $auditInfo['zone'] ?? '');
    $row++;
    $sheet->setCellValue("A$row", 'Date:');
    $sheet->setCellValue("B$row", $auditInfo['date'] ?? date('Y-m-d'));

    // Add responses
    $row = 10;
    $sheet->setCellValue("A$row", 'Question');
    $sheet->setCellValue("B$row", 'Answer');
    $sheet->setCellValue("C$row", 'Comment');
    
    $sheet->getStyle("A$row:C$row")->getFont()->setBold(true);

    $responses = $data['responses'] ?? [];
    $okCount = 0;
    $nokCount = 0;
    $naCount = 0;

    foreach ($responses as $r) {
        $row++;
        $sheet->setCellValue("A$row", $r['question'] ?? '');
        $sheet->setCellValue("B$row", $r['answer'] ?? '');
        $sheet->setCellValue("C$row", $r['comment'] ?? '');
        
        if ($r['answer'] === 'Oui') $okCount++;
        if ($r['answer'] === 'Non') $nokCount++;
        if ($r['answer'] === 'N/A') $naCount++;
    }

    // Add summary
    $row += 2;
    $sheet->setCellValue("A$row", 'Summary');
    $sheet->getStyle("A$row")->getFont()->setBold(true);
    $row++;
    $sheet->setCellValue("A$row", 'Ok:');
    $sheet->setCellValue("B$row", $okCount);
    $row++;
    $sheet->setCellValue("A$row", 'Non-Ok:');
    $sheet->setCellValue("B$row", $nokCount);
    $row++;
    $sheet->setCellValue("A$row", 'N/A:');
    $sheet->setCellValue("B$row", $naCount);

    // Calculate percentage
    $total = $okCount + $nokCount;
    if ($total > 0) {
        $percentage = ($okCount / $total) * 100;
        $row += 2;
        $sheet->setCellValue("A$row", 'Compliance:');
        $sheet->setCellValue("B$row", number_format($percentage, 2) . '%');
    }

    // Adjust column widths
    $sheet->getColumnDimension('A')->setWidth(30);
    $sheet->getColumnDimension('B')->setWidth(15);
    $sheet->getColumnDimension('C')->setWidth(40);

    // Output file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="Audit_' . date('Y-m-d_H-i-s') . '.xlsx"');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    error_log('Excel generation error: ' . $e->getMessage());
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
?>
?>
