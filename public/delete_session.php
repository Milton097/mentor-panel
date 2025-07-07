<?php
require 'vendor/autoload.php';
require 'config.php';

use Google\Client;
use Google\Service\Sheets;

function getClient() {
    $client = new Client();
    $client->setAuthConfig(CREDENTIALS_PATH);
    $client->addScope(Sheets::SPREADSHEETS);
    return $client;
}

$data = json_decode(file_get_contents('php://input'), true);
$student_id = $data['student_id'];
$sessionNum = $data['session'];

$client = getClient();
$service = new Sheets($client);

// Get all values
$response = $service->spreadsheets_values->get(SHEET2_ID, SHEET2_TAB . '!A2:H');
$values = $response->getValues();

foreach ($values as $i => $row) {
    if ($row[0] === $student_id && (int)$row[2] === (int)$sessionNum) {
        $rowIndex = $i + 2; // Google Sheets row (offset from A2)

        // Clear row by updating with empty strings
        $emptyRow = [ '', '', '', '', '', '', '', '', '' ];
        $updateRange = SHEET2_TAB . "!A{$rowIndex}:I{$rowIndex}";
        $updateBody = new Sheets\ValueRange(['values' => [$emptyRow]]);
        $service->spreadsheets_values->update(
            SHEET2_ID,
            $updateRange,
            $updateBody,
            ['valueInputOption' => 'USER_ENTERED']
        );

        echo "🗑️ Session deleted.";
        exit;
    }
}
echo "❌ Session not found.";
