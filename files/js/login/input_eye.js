document.addEventListener('DOMContentLoaded', () => {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            const icon = type === 'password' ? 'fa-eye' : 'fa-eye-slash';
            togglePassword.classList.remove('fa-eye', 'fa-eye-slash');
            togglePassword.classList.add(icon);
        });
    }
});
