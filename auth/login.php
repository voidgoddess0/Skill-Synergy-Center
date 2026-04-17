<?php
session_start();
include "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT user_id, username, password_hash, role_type FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role_type'] = $user['role_type'];

        // Redirect based on role
        header("Location: ../index.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SSC | Login</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <div class="glass-container">
        <h2>LOGIN</h2>
        <?php if (isset($error))
            echo "<p class='error'>$error</p>"; ?>
        <form method="post" class="form-box">
            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>
        <p style="margin-top:15px;"><a href="register.php">Create an account</a></p>
    </div>
</body>

</html>