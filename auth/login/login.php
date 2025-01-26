<?php
require('../../backend/connection.php');
session_start();
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

if($isLoggedIn) 
{
    header("Location: ../../index.php");
}

$inputUsername = $inputPassword = "";
$errorMessage = "";
$notificationType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inputUsername = htmlspecialchars(trim($_POST["username"]));
    $inputPassword = htmlspecialchars(trim($_POST["password"]));

    if (empty($inputUsername) || empty($inputPassword)) {
        $errorMessage = "Minden mező kitöltése kötelező!";
        $notificationType = "error";
    } else {
        $checkUserQuery = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($checkUserQuery);
        $stmt->bind_param("s", $inputUsername);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($inputPassword, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['logged_in'] = true;
                $_SESSION['just_logged_in'] = true;
                $errorMessage = "Sikeres bejelentkezés!<br>Azonnal átirányítunk a weboldalra!";
                $notificationType = "success";
                echo "<script>
                    window.location.href = '../../index.php';
            </script>";
            } else {
                $errorMessage = "Hibás felhasználónév vagy jelszó!";
                $notificationType = "error";
            }
        } else {
            $errorMessage = "Hibás felhasználónév vagy jelszó!";
            $notificationType = "error";
        }

        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés - AnimeMate</title>
    <link rel="stylesheet" href="../../files/css/core.css">
    <link rel="stylesheet" href="../../files/css/auth/login/login.css">
    <script src="../../files/js/login/input_eye.js"></script>
    <script src="../../files/js/etc/button_handle.js"></script>
    <script type="module" src="https://kit.fontawesome.com/7159432989.js"></script>
    <script src="../../files/js/etc/notification.js"></script>
</head>
<style>
    body {
        overflow: hidden;
    }
</style>
<body>
<?php if (!empty($errorMessage)): ?>
    <div class="notification <?php echo $notificationType; ?>" id="notification">
        <i class="fas <?php echo $notificationType === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
        <span><?php echo str_replace("\n", "<br>", $errorMessage); ?></span>
        <span class="close" onclick="closeNotification()">&times;</span>
    </div>
<?php endif; ?>

<nav class="navbar">
    <div class="navbar-left">
        <a href="../../index.php" class="logo">AnimeMate</a>
    </div>
    <div class="navbar-right">
        <a href="../../index.php" class="login-button">Főoldal</a>
        <a href="../register/register.php" class="nav-link register-button">Regisztráció</a>
    </div>
</nav>
<div class="container">
    <div class="box" id="box">
        <h2>Bejelentkezés az AnimeMate-ba</h2>
        <form method="POST">
            <div class="form-group">
                <label for="username">Felhasználónév</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Jelszó</label>
                <input type="password" id="password" name="password" required>
                <i class="fas fa-eye eye-icon" id="togglePassword"></i>
            </div>
            <div class="form-actions">
                <label>
                    <a href="../forgot/reset.php" class="forgot-password">Hibát tapasztalsz a bejelentkezéskor?</a>
                </label>
            </div>
            <div class="buttons">
                <button type="submit" class="submit-btn" id="s-btn">Bejelentkezés</button>
            </div>
        </form>
        <button class="reg-button" id="reg-button">Nincs még fiókod? Regisztráció</button>
    </div>
</div>
</body>
</html>