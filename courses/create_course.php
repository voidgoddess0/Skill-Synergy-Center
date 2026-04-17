<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role_type'] !== 'instructor') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$status_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = uniqid(); // Database uses varchar(36)
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $difficulty = $_POST['difficulty_score'];

    $lesson_title = $_POST['lesson_title'];
    $lesson_url = $_POST['lesson_url']; // Mapping to content_url in schema

    try {
        $pdo->beginTransaction();

        // 1. Insert Course into 'courses' table
        $stmtCourse = $pdo->prepare("INSERT INTO courses (course_id, instructor_id, title, description, difficulty_score, price) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtCourse->execute([$course_id, $user_id, $title, $desc, $difficulty, $price]);

        // 2. Insert First Lesson into 'lessons' table
        $lesson_id = uniqid();
        $stmtLesson = $pdo->prepare("INSERT INTO lessons (lesson_id, course_id, title, content_url, is_assignment) VALUES (?, ?, ?, ?, 1)");
        $stmtLesson->execute([$lesson_id, $course_id, $lesson_title, $lesson_url]);

        $pdo->commit();
        $status_msg = "Success: Track forged according to sacred blueprints.";
        $_POST = array(); // Clear form
    } catch (Exception $e) {
        $pdo->rollBack();
        $status_msg = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SSC | Forge Track</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            display: block;
            height: auto;
            padding: 40px 0;
        }

        .forge-card {
            max-width: 650px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.05);
            padding: 35px;
            border-radius: 12px;
            border: 1px solid #333;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .divider {
            height: 1px;
            background: #333;
            margin: 25px 0;
        }
    </style>
</head>

<body>
    <div class="forge-card">
        <h2>FORGE TRACK</h2>
        <?php if ($status_msg)
            echo "<p style='color:#4a90e2; font-size:0.9em;'>$status_msg</p>"; ?>

        <form method="post" class="form-box">
            <label style="font-size:0.8em; color:#aaa;">COURSE DETAILS</label>
            <input type="text" name="title" placeholder="Track Title" required>
            <textarea name="description" placeholder="Description"
                style="background:rgba(255,255,255,0.1); border:none; padding:10px; color:#fff; border-radius:6px; min-height:80px;"></textarea>

            <div class="grid">
                <input type="number" step="0.01" name="price" placeholder="Price (0.00)" required>
                <input type="number" name="difficulty_score" placeholder="XP Difficulty" required>
            </div>

            <div class="divider"></div>
            <label style="font-size:0.8em; color:#aaa;">INITIAL LESSON (ASSIGNMENT)</label>
            <input type="text" name="lesson_title" placeholder="Lesson/Task Title" required>
            <input type="text" name="lesson_url" placeholder="Resource/Instruction URL" required>

            <button type="submit" style="margin-top:10px;">PUBLISH TO BAZAAR</button>
        </form>
        <p style="margin-top:20px; font-size:0.8em;"><a href="../dashboards/instructor_dashboard.php"
                style="color:#aaa;">← Return to Console</a></p>
    </div>
</body>

</html>