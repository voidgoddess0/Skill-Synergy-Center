<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role_type'] !== 'instructor') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Error: No submission selected for review.");
}

$sub_id = $_GET['id'];

// Fetch Submission and Lesson details
$stmt = $pdo->prepare("
    SELECT s.*, u.username, l.title as lesson_title 
    FROM submissions s
    JOIN users u ON s.student_id = u.user_id
    JOIN lessons l ON s.lesson_id = l.lesson_id
    WHERE s.submission_id = ?
");
$stmt->execute([$sub_id]);
$sub = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sub)
    die("Submission not found.");

$work_url = htmlspecialchars($sub['work_url']);
$file_ext = strtolower(pathinfo($work_url, PATHINFO_EXTENSION));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SSC | Reviewing: <?php echo htmlspecialchars($sub['username']); ?></title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            display: block;
            padding: 40px 0;
            background: #0b0b0b;
        }

        .review-container {
            max-width: 900px;
            margin: 0 auto;
            background: #161616;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #222;
        }

        /* Artifact Display Box */
        .artifact-viewer {
            width: 100%;
            min-height: 500px;
            background: #000;
            border: 1px solid #333;
            border-radius: 8px;
            margin: 20px 0;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        iframe {
            width: 100%;
            height: 600px;
            border: none;
        }

        .img-preview {
            max-width: 100%;
            max-height: 600px;
            object-fit: contain;
        }

        .form-group {
            margin-top: 30px;
            border-top: 1px solid #222;
            padding-top: 20px;
        }

        textarea {
            width: 100%;
            height: 120px;
            background: #222;
            border: 1px solid #333;
            color: #fff;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            box-sizing: border-box;
        }

        .btn-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 20px;
        }

        .btn {
            padding: 15px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <div class="review-container">
        <h2 style="color: #00acee;">Review: <?php echo htmlspecialchars($sub['lesson_title']); ?></h2>
        <p style="color: #555;">Artifact by student: <strong><?php echo htmlspecialchars($sub['username']); ?></strong>
        </p>

        <div class="artifact-viewer">
            <?php if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                <img src="<?php echo $work_url; ?>" class="img-preview" alt="Student Work">
            <?php else: ?>
                <iframe src="<?php echo $work_url; ?>" title="Artifact Preview"></iframe>
            <?php endif; ?>
        </div>

        <form method="post" action="process_review.php">
            <input type="hidden" name="submission_id" value="<?php echo $sub_id; ?>">

            <div class="form-group">
                <label style="color: #888; font-size: 0.8em; text-transform: uppercase;">Reviewer Feedback</label>
                <textarea name="feedback_text" placeholder="Explain your decision to the student..."
                    required></textarea>
            </div>

            <div class="btn-grid">
                <button type="submit" name="status" value="reviewed" class="btn"
                    style="background: #00ff88; color: #000;">APPROVE ARTIFACT</button>
                <button type="submit" name="status" value="rejected" class="btn"
                    style="background: #ff4444; color: #fff;">REJECT WORK</button>
            </div>
        </form>

        <p style="text-align: center; margin-top: 20px;">
            <a href="../dashboards/instructor_dashboard.php"
                style="color: #555; text-decoration: none; font-size: 0.8em;">← Return to Command Center</a>
        </p>
    </div>

</body>

</html>