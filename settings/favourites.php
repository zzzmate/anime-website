<?php
session_start();
require('../backend/connection.php');
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "";
$profileImage = "../profiles/". $username . "/default.png";

$favouriteAnimes = [];
$errorMessage = "";
$notificationType = "";

if ($isLoggedIn) {
    $userId = $_SESSION['user_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_favourite'])) {
        $animeId = intval($_POST['anime_id']);

        $stmt = $conn->prepare("SELECT favourites FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $favourites = json_decode($user['favourites'], true);

            if (($key = array_search($animeId, $favourites)) !== false) {
                unset($favourites[$key]);
                $favourites = array_values($favourites);

                $favouritesJson = json_encode($favourites);
                $updateStmt = $conn->prepare("UPDATE users SET favourites = ? WHERE id = ?");
                $updateStmt->bind_param("si", $favouritesJson, $userId);
                $updateStmt->execute();

                if ($updateStmt->affected_rows > 0) {
                    $notificationType = 'success';
                    $errorMessage = 'Sikeresen eltávolítva a kedvencekből!';
                } else {
                    $notificationType = 'error';
                    $errorMessage = 'Nem sikerült frissíteni a kedvenceket!';
                }

                $updateStmt->close();
            } else {
                $notificationType = 'error';
                $errorMessage = 'Az anime nem található a kedvencek között!';
            }
        } else {
            $notificationType = 'error';
            $errorMessage = 'Felhasználó nem található!';
        }

        $stmt->close();
    }

    $stmt = $conn->prepare("SELECT favourites FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $favourites = json_decode($user['favourites'], true);

        if (!empty($favourites)) {
            $favouriteIds = implode(",", $favourites);

            $query = "SELECT id, eng_name, description, image FROM animes WHERE id IN ($favouriteIds)";
            $animeResult = $conn->query($query);

            if ($animeResult->num_rows > 0) {
                while ($row = $animeResult->fetch_assoc()) {
                    $favouriteAnimes[] = $row;
                }
            }
        }
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../files/css/core.css">
    <link rel="stylesheet" href="../files/css/index.css">
    <link rel="stylesheet" href="../files/css/settings/favourites.css">
    <script type="module" src="https://kit.fontawesome.com/7159432989.js"></script>
    <title>Kedvencek</title>
</head>
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
                            <p><?php echo htmlspecialchars($_SESSION['username']) ?></p>
                        </div>
                        <a class="dropdown-item dropdown-first" href="profile.php?id=<?php echo htmlspecialchars($_SESSION['user_id']) ?>">
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
<?php if (!empty($favouriteAnimes)): ?>
<h1 class="title-kedvencek" style="color: #bf94ff;">Kedvencek</h1>
    <?php foreach ($favouriteAnimes as $anime): ?>
        <div class="favourites-container">
            <div class="favourites-list">
                <div class="anime-card">
                    <div class="anime">
                        <a href="#" class="anime-title"><?php echo htmlspecialchars($anime['eng_name']); ?></a>
                        <div class="anime-details">
                            <img src="<?php echo htmlspecialchars($anime['image']); ?>" alt="<?php echo htmlspecialchars($anime['eng_name']); ?>">
                            <div class="anime-description">
                                <p><?php echo htmlspecialchars($anime['description']); ?></p>
                                <div class="buttons">
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="anime_id" value="<?php echo $anime['id']; ?>">
                                        <button type="submit" name="remove_favourite" class="remove-favourite" style="margin-right: 15px;">Eltávolítás</button>
                                    </form>
                                    <a href="../watch/anime.php?id=<?php echo $anime['id']; ?>&playing=false" class="watch-button" id="watch-button-fav">Megnézés</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="no-anime-found">
        <p>Még egy animét se a kedvenceztél be az oldalon!</p>
        <div class="search-box2">
            <form action="../watch/search.php" method="GET">
                <input type="text" name="search" placeholder="Anime Keresés" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="search-icon">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
        </div>
    </div>
<?php endif; ?>
</body>
<script src="../files/js/etc/dropdown.js"></script>
</html>