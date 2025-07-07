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

// Step 1: Check if student_id already exists in SHEET1
$response = $service->spreadsheets_values->get(SHEET1_ID, SHEET1_TAB . '!A2:B'); // Column B has student_id
$rows = $response->getValues();

$already_exists = false;
if ($rows) {
    foreach ($rows as $row) {
        if (isset($row[1]) && trim($row[1]) === $student_id) {
            $already_exists = true;
            break;
        }
    }
}

if ($already_exists) {
    // Skip adding to sheet, just redirect
    echo "<!DOCTYPE html>
    <html><head>
    <meta http-equiv='refresh' content='3;url=schedule.php?student_id=$student_id&mentor_id=$mentor_id'>
    <style>body { text-align: center; font-family: sans-serif; padding-top: 50px; }</style>
    </head>
    <body>
    <h2>⚠️ Student already added!</h2>
    <p>Redirecting to session page...</p>
    </body></html>";
    exit;
}

// Step 2: Add to SHEET1
$students_data = [[
    $_POST['student_name'],
    $student_id,
    $_POST['student_email'],
    $mentor_id,
    $_POST['mentor_name'],
    $_POST['mentor_email'],
    $_POST['gmeet_link'],
    '' // sessions_per_week - initially blank
]];

$students_body = new Sheets\ValueRange(['values' => $students_data]);
$service->spreadsheets_values->append(
    SHEET1_ID,
    SHEET1_TAB . '!A1',
    $students_body,
    ['valueInputOption' => 'USER_ENTERED']
);

// Step 4: Redirect to schedule entry
header("Location: schedule.php?student_id=" . urlencode($student_id) . "&mentor_id=" . urlencode($mentor_id));
exit;
