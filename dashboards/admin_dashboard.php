<?php
session_start();
include "../config/db.php";

// Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role_type'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// 1. Fetch Global Stats
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$courseCount = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$pendingSubs = $pdo->query("SELECT COUNT(*) FROM submissions WHERE status_type = 'pending'")->fetchColumn();

// 2. Fetch Recent Users
$usersStmt = $pdo->query("SELECT username, email, role_type, created_at FROM users ORDER BY created_at DESC LIMIT 5");
$recent_users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Fetch Flagged or Latest Submissions
$subStmt = $pdo->query("
    SELECT s.submission_id, u.username, c.title as course_title, s.status_type 
    FROM submissions s
    JOIN users u ON s.student_id = u.user_id
    JOIN lessons l ON s.lesson_id = l.lesson_id
    JOIN courses c ON l.course_id = c.course_id
    ORDER BY s.submission_id DESC LIMIT 5
");
$recent_subs = $subStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SSC Bazaar | Admin Overseer</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            display: block;
            height: auto;
            padding: 40px 0;
            justify-content: start;
            align-items: start;
        }

        .admin-grid {
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .card {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #333;
        }

        .stat {
            font-size: 2em;
            font-weight: bold;
            color: #4a90e2;
        }

        .table-container {
            max-width: 1000px;
            margin: 40px auto;
            background: #111;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #222;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.9em;
        }

        th {
            color: #555;
            padding: 10px;
            border-bottom: 1px solid #333;
        }

        td {
            padding: 12px 10px;
            border-bottom: 1px solid #1a1a1a;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }

        .badge-student {
            background: #222;
            color: #aaa;
        }

        .badge-instructor {
            background: rgba(74, 144, 226, 0.2);
            color: #4a90e2;
        }
    </style>
</head>

<body>
    <div class="admin-grid">
        <div class="card">
            <div style="font-size: 0.8em; color: #888;">TOTAL USERS</div>
            <div class="stat"><?php echo $userCount; ?></div>
        </div>
        <div class="card">
            <div style="font-size: 0.8em; color: #888;">ACTIVE TRACKS</div>
            <div class="stat"><?php echo $courseCount; ?></div>
        </div>
        <div class="card">
            <div style="font-size: 0.8em; color: #888;">PENDING REVIEWS</div>
            <div class="stat"><?php echo $pendingSubs; ?></div>
        </div>
    </div>

    <div class="table-container">
        <h3>Recent Citizen Registration</h3>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_users as $u): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><span
                                class="badge badge-<?php echo $u['role_type']; ?>"><?php echo strtoupper($u['role_type']); ?></span>
                        </td>
                        <td style="color:#555;"><?php echo date("Y-m-d", strtotime($u['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="table-container">
        <h3>Latest Submissions</h3>
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Track</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_subs as $s): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($s['username']); ?></td>
                        <td><?php echo htmlspecialchars($s['course_title']); ?></td>
                        <td style="color: <?php echo $s['status_type'] == 'pending' ? '#ffcc00' : '#00ff88'; ?>">
                            <?php echo strtoupper($s['status_type']); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p style="text-align:center;"><a href="../auth/logout.php" style="color:#ff6b6b;">Logout</a></p>
</body>

</html>