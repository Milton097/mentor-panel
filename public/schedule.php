<?php include('session.php'); ?>
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

$student_id = $_GET['student_id'] ?? '';
$mentor_id = $_GET['mentor_id'] ?? '';

$client = getClient();
$service = new Sheets($client);

// Fetch all session data
$response = $service->spreadsheets_values->get(SHEET2_ID, SHEET2_TAB . '!A2:H');
$allSessions = $response->getValues();

// Filter sessions by student_id
$studentSessions = [];
$latestSessionNum = 0;

foreach ($allSessions as $row) {
    if (isset($row[0]) && trim($row[0]) === $student_id) {
        $studentSessions[] = $row;

        if (isset($row[2]) && is_numeric($row[2])) {
            $latestSessionNum = max($latestSessionNum, (int)$row[2]);
        }
    }
}
$nextSessionNum = $latestSessionNum + 1;
?>
<?php
$student_id = $_GET['student_id'] ?? '';
$mentor_id = $_GET['mentor_id'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
  <title>Add Sessions</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .session-row { margin-bottom: 15px; padding: 10px; border: 1px solid #ccc; border-radius: 6px; background: #f1f1f1; }
    .remove-btn { background: red; color: white; padding: 2px 6px; margin-left: 10px; border: none; cursor: pointer; }
  </style>
</head>
<body>
  <h2>Schedule Sessions</h2>

  <form method="POST" action="submit_schedule.php">
    <label>Student ID:</label>
    <input name="student_id" value="<?= htmlspecialchars($student_id) ?>" readonly><br>
    <label>Mentor ID:</label>
    <input name="mentor_id" value="<?= htmlspecialchars($mentor_id) ?>" readonly><br>
    <h3>📋 Existing Sessions for <?php echo htmlspecialchars($student_id); ?>:</h3>
    <table border="1" cellpadding="8" id="sessionTable">
      <tr>
        <th>Session</th>
        <th>Date</th>
        <th>Time</th>
        <th>Completed</th>
        <th>Actions</th>
      </tr>
      <?php
      if (!empty($studentSessions)) {
          foreach ($studentSessions as $index => $row) {
              $sessionNum = htmlspecialchars($row[2] ?? '');
              $date = htmlspecialchars($row[3] ?? '');
              $time = htmlspecialchars($row[4] ?? '');
              $completed = htmlspecialchars($row[7] ?? 'FALSE');

              echo "<tr data-session='$sessionNum'>";
              echo "<td>$sessionNum</td>";
              echo "<td contenteditable='true' class='editable-date'>$date</td>";
              echo "<td contenteditable='true' class='editable-time'>$time</td>";
              echo "<td>$completed</td>";
              echo "<td>
                      <button onclick='updateSession($sessionNum)'>💾 Save</button>
                      <button onclick='deleteSession($sessionNum)'>🗑️ Delete</button>
                    </td>";
              echo "</tr>";
          }
      } else {
          echo "<tr><td colspan='5'>No sessions found.</td></tr>";
      }
      ?>
    </table>

    <div id="sessions-container">
      <!-- Sessions will be added here -->
    </div>

    <button type="button" onclick="addSession()">+ Add Session</button><br><br>
    <button type="submit">Submit Sessions</button>
  </form>

  <script>
    let sessionCount = 0;

    function addSession() {
      sessionCount++;
      const container = document.getElementById('sessions-container');

      const div = document.createElement('div');
      div.className = 'session-row';
      div.innerHTML = `
      <label>Date:</label>
      <input name="session[${sessionCount}][date]" required><br>

      <label>Time:</label>
      <input name="session[${sessionCount}][time]" required><br>
        <button type="button" class="remove-btn" onclick="this.parentNode.remove()">✖</button>
      `;
      container.appendChild(div);
    }

    function updateSession(sessionNum) {
        const row = document.querySelector(`tr[data-session='${sessionNum}']`);
        const date = row.querySelector('.editable-date').innerText.trim();
        const time = row.querySelector('.editable-time').innerText.trim();

        fetch('edit_session.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                student_id: '<?php echo $student_id; ?>',
                session: sessionNum,
                date: date,
                time: time
            })
        }).then(res => res.text())
          .then(alert)
          .catch(err => alert('Error updating: ' + err));
    }

    function deleteSession(sessionNum) {
        if (!confirm('Delete session ' + sessionNum + '?')) return;
        fetch('delete_session.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                student_id: '<?php echo $student_id; ?>',
                session: sessionNum
            })
        }).then(res => res.text())
          .then(msg => {
            alert(msg);
            location.reload();
          })
          .catch(err => alert('Error deleting: ' + err));
    }
  </script>
</body>
</html>
