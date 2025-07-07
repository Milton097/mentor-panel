<?php include('session.php'); ?>
<!DOCTYPE html>
<html>
<head>
  <title>Mentor Assignment</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <h2>Mentor Assignment Form</h2>
  <form method="POST" action="submit_mentor.php">
    <label>Student Name:</label><input name="student_name" required><br>
    <label>Student ID:</label><input name="student_id" required><br>
    <label>Student Email:</label><input name="student_email" type="email" required><br>

    <label>Mentor Name:</label><input name="mentor_name" required><br>
    <label>Mentor ID:</label><input name="mentor_id" required><br>
    <label>Mentor Email:</label><input name="mentor_email" type="email" required><br>

    <label>GMeet Link:</label><input name="gmeet_link" type="url" required><br>

    <button type="submit">Continue to Session Entry ➜</button>
  </form>
</body>
</html>
