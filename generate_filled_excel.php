<?php
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data || !isset($data['responses']) || !is_array($data['responses'])) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['error' => 'Invalid data']);
        exit;
    }

    $templatePath = __DIR__ . '/template.xlsx';
    if (!file_exists($templatePath)) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Template file not found: template.xlsx']);
        exit;
    }

    $spreadsheet = IOFactory::load($templatePath);
    $sheet = $spreadsheet->getActiveSheet();

    $auditInfo = $data['auditInfo'] ?? [];
    $atelier = '';
    if (!empty($auditInfo['atelier'])) {
        $atelier = ucfirst(strtolower($auditInfo['atelier']));
        if ($atelier === 'Electronique') {
            $atelier = 'Électronique';
        }
    }

    // Template metadata cells
    $sheet->setCellValue('C3', $atelier);
    $sheet->setCellValue('D4', $auditInfo['auditeur'] ?? '');
    $sheet->setCellValue('F3', $auditInfo['zone'] ?? '');
    $sheet->setCellValue('K3', $auditInfo['date'] ?? date('Y-m-d'));
    $sheet->setCellValue('D5', $auditInfo['audite'] ?? '');
    $sheet->setCellValue('F5', $auditInfo['produit'] ?? '');

    $questionRows = [
        5, 8, 10, 11, 13, 14, 16, 17, 19, 20,
        21, 22, 23, 25, 26, 28, 29, 30, 32, 33,
        34, 35, 37, 38, 39
    ];

    foreach ($data['responses'] as $i => $resp) {
        if (!isset($questionRows[$i])) {
            continue;
        }

        $row = $questionRows[$i];
        $answer = $resp['answer'] ?? '';
        $comment = $resp['comment'] ?? '';

        // Clear mark columns first
        $sheet->setCellValue("I{$row}", '');
        $sheet->setCellValue("J{$row}", '');
        $sheet->setCellValue("K{$row}", '');

        if ($answer === 'Oui') {
            $sheet->setCellValue("I{$row}", 'X');
        } elseif ($answer === 'Non') {
            $sheet->setCellValue("J{$row}", 'X');
        } elseif ($answer === 'N/A') {
            $sheet->setCellValue("K{$row}", 'X');
        }

        if ($comment !== '') {
            $sheet->setCellValue("L{$row}", $comment);
        }
    }

    $okCount = 0;
    $nokCount = 0;
    $naCount = 0;

    foreach ($questionRows as $row) {
        if (trim((string)$sheet->getCell("I{$row}")->getValue()) === 'X') $okCount++;
        if (trim((string)$sheet->getCell("J{$row}")->getValue()) === 'X') $nokCount++;
        if (trim((string)$sheet->getCell("K{$row}")->getValue()) === 'X') $naCount++;
    }

    $sheet->setCellValue('I40', $okCount);
    $sheet->setCellValue('J40', $nokCount);
    $sheet->setCellValue('K40', $naCount);

    $totalAnswered = $okCount + $nokCount;
    if ($totalAnswered > 0) {
        $percentage = ($okCount / $totalAnswered) * 100;
        $sheet->setCellValue('I41', number_format($percentage, 2, ',', '') . '%');
    } else {
        $sheet->setCellValue('I41', '0,00%');
    }

    $sheet->setCellValue('E42', $auditInfo['auditeur'] ?? '');
    $sheet->setCellValue('E43', $auditInfo['audite'] ?? '');

    $tmpFile = tempnam(sys_get_temp_dir(), 'audit_') . '.xlsx';
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->setPreCalculateFormulas(false);
    $writer->save($tmpFile);

    while (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="Audit_Tunelec_' . date('Y-m-d_H-i-s') . '.xlsx"');
    header('Content-Length: ' . filesize($tmpFile));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    readfile($tmpFile);
    unlink($tmpFile);
    exit;
} catch (Throwable $e) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
