<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role_type'] !== 'instructor') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch Instructor Profile
$profStmt = $pdo->prepare("SELECT expertise_tags, total_earnings, average_rating FROM InstructorProfiles WHERE instructor_id = ?");
$profStmt->execute([$user_id]);
$profile = $profStmt->fetch(PDO::FETCH_ASSOC);

// Fetch Real Pending Submissions
$subStmt = $pdo->prepare("
    SELECT s.submission_id, s.work_url, u.username as student_name, l.title as lesson_title
    FROM Submissions s
    JOIN Lessons l ON s.lesson_id = l.lesson_id
    JOIN Courses c ON l.course_id = c.course_id
    JOIN Users u ON s.student_id = u.user_id
    WHERE c.instructor_id = ? AND s.status_type = 'pending'
");
$subStmt->execute([$user_id]);
$pending_work = $subStmt->fetchAll(PDO::FETCH_ASSOC);

// MOCK DATA: Inject a test row if DB is empty so you can test the 'VALIDATE' button
if (empty($pending_work)) {
    $pending_work = [
        [
            'submission_id' => 'test_999',
            'student_name' => 'Demo Student',
            'lesson_title' => 'Introduction to Flipping',
            'work_url' => 'https://github.com'
        ]
    ];
}

$courseStmt = $pdo->prepare("SELECT title, difficulty_score, price FROM Courses WHERE instructor_id = ?");
$courseStmt->execute([$user_id]);
$my_courses = $courseStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SSC Bazaar | Instructor Console</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            display: block;
            height: auto;
            padding-bottom: 50px;
            justify-content: start;
            align-items: start;
        }

        .nav-bar {
            width: 100%;
            background: rgba(22, 22, 22, 0.9);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            box-sizing: border-box;
            border-bottom: 1px solid #333;
        }

        .main-content {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: #161616;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #333;
        }

        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #00acee;
            margin-top: 10px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .data-table td,
        .data-table th {
            padding: 15px 10px;
            border-bottom: 1px solid #1a1a1a;
        }

        .action-link {
            color: #00acee;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <header class="nav-bar">
        <div style="font-weight:bold;">SSC BAZAAR | INSTRUCTOR</div>
        <nav>
            <a href="../courses/create_course.php" style="margin-right:20px;">+ New Course</a>
            <a href="../auth/logout.php" style="color:#ff6b6b;">Logout</a>
        </nav>
    </header>
    <div class="main-content">
        <div class="stats-row">
            <div class="stat-card">
                <div style="color:#888; font-size:0.8em;">EARNINGS</div>
                <div class="stat-value">$<?php echo number_format($profile['total_earnings'], 2); ?></div>
            </div>
            <div class="stat-card">
                <div style="color:#888; font-size:0.8em;">RATING</div>
                <div class="stat-value">⭐ <?php echo $profile['average_rating']; ?></div>
            </div>
        </div>
        <h3>Pending Reviews</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Lesson</th>
                    <th>Artifact</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_work as $work): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($work['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($work['lesson_title']); ?></td>
                        <td><a href="<?php echo htmlspecialchars($work['work_url']); ?>" target="_blank"
                                style="color:#666;">View Link</a></td>
                        <td><a href="../submissions/review_work.php?id=<?php echo $work['submission_id']; ?>"
                                class="action-link">VALIDATE</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>