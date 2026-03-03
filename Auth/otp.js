/**
 * UIU TalentHUB - OTP Verification Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    const otpInputs = document.querySelectorAll('.otp-inputs input');
    const timerElement = document.getElementById('timer');
    const resendBtn = document.getElementById('resendBtn');

    let timeLeft = 59;

    // Handle OTP Input focusing
    otpInputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            if (e.target.value.length === 1 && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                otpInputs[index - 1].focus();
            }
        });
    });

    // Simple timer logic
    if (timerElement && resendBtn) {
        const countdown = setInterval(() => {
            timeLeft--;
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerElement.textContent = `0${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

            if (timeLeft <= 0) {
                clearInterval(countdown);
                resendBtn.disabled = false;
                timerElement.parentElement.style.display = 'none';
            }
        }, 1000);
    }

    // Global focus for first input
    if (otpInputs[0]) otpInputs[0].focus();
});

function resendOtp() {
    alert('A new OTP has been sent to your UIU email!');
    window.location.reload();
}

function verifyAndJoin() {
    // Collect OTP
    const otp = Array.from(document.querySelectorAll('.otp-inputs input'))
        .map(input => input.value)
        .join('');

    if (otp.length < 6) {
        alert('Please enter the full 6-digit code.');
        return;
    }

    // Transition to main site
    window.location.href = '../Trending Page/index.html';
}
