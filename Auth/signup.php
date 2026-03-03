<?php
/**
 * UIU TalentHUB - Signup Processing
 */
require_once '../includes/db_connect.php';

$error = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['name']);
    $student_id = trim($_POST['studentId']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 1. Basic Validation
    if (empty($full_name) || empty($student_id) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with(strtolower($email), '.uiu.ac.bd')) {
        $error = "Please use your official UIU email address (@uiu.ac.bd).";
    } else {
        // 2. Check if user already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR student_id = ?");
        $stmt->execute([$email, $student_id]);
        if ($stmt->fetch()) {
            $error = "An account with this email or Student ID already exists.";
        } else {
            // 3. Hash Password and Create OTP
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $otp_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

            // 4. Insert into Database
            try {
                $stmt = $pdo->prepare("INSERT INTO users (full_name, student_id, email, password_hash, otp_code) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$full_name, $student_id, $email, $password_hash, $otp_code]);

                // 5. Send OTP Email
                require_once '../includes/send_email.php';
                $email_sent = sendOTPEmail($email, $full_name, $otp_code);

                if ($email_sent) {
                    // Success! Redirect to OTP verification page
                    header("Location: otp.php?email=" . urlencode($email));
                    exit();
                } else {
                    // Email failed but account created - still allow OTP entry
                    header("Location: otp.php?email=" . urlencode($email) . "&email_error=1");
                    exit();
                }
            } catch (PDOException $e) {
                $error = "Registration failed. Please try again later.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - UIU TalentHUB</title>
    <link rel="stylesheet" href="auth.css">
</head>

<body>
    <div class="auth-container fade-in">
        <div class="brand">
            <img src="../assets/images/UIUTELENTHUBLOGO.png" alt="Logo">
            <h1>UIU TalentHUB</h1>
            <p>Showcase Your Genius</p>
        </div>

        <?php if ($error): ?>
            <div
                style="color: #ff3b3b; background: rgba(255, 59, 59, 0.1); padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 13px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form id="signupForm" action="signup.php" method="POST">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Enter your full name"
                    value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="studentId">UIU ID</label>
                <input type="text" id="studentId" name="studentId" placeholder="e.g. 011 201 000"
                    value="<?php echo isset($_POST['studentId']) ? htmlspecialchars($_POST['studentId']) : ''; ?>"
                    required>
            </div>

            <div class="form-group">
                <label for="email">UIU Email</label>
                <input type="email" id="email" name="email" placeholder="example@bscse.uiu.ac.bd"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a strong password" required>
            </div>

            <button type="submit" class="auth-btn">Create Account</button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="login.php">Sign In</a>
        </div>
    </div>

    <script src="signup.js"></script>
</body>

</html>