<?php
session_start();
require('../backend/connection.php');

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "";
$profileImage = "../profiles/". $username . "/default.png";

if ($isLoggedIn == false) {
    header("Location: ../index.php");
    exit();
}

$isAdmin = false;
$roleQuery = "SELECT role FROM users WHERE username = ?";
$stmt = $conn->prepare($roleQuery);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['role'] === 'admin') {
        $isAdmin = true;
    }
}
$stmt->close();

$searchResults = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $searchQuery = "SELECT id, eng_name, name, image FROM animes WHERE eng_name LIKE ? OR name LIKE ?";
    $stmt = $conn->prepare($searchQuery);
    $searchTerm = "%$searchTerm%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $searchResults[] = $row;
    }
    $stmt->close();
}

$errorMessage = "";
$notificationType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_SESSION['username'];

    $checkQuery = "SELECT submitted FROM recommended_parts WHERE recommended_by = ? ORDER BY submitted DESC LIMIT 1";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $latestSubmission = $result->fetch_assoc();
    $stmt->close();

    $canSubmit = true;
    if ($latestSubmission) {
        $lastSubmissionTime = strtotime($latestSubmission['submitted']);
        $currentTime = time();
        $timeDifference = $currentTime - $lastSubmissionTime;

        if ($timeDifference < 86400) {
            $canSubmit = false;
            $errorMessage = "Csak 24 óránként küldhetsz be részt.";
            $notificationType = "error";
        }
    }

    if ($canSubmit) {
        $to_anime = $_POST['to_anime'] ?? '';
        $part = $_POST['part'] ?? '';
        $link = $_POST['link'] ?? '';

        $insertQuery = "INSERT INTO recommended_parts (recommended_by, to_anime, part, link, submitted) 
                        VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("siis", $username, $to_anime, $part, $link);

        if ($stmt->execute()) {
            $errorMessage = "Rész sikeresen beküldve!";
            $notificationType = "success";
        } else {
            $errorMessage = "Hiba történt a rész beküldése során.";
            $notificationType = "error";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../files/css/core.css">
    <link rel="stylesheet" href="../files/css/upload/upload_part.css">
    <link rel="stylesheet" href="../files/css/auth/login/login.css">
    <link rel="stylesheet" href="../files/css/index.css">
    <script type="module" src="https://kit.fontawesome.com/7159432989.js"></script>
    <title>Rész Feltöltés</title>
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

    <div class="upload-container">
        <div class="sidebar">
            <p style="color: #9147ff; font-weight: 550; margin-top: 0px">Felhasználó</p>
            <a href="upload_part.php" class="sidebar-item active">
                <i class="fas fa-upload"></i>
                <span>Rész feltöltés</span>
            </a>
            <a href="recommend_anime.php" class="sidebar-item">
                <i class="fas fa-video"></i>
                <span>Ajánlás</span>
            </a>
            <a href="history.php" class="sidebar-item">
                <i class="fas fa-history"></i>
                <span>Beküldött tartalmak</span>
            </a>
            <?php if($isAdmin): ?>
            <p style="color: #9147ff; font-weight: 550; margin-top: 0px">Adminisztrátor</p>
            <a href="admin/admin.php" class="sidebar-item">
                <i class="fa-solid fa-lock"></i>
                <span>Animék</span>
            </a>
            <?php endif; ?>
        </div>
        <div class="upload-content boxocska">
            <h1>Anime Rész Feltöltés</h1>
            <form class="upload-form" method="POST" action="">
                <div class="form-group">
                    <label for="to_anime">Anime ID</label>
                    <input type="number" id="to_anime" name="to_anime" placeholder="Anime ID" required>
                </div>
                <div class="form-group">
                    <label for="part">Hanyadik rész?</label>
                    <input type="number" id="part" name="part" placeholder="Animének a valahányadik része" required>
                </div>
                <div class="form-group">
                    <label for="link">Link</label>
                    <input type="text" id="link" name="link" placeholder="Animének a linkje" required>
                </div>
                <button type="submit" class="upload-button">
                    Rész beküldése a(z) AnimeMate csapatnak
                </button>
            </form>
        </div>
    </div>

    <div class="search-mezo">
        <div class="search-box2">
            <form action="" method="GET">
                <input type="text" name="search" placeholder="Anime ID Keresés" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="search-icon">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="results" style="<?php echo empty($searchResults) && isset($_GET['search']) ? 'grid-template-columns: none;' : ''; ?>">
        <?php if (!empty($searchResults)): ?>
            <?php foreach ($searchResults as $anime): ?>
                <div class="result-anime-search">
                    <a href="../watch/anime.php?id=<?php echo htmlspecialchars($anime['id']); ?>&played=false">
                        <img src="<?php echo htmlspecialchars($anime['image']); ?>" alt="<?php echo htmlspecialchars($anime['eng_name']); ?>">
                        <p><?php echo htmlspecialchars($anime['eng_name']); ?></p>
                        <p class="animeid">ID: <?php echo htmlspecialchars($anime['id']); ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <?php if (isset($_GET['search'])): ?>
                <p>Nincs találat.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="../files/js/etc/dropdown.js"></script>
</body>
</html>