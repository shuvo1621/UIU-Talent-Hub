<?php
/**
 * UIU TalentHUB - OTP Verification Logic
 */
require_once '../includes/db_connect.php';
session_start();

$email = isset($_GET['email']) ? $_GET['email'] : '';
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect 6-digit OTP from the inputs
    $entered_otp = $_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'] . $_POST['otp5'] . $_POST['otp6'];
    $email = $_POST['email'];

    if (strlen($entered_otp) < 6) {
        $error = "Please enter the full 6-digit code.";
    } else {
        $stmt = $pdo->prepare("SELECT id, otp_code, full_name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && $user['otp_code'] === $entered_otp) {
            // Success! Verify user
            $update = $pdo->prepare("UPDATE users SET is_verified = 1, otp_code = NULL WHERE id = ?");
            $update->execute([$user['id']]);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];

            header("Location: ../index.php");
            exit();
        } else {
            $error = "Incorrect OTP code. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - UIU TalentHUB</title>
    <link rel="stylesheet" href="auth.css">
</head>

<body>
    <div class="auth-container fade-in">
        <div class="brand">
            <img src="../assets/images/UIUTELENTHUBLOGO.png" alt="Logo">
            <h1>Verify OTP</h1>
            <p>Check your UIU Email</p>
        </div>

        <p class="auth-instruction">
            We've sent a 6-digit code to <strong><?php echo htmlspecialchars($email); ?></strong>.
        </p>

        <?php if ($error): ?>
            <div
                style="color: #ff3b3b; background: rgba(255, 59, 59, 0.1); padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 13px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['email_error'])): ?>
            <div
                style="color: #ff8c00; background: rgba(255, 140, 0, 0.1); padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 13px;">
                ⚠️ Email sending failed, but your account was created. You can still enter your OTP or request a resend.
            </div>
        <?php endif; ?>

        <form action="otp.php" method="POST" id="otpForm">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <div class="otp-inputs">
                <input type="text" name="otp1" maxlength="1" id="opt1" autocomplete="off">
                <input type="text" name="otp2" maxlength="1" id="opt2" autocomplete="off">
                <input type="text" name="otp3" maxlength="1" id="opt3" autocomplete="off">
                <input type="text" name="otp4" maxlength="1" id="opt4" autocomplete="off">
                <input type="text" name="otp5" maxlength="1" id="opt5" autocomplete="off">
                <input type="text" name="otp6" maxlength="1" id="opt6" autocomplete="off">
            </div>

            <div class="otp-timer">
                Resend code in <span id="timer">00:59</span>
            </div>

            <button type="button" class="resend-btn" id="resendBtn" disabled onclick="resendOtp()">Resend OTP</button>
            <button type="submit" class="auth-btn btn-verify">Verify & Join</button>
        </form>

        <div class="auth-footer">
            Incorrect email? <a href="signup.php">Go Back</a>
        </div>
    </div>

    <script src="otp.js"></script>
</body>

</html>