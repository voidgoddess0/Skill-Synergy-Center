<?php
session_start();
include "../config/db.php";

// Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role_type'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. Fetch Student Profile (Case-sensitive check for table names per your schema)
// We explicitly fetch total_xp to display in the main banner
$profStmt = $pdo->prepare("SELECT total_xp, current_learning_goal FROM studentprofiles WHERE student_id = ?");
$profStmt->execute([$user_id]);
$profile = $profStmt->fetch(PDO::FETCH_ASSOC);

// If no profile exists yet, default to 0 XP and a placeholder goal
$display_xp = $profile['total_xp'] ?? 0;
$display_goal = !empty($profile['current_learning_goal']) ? $profile['current_learning_goal'] : 'Set New Objective';

// 2. Fetch Enrolled Courses with Progress
$enrollStmt = $pdo->prepare("
    SELECT c.course_id, c.title, c.difficulty_score, e.progress_percent, e.is_completed 
    FROM enrollments e 
    JOIN courses c ON e.course_id = c.course_id 
    WHERE e.user_id = ?
");
$enrollStmt->execute([$user_id]);
$courses = $enrollStmt->fetchAll(PDO::FETCH_ASSOC);

// 3. CTA Logic for the Banner
$activeCourse = null;
foreach ($courses as $c) {
    if (!$c['is_completed']) {
        $activeCourse = $c;
        break;
    }
}

$ctaText = $activeCourse ? "CONTINUE LEARNING" : "EXPLORE TRACKS";
$ctaLink = $activeCourse ? "../submissions/submit_work.php?course_id=" . $activeCourse['course_id'] : "../courses/marketplace.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SSC Bazaar | Student Dashboard</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            display: block;
            height: auto;
            padding-bottom: 50px;
            background-color: #0b0b0b;
        }

        .nav-bar {
            width: 100%;
            background: #111;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #222;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .main-content {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* Banner Inspired by image_4a7e1a.png */
        .pro-banner {
            background: linear-gradient(135deg, #111 0%, #161616 100%);
            border: 1px solid #00acee;
            border-radius: 12px;
            padding: 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 45px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 172, 238, 0.1);
        }

        .pro-banner::before {
            content: "PREMIUM";
            position: absolute;
            top: 15px;
            right: 15px;
            background: #00acee;
            color: #000;
            font-size: 10px;
            font-weight: 900;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .xp-stat {
            color: #00acee;
            font-family: monospace;
            font-weight: bold;
            font-size: 1.1em;
            letter-spacing: 1px;
        }

        .banner-text h2 {
            font-size: 2.2em;
            margin: 10px 0;
            letter-spacing: -1px;
        }

        .btn-premium {
            background: #00acee;
            color: #000;
            padding: 18px 35px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 900;
            transition: 0.3s;
            box-shadow: 0 4px 15px rgba(0, 172, 238, 0.3);
        }

        .btn-premium:hover {
            background: #00d4ff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 172, 238, 0.5);
        }

        /* Card Grid */
        .course-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 25px;
        }

        .course-card {
            background: #161616;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #222;
            transition: 0.3s;
        }

        .course-card:hover {
            border-color: #00acee;
            transform: translateY(-5px);
        }

        .progress-bar-bg {
            background: #222;
            height: 8px;
            border-radius: 4px;
            margin: 20px 0 10px 0;
            overflow: hidden;
        }

        .progress-bar-fill {
            background: #00acee;
            height: 100%;
            transition: width 0.5s ease;
        }

        .status-badge {
            font-size: 0.75em;
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <header class="nav-bar">
        <div style="font-weight:900; color:#00acee; font-size:1.2em;">BAZAAR META</div>
        <nav>
            <a href="../courses/marketplace.php"
                style="margin-right:25px; text-decoration:none; color:#aaa; font-size:0.9em;">ALL ITEMS</a>
            <a href="../submissions/submit_work.php"
                style="margin-right:25px; text-decoration:none; color:#aaa; font-size:0.9em;">SUBMISSIONS</a>
            <a href="../auth/logout.php" style="color:#ff6b6b; text-decoration:none; font-size:0.9em;">DISCONNECT</a>
        </nav>
    </header>

    <main class="main-content">
        <div class="pro-banner">
            <div class="banner-text">
                <div class="xp-stat">⚡ <?php echo number_format($display_xp); ?> TOTAL XP</div>
                <h2><?php echo htmlspecialchars($display_goal); ?></h2>
                <p style="color:#666; font-size:0.9em;">flip your current assignments into rewards.</p>
            </div>
            <div class="banner-action">
                <a href="<?php echo $ctaLink; ?>" class="btn-premium"><?php echo $ctaText; ?></a>
            </div>
        </div>

        <h3 style="margin-bottom:25px; font-weight:900; text-transform:uppercase; letter-spacing:1px;">Active Contracts
        </h3>

        <div class="course-grid">
            <?php foreach ($courses as $c): ?>
                <div class="course-card">
                    <div style="font-size:1.5em; margin-bottom:15px;">📘</div>
                    <div style="font-size:0.7em; color:#555; text-transform:uppercase;">Track Module</div>
                    <h4 style="margin:5px 0 15px 0; font-size:1.1em;"><?php echo htmlspecialchars($c['title']); ?></h4>
                    <div style="font-size:0.8em; color:#888;">Potential: <span
                            style="color:#00acee;"><?php echo $c['difficulty_score']; ?> XP</span></div>

                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: <?php echo $c['progress_percent']; ?>%;"></div>
                    </div>

                    <div class="status-badge"
                        style="background: <?php echo $c['is_completed'] ? 'rgba(0, 255, 136, 0.1)' : 'rgba(255, 255, 255, 0.05)'; ?>; color: <?php echo $c['is_completed'] ? '#00ff88' : '#888'; ?>;">
                        <?php echo $c['is_completed'] ? 'FULLY FLIPPED' : $c['progress_percent'] . '% ANALYSIS'; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>

</html>