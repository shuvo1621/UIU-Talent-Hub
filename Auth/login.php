<?php
/**
 * UIU TalentHUB - Login Processing
 */
require_once '../includes/db_connect.php';
session_start();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_email = trim($_POST['id_email']);
    $password = $_POST['password'];

    if (empty($id_email) || empty($password)) {
        $error = "Please enter both ID/Email and Password.";
    } else {
        // Search by email or student ID
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR student_id = ?");
        $stmt->execute([$id_email, $id_email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['is_verified'] == 0) {
                header("Location: otp.php?email=" . urlencode($user['email']));
                exit();
            } else {
                // Set Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['student_id'] = $user['student_id'];
                header("Location: ../index.php");
                exit();
            }
        } else {
            $error = "Invalid Email/ID or Password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - UIU TalentHUB</title>
    <link rel="stylesheet" href="auth.css">
</head>

<body>
    <div class="auth-container fade-in">
        <div class="brand">
            <img src="../assets/images/UIUTELENTHUBLOGO.png" alt="Logo">
            <h1>Welcome Back</h1>
            <p>UIU TalentHUB Login</p>
        </div>

        <?php if ($error): ?>
            <div
                style="color: #ff3b3b; background: rgba(255, 59, 59, 0.1); padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 13px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="id_email">UIU ID or Email</label>
                <input type="text" name="id_email" id="id_email" placeholder="Student ID or official email"
                    value="<?php echo isset($_POST['id_email']) ? htmlspecialchars($_POST['id_email']) : ''; ?>"
                    required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Enter your password" required>
                <a href="#" class="forgot-link">Forgot Password?</a>
            </div>

            <button type="submit" class="auth-btn">Sign In</button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="signup.php">Join Now</a>
        </div>
    </div>
</body>

</html>