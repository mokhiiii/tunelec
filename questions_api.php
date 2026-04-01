<?php
// Simple API for managing questions in questions.csv
$csvFile = __DIR__ . '/questions.csv';
header('Content-Type: application/json');

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Return all questions as JSON
        $questions = [];
        if (file_exists($csvFile)) {
            $f = fopen($csvFile, 'r');
            while (($row = fgetcsv($f)) !== false) {
                // row[0] = question, row[1] = row (line)
                if (isset($row[0])) {
                    $questions[] = [
                        'question' => $row[0],
                        'row' => isset($row[1]) ? $row[1] : ''
                    ];
                }
            }
            fclose($f);
        }
        // Return as array of objects for frontend compatibility
        $questionsObj = array_map(function($q, $i) { return ['id' => $i+1, 'question' => $q['question'], 'row' => $q['row']]; }, $questions, array_keys($questions));
        echo json_encode(['success' => true, 'questions' => $questionsObj]);
        break;
    case 'POST':
        // Add or update questions (replace all)
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['questions']) || !is_array($input['questions'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid input']);
            exit;
        }
        $f = fopen($csvFile, 'w');
        foreach ($input['questions'] as $q) {
            // Accept object with question and row
            if (is_array($q) && isset($q['question'])) {
                $text = trim($q['question']);
                $row = isset($q['row']) ? trim($q['row']) : '';
            } else {
                $text = trim($q);
                $row = '';
            }
            if ($text !== '') {
                fputcsv($f, [$text, $row]);
            }
        }
        fclose($f);
        echo json_encode(['success' => true]);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        break;
}
