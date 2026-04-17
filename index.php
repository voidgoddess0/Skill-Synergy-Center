<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_type'])) {
    // Not logged in → redirect to login
    header("Location: auth/login.php");
    exit;
}

switch ($_SESSION['role_type']) {
    case 'student':
        header("Location: dashboards/student_dashboard.php");
        break;
    case 'instructor':
        header("Location: dashboards/instructor_dashboard.php");
        break;
    case 'admin':
        header("Location: dashboards/admin_dashboard.php");
        break;
    default:
        // fallback if role_type is unexpected
        echo "Invalid role type.";
}
exit;
?>