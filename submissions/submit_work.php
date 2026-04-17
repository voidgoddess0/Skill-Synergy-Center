<?php
session_start();
include "../config/db.php";

// Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role_type'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = "";

// Handle Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lesson_id = $_POST['lesson_id'];
    $work_url = $_POST['work_url'];

    try {
        $stmt = $pdo->prepare("INSERT INTO Submissions (lesson_id, student_id, work_url, status_type) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$lesson_id, $user_id, $work_url]);
        $msg = "Contract submitted for review.";
    } catch (Exception $e) {
        $msg = "Error: " . $e->getMessage();
    }
}

// Fetch Lessons from enrolled courses to populate the dropdown
$lessonsStmt = $pdo->prepare("
    SELECT l.lesson_id, l.title as lesson_title, c.title as course_title 
    FROM Lessons l
    JOIN Courses c ON l.course_id = c.course_id
    JOIN Enrollments e ON c.course_id = e.course_id
    WHERE e.user_id = ? AND e.is_completed = 0
");
$lessonsStmt->execute([$user_id]);
$available_lessons = $lessonsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SSC Bazaar | Submit Work</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            display: block;
            height: auto;
            padding: 40px;
        }

        .submit-container {
            max-width: 600px;
            margin: 0 auto;
            background: #161616;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #333;
        }

        .msg {
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.9em;
            text-align: center;
        }

        .success {
            background: rgba(0, 255, 136, 0.1);
            color: #00ff88;
        }

        select,
        input {
            width: 100%;
            box-sizing: border-box;
        }
    </style>
</head>

<body>
    <div class="submit-container">
        <h2>Submit Contract Work</h2>
        <p style="color: #888; font-size: 0.9em; margin-bottom: 25px;">Provide the link to your completed artifact for
            instructor validation.</p>

        <?php if ($msg): ?>
            <div class="msg <?php echo strpos($msg, 'Error') === false ? 'success' : 'error'; ?>">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form method="post" class="form-box">
            <label>Select Lesson/Assignment</label>
            <select name="lesson_id" required>
                <option value="">-- Choose Target --</option>
                <?php foreach ($available_lessons as $l): ?>
                    <option value="<?php echo $l['lesson_id']; ?>">
                        [<?php echo htmlspecialchars($l['course_title']); ?>]
                        <?php echo htmlspecialchars($l['lesson_title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Work URL (GitHub / Drive / Figma)</label>
            <input type="url" name="work_url" placeholder="https://..." required>

            <button type="submit">SUBMIT FOR REVIEW</button>
        </form>

        <p style="margin-top: 30px;"><a href="../dashboards/student_dashboard.php">← Back to Dashboard</a></p>
    </div>
</body>

</html>