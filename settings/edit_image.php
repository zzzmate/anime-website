<?php
session_start();
require('../backend/connection.php');

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$profileImage = "../profiles/". $_SESSION['username'] . "/default.png";

if (!$isLoggedIn) {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $userId = $_GET['id'];
} else {
    $userId = $_SESSION['user_id'];
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
$errorMessage = "";
$notificationType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['upload-picture']) && $_FILES['upload-picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "../profiles/" . $user['username'] . "/";
        $uploadFile = $uploadDir . "default.png";

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileType = strtolower(pathinfo($_FILES['upload-picture']['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['upload-picture']['tmp_name'], $uploadFile)) {
                $errorMessage = "A profilképed sikeresen frissült!";
                $notificationType = "success";
                $profileImage = $uploadFile;
            } else {
                $errorMessage = "Hiba történt a kép feltöltése közben.";
                $notificationType = "error";
            }
        } else {
            $errorMessage = "Csak JPG, JPEG, PNG és GIF fájlok tölthetők fel.";
            $notificationType = "error";
        }
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
        <h2>Jelenlegi profilképed</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group img-group">
                <img src="<?php echo $profileImage; ?>" alt="Profile Image" style="border-radius: 3px;">
                <label for="upload-picture" class="upload-button">Kép feltöltés</label>
                <input type="file" name="upload-picture" id="upload-picture" class="upload-picture" accept="image/*">
            </div>
            <div class="form-actions">
                <label>
                    <a href="edit_profile.php" class="forgot-password">Szöveget szeretnél cserélni?</a>
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