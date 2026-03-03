/**
 * UIU TalentHUB - Signup Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    const signupForm = document.getElementById('signupForm');
    const emailInput = document.getElementById('email');

    if (signupForm) {
        signupForm.addEventListener('submit', (e) => {
            const email = emailInput.value.trim();

            // Simple validation for official UIU email
            if (!email.toLowerCase().endsWith('.uiu.ac.bd')) {
                alert('Please use your official UIU email address (e.g. @bscse.uiu.ac.bd)');
                e.preventDefault();
                return;
            }

            // In a real app, you would handle the signup data here
            console.log('Signup form submitted to OTP stage');
        });
    }
});
