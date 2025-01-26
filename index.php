<?php
session_start();
require('backend/connection.php');

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "";
$profileImage = "profiles/". $username . "/default.png";

if (isset($_SESSION['just_logged_in']) && $_SESSION['just_logged_in'] === true) {
    $errorMessage = "Sikeresen bejelentkeztél az oldalra!";
    $notificationType = "success";
    $_SESSION['just_logged_in'] = false;
} else {
    $errorMessage = "";
    $notificationType = "";
}

$recommendedQuery = "SELECT id, eng_name, description, rated, trailer, image FROM animes WHERE recommended = 1 LIMIT 1";
$recommendedResult = $conn->query($recommendedQuery);
$recommendedAnime = $recommendedResult->fetch_assoc();

$recommendedVidQuery = "SELECT link FROM anime_parts WHERE part = 1 AND anime_id = ?";
$stmt = $conn->prepare($recommendedVidQuery);
$stmt->bind_param("i", $recommendedAnime['id']);
$stmt->execute();
$recommendedVidResult = $stmt->get_result();
$recommendedVidAnime = $recommendedVidResult->fetch_assoc();

$tags = [];
if ($recommendedAnime) {
    $tagsQuery = "SELECT tag FROM tags WHERE to_anime = ?";
    $stmt = $conn->prepare($tagsQuery);
    $stmt->bind_param("i", $recommendedAnime['id']);
    $stmt->execute();
    $tagsResult = $stmt->get_result();

    while ($row = $tagsResult->fetch_assoc()) {
        $tags[] = $row['tag'];
    }
    $stmt->close();
}

$weeklyQuery = "SELECT id, eng_name, image FROM animes WHERE weekly = 1 LIMIT 5";
$weeklyResult = $conn->query($weeklyQuery);
$weeklyPopular = [];
if ($weeklyResult->num_rows > 0) {
    while ($row = $weeklyResult->fetch_assoc()) {
        $weeklyPopular[] = $row;
    }
}

$ratedQuery = "SELECT id, eng_name, image, rated FROM animes ORDER BY rated DESC LIMIT 5";
$ratedResult = $conn->query($ratedQuery);
$topRated = [];
if ($ratedResult->num_rows > 0) {
    while ($row = $ratedResult->fetch_assoc()) {
        $topRated[] = $row;
    }
}

$recentQuery = "SELECT id, eng_name, image FROM animes ORDER BY uploaded_at DESC LIMIT 10";
$recentResult = $conn->query($recentQuery);
$recentlyAdded = [];
if ($recentResult->num_rows > 0) {
    while ($row = $recentResult->fetch_assoc()) {
        $recentlyAdded[] = $row;
    }
}

$userFavourites = [];
if ($isLoggedIn) {
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT favourites FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && !empty($user['favourites'])) {
        $userFavourites = json_decode($user['favourites'], true);
    }
}

$isFavourite = in_array($recommendedAnime['id'], $userFavourites);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="files/css/core.css">
    <link rel="stylesheet" href="files/css/index.css">
    <script type="module" src="https://kit.fontawesome.com/7159432989.js"></script>
    <script src="files/js/index/playbutton.js"></script>
    <title>AnimeMate</title>
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
        <a href="index.php" class="logo">AM</a>
    </div>
    <div class="navbar-right">
        <?php if ($isLoggedIn): ?>
            <a href="upload/upload_part.php" class="login-button">Feltöltés</a>
            <div class="profile">
                <div class="profile-dropdown">
                    <img src="<?php echo $profileImage; ?>" alt="Profile" class="profile-image" id="profileImage">
                    <div class="dropdown-content" id="dropdownContent">
                        <div class="current-profile">
                            <img src="<?php echo $profileImage; ?>" alt="">
                            <p><?php echo htmlspecialchars($_SESSION['username']) ?></p>
                        </div>
                        <a class="dropdown-item dropdown-first" href="settings/profile.php?id=<?php echo htmlspecialchars($_SESSION['user_id']) ?>">
                            <i class="fas fa-user"></i>
                            <span>Profil</span>
                        </a>
                        <a class="dropdown-item" href="settings/favourites.php">
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
                        <a class="dropdown-item logout" href="auth/logout/logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Kijelentkezés</span>
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <a href="auth/login/login.php" class="login-button">Bejelentkezés</a>
            <a href="auth/register/register.php" class="nav-link register-button">Regisztráció</a>
        <?php endif; ?>
    </div>
</nav>
<div class="container">
    <div class="search-box2">
        <form action="watch/search.php" method="GET">
            <input type="text" name="search" placeholder="Anime Keresés" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit" class="search-icon">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </form>
    </div>
    <div class="recommended">
        <video class="recommend-background" id="recommend-background" autoplay muted loop>
            <source src="<?php echo htmlspecialchars($recommendedVidAnime['link']); ?>" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="anime-recommended">
            <img src="<?php echo htmlspecialchars($recommendedAnime['image']); ?>" class="pc-img" alt="">
            <div class="recommended-desc">
                <h3><?php echo htmlspecialchars($recommendedAnime['eng_name']); ?></h3>
                <div class="tags">
                    <?php foreach ($tags as $tag): ?>
                        <a href="watch/search.php?tag=<?php echo urlencode($tag); ?>"><?php echo htmlspecialchars($tag); ?></a>
                    <?php endforeach; ?>
                    <?php if ($isFavourite): ?>
                        <a href="settings/favourites.php" class="favourite">Kedvenc</a>
                    <?php endif; ?>
                </div>
                <span class="rating"><i class="fa-solid fa-star" style="color: yellow;"></i> <?php echo htmlspecialchars($recommendedAnime['rated']); ?>/10</span>
                <p><?php echo htmlspecialchars($recommendedAnime['description']); ?></p>
                <div class="buttonss">
                    <a href="watch/anime.php?id=<?php echo htmlspecialchars($recommendedAnime['id']) ?>&playing=false" class="watch-button">Megtekintés</a>
                    <or>—</or>
                    <a href="<?php echo htmlspecialchars($recommendedAnime['trailer']); ?>" class="watch-button" target="_blank">Előzetes</a>
                    <a href="#" class="play-button watch-button"><i class="fa-solid fa-stop"></i></a>
                </div>
            </div>
        </div>
        <div class="m-recommended-img">
            <img src="<?php echo htmlspecialchars($recommendedAnime['image']); ?>" alt="">
        </div>
    </div>
    <div class="animes">
        <h3 class="anime-title">Heti felkapottak</h3>
        <div class="popular">
            <div class="row">
                <?php foreach ($weeklyPopular as $anime): ?>
                    <a href="http://localhost/mateanime/watch/anime.php?id=<?php echo $anime['id']; ?>&playing=false">
                        <img src="<?php echo $anime['image']; ?>" alt="<?php echo $anime['eng_name']; ?>">
                        <h4><?php echo $anime['eng_name']; ?></h4>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <h3 class="anime-title">Legjobb értékeltek</h3>
        <div class="rated">
            <div class="row">
                <?php foreach ($topRated as $anime): ?>
                    <a href="http://localhost/mateanime/watch/anime.php?id=<?php echo $anime['id']; ?>&playing=false">
                        <img src="<?php echo $anime['image']; ?>" alt="<?php echo $anime['eng_name']; ?>">
                        <h4><?php echo $anime['eng_name']; ?></h4>
                        <p style="text-decoration: none; color: white"><i class="fa-solid fa-star" style="color: yellow;"></i> <?php echo $anime['rated']; ?>/10</p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <h3 class="anime-title">Nem rég feltöltve</h3>
        <div class="rated">
            <div class="row">
                <?php foreach ($recentlyAdded as $anime): ?>
                    <a href="http://localhost/mateanime/watch/anime.php?id=<?php echo $anime['id']; ?>&playing=false">
                        <img src="<?php echo $anime['image']; ?>" alt="<?php echo $anime['eng_name']; ?>">
                        <h4><?php echo $anime['eng_name']; ?></h4>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
</body>
<script src="files/js/etc/dropdown.js"></script>
</html>