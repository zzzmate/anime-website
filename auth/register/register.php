<?php
require('../../backend/connection.php');
session_start();
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

if($isLoggedIn) 
{
    header("Location: ../../index.php");
}

$inputUsername = $inputPassword = $inputEmail = "";
$errorMessage = "";
$notificationType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inputUsername = htmlspecialchars(trim($_POST["username"]));
    $inputPassword = htmlspecialchars(trim($_POST["password"]));
    $inputEmail = htmlspecialchars(trim($_POST["email"]));

    if (empty($inputUsername) || empty($inputPassword) || empty($inputEmail)) {
        $errorMessage = "Minden mező kitöltése kötelező!";
        $notificationType = "error";
    } elseif (!filter_var($inputEmail, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Érvénytelen e-mail cím!";
        $notificationType = "error";
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $inputUsername)) {
        $errorMessage = "A felhasználónév csak betűket, számokat és alulvonást tartalmazhat, és 3-20 karakter hosszú lehet!";
        $notificationType = "error";
    } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/', $inputPassword)) {
        $errorMessage = "A jelszónak legalább 8 karakter hosszúnak kell lennie, és tartalmaznia kell legalább egy betűt, egy számot és egy speciális karaktert!";
        $notificationType = "error";
    } else {
        $checkEmailQuery = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($checkEmailQuery);
        $stmt->bind_param("s", $inputEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errorMessage = "Ez az e-mail cím már használatban van!";
            $notificationType = "error";
            $stmt->close();
        } else {
            $checkUsernameQuery = "SELECT * FROM users WHERE username = ?";
            $stmt = $conn->prepare($checkUsernameQuery);
            $stmt->bind_param("s", $inputUsername);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $errorMessage = "Ez a felhasználónév már foglalt!";
                $notificationType = "error";
                $stmt->close();
            } else {
                $hashedPassword = password_hash($inputPassword, PASSWORD_DEFAULT);

                $insertQuery = "INSERT INTO users (username, nickname, password, email, registered_at) VALUES (?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($insertQuery);
                $randomNumber = mt_rand(1000, 9999);
                $nick = $inputUsername . $randomNumber;
                $stmt->bind_param("ssss", $inputUsername, $nick, $hashedPassword, $inputEmail);

                if ($stmt->execute()) {
                    $errorMessage = "Sikeres regisztráció!<br>Átirányítunk a bejelentkező felületünkre!";
                    $notificationType = "success";
                    echo "<script>setTimeout(() => { window.location.href='../login/login.php'; }, 3000);</script>";
                    $folderName = '../../profiles/' . $inputUsername;
                    if(!file_exists($folderName))
                    {
                        if(mkdir($folderName, 0755, true))
                        {
                            $sourceFile = "../../profiles/". $_SESSION['username'] . "/default.png";
                            $destionationFile = $folderName . '/default.png';
                            copy($sourceFile, $destionationFile);
                        }
                    }
                } else {
                    $errorMessage = "Hiba történt a regisztráció során: " . $stmt->error;
                    $notificationType = "error";
                }

                $stmt->close();
            }
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztréció - AnimeMate</title>
    <link rel="stylesheet" href="../../files/css/core.css">
    <link rel="stylesheet" href="../../files/css/auth/register/register.css">
    <script src="../../files/js/login/input_eye.js"></script>
    <script src="../../files/js/etc/button_handle.js"></script>
    <script type="module" src="https://kit.fontawesome.com/7159432989.js"></script>
    <script src="../../files/js/etc/notification.js"></script>
</head>
<body>
<?php if (!empty($errorMessage)): ?>
    <div class="notification <?php echo $notificationType; ?>" id="notification">
        <i class="fas <?php echo $notificationType === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
        <span><?php echo $errorMessage; ?></span>
        <span class="close" onclick="closeNotification()">&times;</span>
    </div>
<?php endif; ?>

<nav class="navbar">
    <div class="navbar-left">
        <a href="../../index.php" class="logo">AnimeMate</a>
    </div>
    <div class="navbar-right">
        <a href="../../index.php" class="login-button">Főoldal</a>
        <a href="../login/login.php" class="nav-link register-button">Bejelentkezés</a>
    </div>
</nav>
<div class="container">
    <div class="box">
        <h2>Csatlakozz ma az AnimeMate-hoz</h2>
        <p>A fiók létrehozása lehetővé teszi, hogy animéket kedvencezz, kommenteket írj animék alatt, és egyéb <purple>csak-felhasználó</purple> ajándékokban részesülj.</p>
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
            <div class="form-group">
                <label for="email">E-Mail</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-actions">
                <label>
                    <register-thing>
                        <h4>
                            A Regisztráció gombra kattintva beleegyezel az AnimeMate <purple_line>Szolgáltatási feltételeibe</purple_line>, illetve tudomásul veszed az <purple_line>Adatvédelmi nyilatkozatunkat</purple_line> is.
                        </h4>
                    </register-thing>
                </label>
            </div>
            <div class="buttons">
                <button type="submit" class="submit-btn">Regisztráció</button>
            </div>
        </form>
        <button class="log-button" id="log-button">Van fiókod? Bejelentkezés</button>
    </div>
</div>
</body>
</html>