<?php
session_start();
include "../config/db.php";

// Only allow instructors
if ($_SESSION['role_type'] !== 'instructor') {
    die("Access denied.");
}

$course_id = $_GET['course_id'] ?? null;
if (!$course_id) {
    die("Missing course ID.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lesson_id = uniqid();
    $title = $_POST['title'];
    $content_url = $_POST['content_url'];
    $is_assignment = isset($_POST['is_assignment']) ? 1 : 0;

    try {
        $stmt = $pdo->prepare("INSERT INTO Lessons (lesson_id, course_id, title, content_url, is_assignment) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$lesson_id, $course_id, $title, $content_url, $is_assignment]);
        $success = "Lesson added successfully!";
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch course info
$courseStmt = $pdo->prepare("SELECT title FROM Courses WHERE course_id=?");
$courseStmt->execute([$course_id]);
$course = $courseStmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Lesson</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <div class="glass-container">
        <h2>Add Lesson to <?php echo htmlspecialchars($course['title']); ?></h2>
        <p class="subtitle">Provide lesson details</p>

        <?php if (!empty($success))
            echo "<p class='success'>$success</p>"; ?>
        <?php if (!empty($error))
            echo "<p class='error'>$error</p>"; ?>

        <form method="post" class="form-box">
            <label>Lesson Title</label>
            <input type="text" name="title" required>

            <label>Content URL</label>
            <input type="url" name="content_url" placeholder="https://link-to-content.com" required>

            <label>
                <input type="checkbox" name="is_assignment"> Is Assignment?
            </label>

            <button type="submit">Add Lesson</button>
        </form>

        <p><a href="../dashboards/instructor_dashboard.php">Back to Dashboard</a></p>
    </div>
</body>

</html>