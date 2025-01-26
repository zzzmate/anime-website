document.addEventListener('DOMContentLoaded', () => {
    const regBUttonOnLogin = document.getElementById('reg-button');
    const logBUttonOnRegister = document.getElementById('log-button');
    const BackButtonOnProfile = document.getElementById('back-button');

    if (regBUttonOnLogin) {
        regBUttonOnLogin.addEventListener('click', () => {
            window.location.href = '../register/register.php';
        });
    }

    if (logBUttonOnRegister) {
        logBUttonOnRegister.addEventListener('click', () => {
            window.location.href = '../login/login.php';
        });
    }

    if (BackButtonOnProfile) {
        BackButtonOnProfile.addEventListener('click', () => {
            window.location.href = 'profile.php';
        });
    }
});
