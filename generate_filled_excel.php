<?php
// Prevent any output before headers
ob_start();

// Enable error logging to file
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

require 'vendor/autoload.php';
require 'config.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

// Set JSON header
header('Content-Type: application/json');

try {
    // Get POSTed JSON data
    $jsonInput = file_get_contents('php://input');
    if (empty($jsonInput)) {
        http_response_code(400);
        echo json_encode(['error' => 'No data received in request']);
        exit;
    }

    $data = json_decode($jsonInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'JSON decode error: ' . json_last_error_msg()]);
        exit;
    }

    // Try to fetch template from Supabase 
    $filedata = null;
    $filename = 'audit_report.xlsx';
    
    try {
        $db = new Supabase('templates');
        $templates = $db->select('order=id.desc&limit=1');
        
        if ($templates && count($templates) > 0) {
            $template = $templates[0];
            // Decode if stored as base64
            $filedata = base64_decode($template['filedata']);
            $filename = $template['filename'] ?? 'audit_report.xlsx';
        }
    } catch (Exception $e) {
        error_log("Template fetch error (non-critical): " . $e->getMessage());
        // Continue without template - will create blank spreadsheet
    }

    if (empty($filedata)) {
        error_log("No template found, will create blank spreadsheet");
        // Create a minimal blank template instead of failing
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Audit Report');
    } else {

    if (empty($filedata)) {
        error_log("No template found, will create blank spreadsheet");
        // Create a minimal blank template instead of failing
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Audit Report');
    } else {
        // Clear output buffer
        ob_end_clean();

        // Create a temporary directory
        $tempDir = sys_get_temp_dir() . '/excel_template_' . uniqid();
        mkdir($tempDir);
        
        // Create temporary Excel file
        $tempFile = $tempDir . '/template.xlsx';
        file_put_contents($tempFile, $filedata);

        try {
            // Load the spreadsheet with settings to preserve images
            $reader = IOFactory::createReader('Xlsx');
            $reader->setIncludeCharts(true);
            $reader->setReadDataOnly(false);
            $spreadsheet = $reader->load($tempFile);
            $sheet = $spreadsheet->getActiveSheet();
        } catch (Exception $e) {
            error_log("Template load error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to load template: ' . $e->getMessage()]);
            exit;
        }
    }

    // Fill audit info (adjust these cell references according to your template)
    $auditInfo = $data['auditInfo'] ?? [];
    
    // Capitalize first letter of atelier if it exists
    $atelier = '';
    if (isset($auditInfo['atelier']) && !empty($auditInfo['atelier'])) {
        $atelier = ucfirst(strtolower($auditInfo['atelier']));
        if ($atelier === 'Electronique') {
            $atelier = 'Électronique'; // Fix special character
        }
    }
    
    $sheet->setCellValue('C3', $atelier);
    $sheet->setCellValue('D4', $auditInfo['auditeur'] ?? '');
    $sheet->setCellValue('F3', $auditInfo['zone'] ?? '');
    $sheet->setCellValue('J3', 'date');
    $sheet->setCellValue('K3', $auditInfo['date'] ?? '');
    $sheet->setCellValue('D5', $auditInfo['audite'] ?? '');
    $sheet->setCellValue('F5', $auditInfo['produit'] ?? '');

        // Question row mapping - updated to include all questions
        $questionRows = array(
            5,  // THEME 1 - SECURITE - Les risques de sécuritée
            8,  // THEME 2 - EPI - La matrice d'utilisation des EPI
            10, // THEME 3 - Dossier de Travail - Les instructions de travail
            11, // Les fiches suiveuses
            13, // THEME 4 - Ok Démarrage - Les instruction d'Ok démarrage
            14, // Le document d'Ok démarrage
            16, // THEME 5 - Tableau QCDM - Le tableau QCDM est propre
            17, // Plan d'action en cours
            19, // Ouverture d'un plan d'action
            20, // THEME 6 - Qualité - Disponibilité de bac rouge
            21, // Tous pièces NC
            22, // Chaque NC fait l'objet
            23, // La déclaration des rebuts
            25, // Travail pièce par pièce
            26, // THEME 7 - 5S - Zonning de la zone
            28, // Pas de mélange de pièce
            29, // THEME 8 - Qualification des opérateurs - Le document Grille de Polyvalence
            30, // Les fiches d'habilitation
            32, // Aucun opérateur non habilité
            33, // THEME 9 - Maintenance - Etiquette Maint Niv 02
            34, // Fiche intervention maintenance
            35, // Pas de problème maintenance
            37, // Pas de fuite d'huile
            38, // THEME 10 - Conditionnement - Les encours sont stockés
            39, // Existance d'étiquettes et A la fin de ligne
        );

        foreach ($data['responses'] as $i => $resp) {
            if (isset($questionRows[$i])) {
                $currentRow = $questionRows[$i];
                
                // Clear previous marks first
                $sheet->setCellValue("I{$currentRow}", ''); // Ok column
                $sheet->setCellValue("J{$currentRow}", ''); // Nok column
                $sheet->setCellValue("K{$currentRow}", ''); // N/A column
                
                // Place X in the appropriate column based on the answer
                switch($resp['answer']) {
                    case 'Oui':
                        $sheet->setCellValue("I{$currentRow}", 'X'); // Ok column
                        break;
                    case 'Non':
                        $sheet->setCellValue("J{$currentRow}", 'X'); // Nok column
                        break;
                    case 'N/A':
                        $sheet->setCellValue("K{$currentRow}", 'X'); // N/A column
                        break;
                }
                
                // Add comment if provided
                if (!empty($resp['comment'])) {
                    $sheet->setCellValue("L{$currentRow}", $resp['comment']); // Comment column
                }
            }
        }

        // Calculate totals
        $okCount = 0;
        $nokCount = 0;
        $naCount = 0;

        // Count responses for each question row
        foreach ($questionRows as $row) {
            $cellI = trim($sheet->getCell("I{$row}")->getValue());
            $cellJ = trim($sheet->getCell("J{$row}")->getValue());
            $cellK = trim($sheet->getCell("K{$row}")->getValue());
            
            if ($cellI === 'X') $okCount++;
            if ($cellJ === 'X') $nokCount++;
            if ($cellK === 'X') $naCount++;
        }

        // Add totals row (row 40)
        $sheet->setCellValue("I40", $okCount);
        $sheet->setCellValue("J40", $nokCount);
        $sheet->setCellValue("K40", $naCount);

        // Calculate and add percentage (row 41)
        $totalAnsweredQuestions = $okCount + $nokCount; // Don't include N/A in total
        if ($totalAnsweredQuestions > 0) {
            $percentage = ($okCount / $totalAnsweredQuestions) * 100;
            // Format with exactly 2 decimal places and comma as separator
            $formattedPercentage = number_format($percentage, 2, ',', '');
            $sheet->setCellValue("I41", $formattedPercentage . '%');
        } else {
            $sheet->setCellValue("I41", "0,00%");
        }

        // Add auditeur and audité names (rows 42 and 43)
        $sheet->setCellValue("E42", $auditInfo['auditeur'] ?? '');
        $sheet->setCellValue("E43", $auditInfo['audite'] ?? '');

        // Set headers for Excel file download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Audit_Tunelec_'.date('Y-m-d_H-i-s').'.xlsx"');
        header('Cache-Control: max-age=0');

        // Create writer
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->setPreCalculateFormulas(false);
        
        // Save and output
        $writer->save('php://output');
        exit;

} catch (Exception $e) {
    error_log("Error in generate_filled_excel.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    exit;
}
?>
