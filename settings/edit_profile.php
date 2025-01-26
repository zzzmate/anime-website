<?php
session_start();
require('../backend/connection.php');

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$profileImage = "../profiles/". $_SESSION['username'] . "/default.png";

if($isLoggedIn == false) 
{
    header("Location: ../index.php");
}

if (isset($_GET['id'])) {
    $userId = $_GET['id'];
} elseif ($isLoggedIn) {
    $userId = $_SESSION['user_id'];
} else {
    header("Location: ../index.php");
    exit();
}

$stmt = $conn->prepare("SELECT id, username, nickname, bio, registered_at FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $profileImage = "../profiles/" . $user['username'] . "/default.png";
    if (!file_exists($profileImage)) {
        $profileImage = "../profiles/". $_SESSION['username'] . "/default.png";
    }
} else {
    header("Location: ../index.php");
    exit();
}
$stmt->close();

$notification = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newNickname = $_POST['username'];
    $newBio = $_POST['bio'];

    if ($newNickname !== $user['nickname'] || $newBio !== $user['bio']) {
        $updateStmt = $conn->prepare("UPDATE users SET nickname = ?, bio = ? WHERE id = ?");
        $updateStmt->bind_param("ssi", $newNickname, $newBio, $userId);
        if ($updateStmt->execute()) {
            $errorMessage = "Sikeresen megváltoztattad a profilod!";
            $notificationType = "success";
            $user['nickname'] = $newNickname;
            $user['bio'] = $newBio;
        } else {
            $errorMessage = "Hiba történt a profil frissítése közben.";
            $notificationType = "error";
        }
        $updateStmt->close();
    } else {
        $errorMessage = "Nem történt változás.";
        $notificationType = "success";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../files/css/core.css">
    <link rel="stylesheet" href="../files/css/settings/edit_profile.css">
    <script type="module" src="https://kit.fontawesome.com/7159432989.js"></script>
    <script src="../files/js/etc/button_handle.js"></script>
    <title>Profil</title>
</head>
<body>
<nav class="navbar">
    <div class="navbar-left">
        <a href="../index.php" class="logo">AM</a>
    </div>
    <div class="navbar-right">
        <?php if ($isLoggedIn): ?>
            <div class="profile">
                <div class="profile-dropdown">
                    <img src="<?php echo $profileImage; ?>" alt="Profile" class="profile-image" id="profileImage">
                    <div class="dropdown-content" id="dropdownContent">
                        <div class="current-profile">
                            <img src="<?php echo $profileImage; ?>" alt="">
                            <p><?php echo htmlspecialchars($user['username']); ?></p>
                        </div>
                        <a class="dropdown-item dropdown-first" href="profile.php?id=<?php echo htmlspecialchars($user['id']); ?>">
                            <i class="fas fa-user"></i>
                            <span>Profil</span>
                        </a>
                        <a class="dropdown-item" href="favourites.php">
                            <i class="fas fa-star"></i>
                            <span>Kedvencek</span>
                        </a>
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-cog"></i>
                            <span>Beállítások</span>
                        </a>
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-question-circle"></i>
                            <span>Támogatás</span>
                        </a>
                        <a class="dropdown-item logout" href="../auth/logout/logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Kijelentkezés</span>
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <a href="../auth/login/login.php" class="login-button">Bejelentkezés</a>
            <a href="../auth/register/register.php" class="nav-link register-button">Regisztráció</a>
        <?php endif; ?>
    </div>
</nav>
<?php if (!empty($errorMessage)): ?>
    <div class="notification <?php echo $notificationType; ?>" id="notification">
        <i class="fas <?php echo $notificationType === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
        <span><?php echo str_replace("\n", "<br>", $errorMessage); ?></span>
        <span class="close" onclick="closeNotification()">&times;</span>
    </div>
<?php endif; ?>
<div class="container">
    <div class="box" id="box">
        <h2>Profil Beállítások</h2>
        <form method="POST">
            <div class="form-group">
                <label for="username">Becenév</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['nickname']); ?>" required>
            </div>
            <div class="form-group">
                <label for="bioTextarea">Leírás</label>
                <textarea name="bio" class="bioTextarea" maxlength="80" id="bioTextarea" rows="3"><?php echo htmlspecialchars($user['bio']); ?></textarea>
            </div>
            <div class="form-actions">
                <label>
                    <a href="edit_image.php" class="forgot-password">Képet szeretnél cserélni?</a>
                </label>
            </div>
            <div class="buttons">
                <button type="submit" class="submit-btn" id="s-btn">Mentés</button>
            </div>
        </form>
        <button class="reg-button" id="back-button">Vissza</button>
    </div>
</div>
</body>
<script src="../files/js/etc/dropdown.js"></script>
</html>