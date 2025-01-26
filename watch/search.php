<?php
session_start();
require('../backend/connection.php');

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "";
$profileImage = "../profiles/". $username . "/default.png";

$searchResults = [];
$tagResults = [];

if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $query = "SELECT * FROM animes WHERE eng_name LIKE ? OR name LIKE ?";
    $stmt = $conn->prepare($query);
    $searchTerm = "%$searchTerm%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $searchResults = $result->fetch_all(MYSQLI_ASSOC);
} elseif (isset($_GET['tag'])) {
    $tag = $_GET['tag'];
    $tagQuery = "SELECT to_anime FROM tags WHERE tag = ?";
    $stmt = $conn->prepare($tagQuery);
    $stmt->bind_param("s", $tag);
    $stmt->execute();
    $tagResult = $stmt->get_result();
    $animeIds = $tagResult->fetch_all(MYSQLI_ASSOC);

    if (!empty($animeIds)) {
        $animeIdList = array_column($animeIds, 'to_anime');
        $animeQuery = "SELECT * FROM animes WHERE id IN (" . implode(",", $animeIdList) . ")";
        $animeResult = $conn->query($animeQuery);
        $tagResults = $animeResult->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../files/css/core.css">
    <link rel="stylesheet" href="../files/css/index.css">
    <script type="module" src="https://kit.fontawesome.com/7159432989.js"></script>
    <script src="files/js/etc/dropdown.js"></script>
    <title>AnimeMate</title>
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
                            <img src="../files/images/default.png" alt="">
                            <p><?php echo htmlspecialchars($_SESSION['username']) ?></p>
                        </div>
                        <a class="dropdown-item dropdown-first" href="../settings/profile.php?id=<?php echo htmlspecialchars($_SESSION['user_id']) ?>">
                            <i class="fas fa-user"></i>
                            <span>Profil</span>
                        </a>
                        <a class="dropdown-item" href="../settings/favourites.php">
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
<div class="container">
    <div class="search-box2">
        <form action="" method="GET">
            <input type="text" name="search" placeholder="Anime Keresés" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit" class="search-icon">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </form>
    </div>
    <div class="animes">
        <?php if (isset($_GET['search'])): ?>
            <h3 class="anime-title">Találatok a(z) "<?php echo htmlspecialchars($_GET['search']); ?>" keresésre</h3>
            <?php if (!empty($searchResults)): ?>
                <div class="anime-grid">
                    <?php
                    $rowCount = 0;
                    foreach ($searchResults as $anime):
                        if ($rowCount % 5 == 0) {
                            echo '<div class="row">';
                        }
                    ?>
                        <a href="http://localhost/mateanime/watch/anime.php?id=<?php echo $anime['id']; ?>&playing=false">
                            <img src="<?php echo htmlspecialchars($anime['image']); ?>" alt="<?php echo htmlspecialchars($anime['eng_name']); ?>">
                            <h4><?php echo htmlspecialchars($anime['eng_name']); ?></h4>
                        </a>
                    <?php
                        $rowCount++;
                        if ($rowCount % 5 == 0) {
                            echo '</div>';
                        }
                    endforeach;
                    if ($rowCount % 5 != 0) {
                        echo '</div>';
                    }
                    ?>
                </div>
            <?php else: ?>
                <p>Nincs találat a keresésre.</p>
            <?php endif; ?>
        <?php elseif (isset($_GET['tag'])): ?>
            <h3 class="anime-title">Találatok a(z) "<?php echo htmlspecialchars($_GET['tag']); ?>" címkére</h3>
            <?php if (!empty($tagResults)): ?>
                <div class="anime-grid">
                    <?php
                    $rowCount = 0;
                    foreach ($tagResults as $anime):
                        if ($rowCount % 5 == 0) {
                            echo '<div class="row">';
                        }
                    ?>
                        <a href="anime.php?id=<?php echo $anime['id']; ?>&playing=false">
                            <img src="<?php echo htmlspecialchars($anime['image']); ?>" alt="<?php echo htmlspecialchars($anime['eng_name']); ?>">
                            <h4><?php echo htmlspecialchars($anime['eng_name']); ?></h4>
                        </a>
                    <?php
                        $rowCount++;
                        if ($rowCount % 5 == 0) {
                            echo '</div>';
                        }
                    endforeach;
                    if ($rowCount % 5 != 0) {
                        echo '</div>';
                    }
                    ?>
                </div>
            <?php else: ?>
                <p>Nincs találat a címkére.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<script src="../files/js/etc/dropdown.js"></script>
</body>
</html>