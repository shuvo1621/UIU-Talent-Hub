<?php
/**
 * Resend OTP Email Handler
 */
require_once '../includes/db_connect.php';
require_once '../includes/send_email.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        exit;
    }

    // Get user details
    $stmt = $pdo->prepare("SELECT id, full_name, otp_code FROM users WHERE email = ? AND is_verified = 0");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found or already verified']);
        exit;
    }

    // Generate new OTP
    $new_otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

    // Update OTP in database
    $update = $pdo->prepare("UPDATE users SET otp_code = ? WHERE id = ?");
    $update->execute([$new_otp, $user['id']]);

    // Send email
    $email_sent = sendOTPEmail($email, $user['full_name'], $new_otp);

    if ($email_sent) {
        echo json_encode(['success' => true, 'message' => 'OTP resent successfully! Check your email.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send email. Please try again.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
