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

$client = getClient();
$service = new Sheets($client);

$student_id = $_POST['student_id'];
$mentor_id = $_POST['mentor_id'];
$sessions = $_POST['session'];

// 🧠 Step 1: Get existing sessions for this student
$response = $service->spreadsheets_values->get(SHEET2_ID, SHEET2_TAB . '!A2:C');
$existing = $response->getValues();

$lastSessionNum = 0;
if ($existing) {
    foreach ($existing as $row) {
        if (isset($row[0]) && $row[0] === $student_id && isset($row[2])) {
            $num = intval($row[2]);
            if ($num > $lastSessionNum) {
                $lastSessionNum = $num;
            }
        }
    }
}

$rows = [];
$sessionNumber = $lastSessionNum + 1;

foreach ($sessions as $session) {
    $rows[] = [
        $student_id,
        $mentor_id,
        $sessionNumber,
        $session['date'],
        $session['time'],  // Expected: '5:00 PM' etc.
        '', '', '',
        'FALSE'
    ];
    $sessionNumber++;
}

// ✅ Save to Sheet2
$sessionBody = new Sheets\ValueRange(['values' => $rows]);
$service->spreadsheets_values->append(
    SHEET2_ID,
    SHEET2_TAB . '!A1',
    $sessionBody,
    ['valueInputOption' => 'USER_ENTERED']
);

// ✅ Update session count in Sheet1 (Column H)
$students_range = SHEET1_TAB . '!A:H';
$response = $service->spreadsheets_values->get(SHEET1_ID, $students_range);
$values = $response->getValues();

foreach ($values as $rowIndex => $row) {
    if (isset($row[1]) && $row[1] === $student_id) { // B = student_id
        $updateRange = SHEET1_TAB . '!H' . ($rowIndex + 1);
        $totalSessionCount = $lastSessionNum + count($rows);
        $updateBody = new Sheets\ValueRange(['values' => [[$totalSessionCount]]]);
        $service->spreadsheets_values->update(
            SHEET1_ID,
            $updateRange,
            $updateBody,
            ['valueInputOption' => 'USER_ENTERED']
        );
        break;
    }
}

// ✅ Show success and redirect
echo "<!DOCTYPE html>
<html>
<head>
  <meta charset='UTF-8'>
  <title>Success</title>
  <meta http-equiv='refresh' content='3;url=index.php'>
  <style>
    body { font-family: Arial, sans-serif; text-align: center; padding-top: 100px; }
    .success { font-size: 24px; color: green; }
    .info { font-size: 16px; margin-top: 20px; }
  </style>
</head>
<body>
  <div class='success'>✅ Sessions added successfully.</div>
  <div class='info'>Redirecting to main page in 3 seconds...<br><a href='index.php'>Click here if not redirected</a></div>
</body>
</html>";
exit;
