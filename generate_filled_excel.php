<?php
if (ob_get_level()) ob_end_clean();
if (ob_get_level()) ob_clean();

require_once __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

header('Access-Control-Allow-Origin: *');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['responses'])) {
    header('Content-Type: application/json');
    http_response_code(400);
    die(json_encode(['error' => 'Invalid data']));
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Title
$sheet->setCellValue('A1', 'TunElec Audit');
$sheet->getStyle('A1')->getFont()->setBold(true);

// Info
$row = 3;
$auditInfo = $data['auditInfo'] ?? [];
$sheet->setCellValue("A{$row}", 'Auditeur: ' . ($auditInfo['auditeur'] ?? ''));
$row++;
$sheet->setCellValue("A{$row}", 'Zone: ' . ($auditInfo['zone'] ?? ''));
$row++;
$sheet->setCellValue("A{$row}", 'Date: ' . ($auditInfo['date'] ?? date('Y-m-d')));

// Responses
$row = 8;
$sheet->setCellValue("A{$row}", 'Question');
$sheet->setCellValue("B{$row}", 'Answer');

$ok = $nok = $na = 0;
foreach ($data['responses'] ?? [] as $r) {
    $row++;
    $sheet->setCellValue("A{$row}", substr($r['question'] ?? '', 0, 50));
    $ans = $r['answer'] ?? '';
    $sheet->setCellValue("B{$row}", $ans);
    if ($ans === 'Oui') $ok++;
    elseif ($ans === 'Non') $nok++;
    elseif ($ans === 'N/A') $na++;
}

// Summary
$row += 2;
$sheet->setCellValue("A{$row}", "OK: $ok | NOK: $nok | N/A: $na");

$sheet->getColumnDimension('A')->setWidth(40);
$sheet->getColumnDimension('B')->setWidth(12);

// Save to temp file first
$tmpfile = tempnam(sys_get_temp_dir(), 'audit_');
$writer = new Xlsx($spreadsheet);
$writer->save($tmpfile);

// Read and output
while (ob_get_level()) ob_end_clean();

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Audit_' . date('Y-m-d_H-i-s') . '.xlsx"');
header('Content-Length: ' . filesize($tmpfile));
header('Cache-Control: no-cache');

readfile($tmpfile);
unlink($tmpfile);
exit;
?>
?>
?>
?>
