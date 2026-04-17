<?php
session_start();
include "../config/db.php";

// Only allow admins
if ($_SESSION['role_type'] !== 'admin') {
    die("Access denied.");
}

// Handle certificate issuance
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $certificate_id = uniqid();
    $student_id = $_POST['student_id'];
    $track_id = $_POST['track_id'];
    $verification_code = strtoupper(bin2hex(random_bytes(8))); // secure random code

    try {
        $stmt = $pdo->prepare("INSERT INTO Certificates (certificate_id, user_id, track_id, issue_date, verification_code) 
                               VALUES (?, ?, ?, CURDATE(), ?)");
        $stmt->execute([$certificate_id, $student_id, $track_id, $verification_code]);
        $success = "Certificate issued successfully! Verification Code: $verification_code";
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch students
$studentStmt = $pdo->query("SELECT user_id, username FROM Users WHERE role_type='student'");
$students = $studentStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch tracks
$trackStmt = $pdo->query("SELECT track_id, title FROM LearningTracks");
$tracks = $trackStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Issue Certificate</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <div class="glass-container">
        <h2>Issue Certificate</h2>
        <p class="subtitle">Select student and track</p>

        <?php if (!empty($success))
            echo "<p class='success'>$success</p>"; ?>
        <?php if (!empty($error))
            echo "<p class='error'>$error</p>"; ?>

        <form method="post" class="form-box">
            <label>Student</label>
            <select name="student_id" required>
                <?php foreach ($students as $s): ?>
                    <option value="<?php echo $s['user_id']; ?>">
                        <?php echo htmlspecialchars($s['username']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Track</label>
            <select name="track_id" required>
                <?php foreach ($tracks as $t): ?>
                    <option value="<?php echo $t['track_id']; ?>">
                        <?php echo htmlspecialchars($t['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Issue Certificate</button>
        </form>

        <p><a href="../dashboards/admin_dashboard.php">Back to Dashboard</a></p>
    </div>
</body>

</html>