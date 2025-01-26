<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../files/css/core.css">
    <link rel="stylesheet" href="../../files/css/auth/forgot/reset.css">
    <script type="module" src="https://kit.fontawesome.com/7159432989.js"></script>
    <script src="../../files/js/etc/button_handle.js"></script>
    <script src="../../files/js/index/tooltip.js"></script>
    <title>Fiók visszaszerzése - AnimeMate</title>
</head>
<body>
<nav class="navbar">
        <div class="navbar-left">
            <a href="../../index.php" class="logo">AnimeMate</a>
        </div>
        <div class="navbar-right">
            <a href="../../index.php" class="login-button">Főoldal</a>
        </div>
    </nav>
    <div class="container">
        <div class="box">
            <h2>Visszatérés AnimeMate-fiókodhoz</h2>
            <p class="info-p">Adj meg némi információt erről a fiókról.</p>
            <form>
                <div class="form-group">
                    <div class="email-thing">
                        <label for="username">Írd be az e-mail-címedet</label>
                        <i class="fa-solid fa-circle-info"></i>
                        <div class="tooltip" id="tooltip">Kérlek írd be az email címedet amire majd egy kódot küldünk ki.</div>
                    </div>
                    <input type="email" id="email">
                </div>
                <div class="buttons">
                <button type="submit" class="submit-btn">Kód küldése</button>
                </div>
            </form>
            <div class="divider-container">
                <div class="divider">
                    <span>vagy</span>
                </div>
            </div>
        <button class="reg-button" id="reg-button">Nincs még fiókod? Regisztráció</button>
        </div>
    </div>
</body>
</html>