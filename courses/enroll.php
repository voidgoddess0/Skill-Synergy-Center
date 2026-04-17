<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: marketplace.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$course_id = $_GET['id'];
$enroll_id = uniqid("enr_");

try {
    // Check if already enrolled to prevent primary key errors
    $check = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
    $check->execute([$user_id, $course_id]);

    if ($check->rowCount() == 0) {
        $stmt = $pdo->prepare("INSERT INTO enrollments (enrollment_id, user_id, course_id, progress_percent, is_completed) VALUES (?, ?, ?, 0, 0)");
        $stmt->execute([$enroll_id, $user_id, $course_id]);
    }

    // Proactive: Redirect straight to the dashboard so they see their new contract immediately
    header("Location: ../dashboards/student_dashboard.php");
    exit;

} catch (Exception $e) {
    die("The Bazaar refuses this transaction: " . $e->getMessage());
}