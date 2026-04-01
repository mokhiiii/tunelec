<?php
/**
 * Generate Excel Report - Binary File Output
 * MUST NOT load config.php to avoid output corruption
 */

// ABSOLUTELY MUST BE FIRST - before any require
if (ob_get_level()) ob_end_clean();

// Minimal requires only
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// CORS headers must be before any output
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Start output buffering to catch any accidental output
ob_start();

try {
    // Parse input
    $input = file_get_contents('php://input');
    if (!$input) {
        throw new Exception('No data received');
    }

    $data = json_decode($input, true);
    if (!$data) {
        throw new Exception('Invalid JSON');
    }

    // Create spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Audit Report');

    // Title
    $sheet->setCellValue('A1', 'TunElec Audit Report');
    $sheet->mergeCells('A1:D1');
    $sheet->getStyle('A1')->getFont()->setBold(true);

    // Audit info
    $auditInfo = $data['auditInfo'] ?? [];
    $row = 3;
    
    $sheet->setCellValue("A{$row}", 'Auditeur:');
    $sheet->setCellValue("B{$row}", $auditInfo['auditeur'] ?? '');
    $row++;
    
    $sheet->setCellValue("A{$row}", 'Audité:');
    $sheet->setCellValue("B{$row}", $auditInfo['audite'] ?? '');
    $row++;
    
    $sheet->setCellValue("A{$row}", 'Zone:');
    $sheet->setCellValue("B{$row}", $auditInfo['zone'] ?? '');
    $row++;
    
    $sheet->setCellValue("A{$row}", 'Date:');
    $sheet->setCellValue("B{$row}", $auditInfo['date'] ?? date('Y-m-d'));

    // Questions header
    $row = 10;
    $sheet->setCellValue("A{$row}", 'Question');
    $sheet->setCellValue("B{$row}", 'Answer');
    $sheet->setCellValue("C{$row}", 'Comment');
    $sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);

    // Responses
    $okCount = 0;
    $nokCount = 0;
    $naCount = 0;

    foreach ($data['responses'] ?? [] as $r) {
        $row++;
        $sheet->setCellValue("A{$row}", $r['question'] ?? '');
        $sheet->setCellValue("B{$row}", $r['answer'] ?? '');
        $sheet->setCellValue("C{$row}", $r['comment'] ?? '');
        
        if ($r['answer'] === 'Oui') $okCount++;
        elseif ($r['answer'] === 'Non') $nokCount++;
        elseif ($r['answer'] === 'N/A') $naCount++;
    }

    // Summary
    $row += 2;
    $sheet->setCellValue("A{$row}", 'Summary');
    $sheet->getStyle("A{$row}")->getFont()->setBold(true);
    $row++;
    $sheet->setCellValue("A{$row}", 'Oui:');
    $sheet->setCellValue("B{$row}", $okCount);
    $row++;
    $sheet->setCellValue("A{$row}", 'Non:');
    $sheet->setCellValue("B{$row}", $nokCount);
    $row++;
    $sheet->setCellValue("A{$row}", 'N/A:');
    $sheet->setCellValue("B{$row}", $naCount);
    $row++;

    // Compliance %
    $total = $okCount + $nokCount;
    if ($total > 0) {
        $pct = ($okCount / $total) * 100;
        $sheet->setCellValue("A{$row}", 'Compliance:');
        $sheet->setCellValue("B{$row}", number_format($pct, 2) . '%');
    }

    // Column widths
    $sheet->getColumnDimension('A')->setWidth(25);
    $sheet->getColumnDimension('B')->setWidth(12);
    $sheet->getColumnDimension('C')->setWidth(35);

    // CRITICAL: Clear all buffers before binary output
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Output Excel file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="Audit_' . date('Y-m-d_H-i-s') . '.xlsx"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit(0);

} catch (Exception $e) {
    // Clear buffers on error
    while (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit(1);
}
?>
?>
?>
