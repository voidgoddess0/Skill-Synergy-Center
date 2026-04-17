<?php
session_start();
include "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];

    try {
        $pdo->beginTransaction();

        // Insert into Users
        $stmt = $pdo->prepare("INSERT INTO Users (username, email, password_hash, role_type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password, $role]);
        $new_user_id = $pdo->lastInsertId();

        // Initialize role-specific profile
        if ($role === 'student') {
            $pdo->prepare("INSERT INTO StudentProfiles (student_id, total_xp, current_learning_goal) VALUES (?, 0, '')")
                ->execute([$new_user_id]);
        } elseif ($role === 'instructor') {
            $pdo->prepare("INSERT INTO InstructorProfiles (instructor_id, expertise_tags, total_earnings, average_rating) VALUES (?, '', 0.00, 0)")
                ->execute([$new_user_id]);
        }

        $pdo->commit();
        header("Location: login.php?registered=true");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SSC | Create Account</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <div class="glass-container">
        <h2>CREATE ACCOUNT</h2>
        <?php if (isset($error))
            echo "<p class='error'>$error</p>"; ?>
        <form method="post" class="form-box">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <label>Role</label>
            <select name="role" required>
                <option value="student">Student</option>
                <option value="instructor">Instructor</option>
            </select>

            <button type="submit">Register</button>
        </form>
        <p style="margin-top:15px;"><a href="login.php">Already have an account? Login</a></p>
    </div>
</body>

</html>