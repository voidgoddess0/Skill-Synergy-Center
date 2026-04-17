<?php
session_start();
include "../config/db.php";

// Access Control: Verify the reviewer is an instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role_type'] !== 'instructor') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sub_id = $_POST['submission_id'];
    $status = $_POST['status']; // 'reviewed' or 'rejected'
    $feedback = $_POST['feedback_text'];
    $reviewer_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // 1. Update Submission Status
        $updateSub = $pdo->prepare("UPDATE submissions SET status_type = ? WHERE submission_id = ?");
        $updateSub->execute([$status, $sub_id]);

        // 2. Insert Review Record
        $review_id = uniqid("rev_");
        $insertReview = $pdo->prepare("INSERT INTO reviews (review_id, submission_id, reviewer_id, feedback_text) VALUES (?, ?, ?, ?)");
        $insertReview->execute([$review_id, $sub_id, $reviewer_id, $feedback]);

        // 3. Award XP and Close Enrollment if Approved
        if ($status === 'reviewed') {
            // Retrieve Student ID, Course ID, and XP Value
            $dataStmt = $pdo->prepare("
                SELECT s.student_id, c.course_id, c.difficulty_score 
                FROM submissions s
                JOIN lessons l ON s.lesson_id = l.lesson_id
                JOIN courses c ON l.course_id = c.course_id
                WHERE s.submission_id = ?
            ");
            $dataStmt->execute([$sub_id]);
            $info = $dataStmt->fetch(PDO::FETCH_ASSOC);

            if ($info) {
                $student_id = $info['student_id'];
                $course_id = $info['course_id'];
                $xp_value = $info['difficulty_score'];

                // 3a. Update Student XP Profile
                $updateProfile = $pdo->prepare("UPDATE studentprofiles SET total_xp = total_xp + ? WHERE student_id = ?");
                $updateProfile->execute([$xp_value, $student_id]);

                // 3b. Flip Enrollment to Completed
                $updateEnroll = $pdo->prepare("
                    UPDATE enrollments 
                    SET progress_percent = 100, is_completed = 1 
                    WHERE user_id = ? AND course_id = ?
                ");
                $updateEnroll->execute([$student_id, $course_id]);

                // 3c. Generate Certificate
                $cert_id = uniqid("cert_");
                $v_code = strtoupper(substr(md5($cert_id), 0, 10)); // Simple verification code
                $insertCert = $pdo->prepare("
                    INSERT INTO certificates (certificate_id, user_id, track_id, issue_date, verification_code) 
                    VALUES (?, ?, ?, CURDATE(), ?)
                ");
                $insertCert->execute([$cert_id, $student_id, $course_id, $v_code]);
            }
        }

        $pdo->commit();
        header("Location: ../dashboards/instructor_dashboard.php?msg=Process+Complete");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("The Bazaar rejects this transaction: " . $e->getMessage());
    }
} else {
    header("Location: ../dashboards/instructor_dashboard.php");
    exit;
}