<?php
session_start();
require('../backend/connection.php');

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$username = $_SESSION['username'] ?? "";
$profileImage = "../profiles/" . $username . "/default.png";

if (isset($_GET['id'])) {
    $userId = $_GET['id'];
} elseif ($isLoggedIn) {
    $userId = $_SESSION['user_id'];
} else {
    header("Location: ../index.php");
    exit();
}

$stmt = $conn->prepare("SELECT id, username, nickname, bio, registered_at, last_three_watched FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $profileImage = "../profiles/" . $user['username'] . "/default.png";
    if (!file_exists($profileImage)) {
        $profileImage = "../profiles/" . $username . "/default.png";
    }
} else {
    header("Location: ../index.php");
    exit();
}
$stmt->close();

$lastThreeWatched = [];
if (!empty($user['last_three_watched'])) {
    $lastThreeWatched = json_decode($user['last_three_watched'], true);
}

$watchedAnimes = [];
if (!empty($lastThreeWatched)) {
    $placeholders = implode(',', array_fill(0, count($lastThreeWatched), '?'));
    $stmt = $conn->prepare("SELECT id, eng_name, description, image FROM animes WHERE id IN ($placeholders)");
    $stmt->bind_param(str_repeat('i', count($lastThreeWatched)), ...$lastThreeWatched);
    $stmt->execute();
    $result = $stmt->get_result();
    $fetchedAnimes = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($lastThreeWatched as $id) {
        foreach ($fetchedAnimes as $anime) {
            if ($anime['id'] == $id) {
                $watchedAnimes[] = $anime;
                break;
            }
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
    <link rel="stylesheet" href="../files/css/settings/profile.css">
    <script type="module" src="https://kit.fontawesome.com/7159432989.js"></script>
    <title>Profil</title>
</head>
<body>
<nav class="navbar">
    <div class="navbar-left">
        <a href="../index.php" class="logo">AM</a>
    </div>
    <div class="navbar-right">
        <?php if ($isLoggedIn): ?>
            <a href="../upload/upload_part.php" class="login-button">Feltöltés</a>
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
<div class="profile-container">
    <div class="profile jatekosprofil">
        <div class="felso-sor">
            <div class="kep">
                <img src="<?php echo $profileImage; ?>" alt="Profile Image">
                <h4><?php echo htmlspecialchars($user['registered_at']); ?></h4>
                <h5>AnimeMate ID: <?php echo htmlspecialchars($user['id']); ?></h5>
            </div>
            <div class="szovegek">
                <div class="nickandsettings">
                    <p class="nickname"><?php echo htmlspecialchars($user['nickname']); ?></p>
                    <?php if ($isLoggedIn && $userId == $_SESSION['user_id']): ?>
                        <a href="edit_profile.php"><i class="fa-solid fa-gear"></i></a>
                    <?php endif; ?>
                </div>
                <p class="bio"><?php echo htmlspecialchars($user['bio']); ?></p>
            </div>
        </div>
        <div class="last-watched">
            <h4>Legutóbbi 3 megnézett anime</h4>
            <?php if (!empty($watchedAnimes)): ?>
                <?php foreach ($watchedAnimes as $anime): ?>
                    <div class="first-column">
                        <img src="<?php echo htmlspecialchars($anime['image']); ?>" alt="<?php echo htmlspecialchars($anime['eng_name']); ?>">
                        <div class="leirasok">
                            <a href="watch.php?id=<?php echo htmlspecialchars($anime['id']); ?>"><?php echo htmlspecialchars($anime['eng_name']); ?></a>
                            <p class="leiras"><?php echo htmlspecialchars($anime['description']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nincsenek megnézett animek.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
<script src="../files/js/etc/dropdown.js"></script>
</html>