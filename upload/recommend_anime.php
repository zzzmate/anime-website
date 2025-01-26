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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_SESSION['username'];

    $checkQuery = "SELECT submitted FROM recommended_animes WHERE recommended_by = ? ORDER BY submitted DESC LIMIT 1";
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
            $errorMessage = "Csak 24 óránként küldhetsz be ajánlatot.";
            $notificationType = "error";
        }
    }

    if ($canSubmit) {
        $name = $_POST['name'] ?? '';
        $eng_name = $_POST['eng_name'] ?? '';
        $description = $_POST['description'] ?? '';
        $ep = $_POST['ep'] ?? '';
        $studio = $_POST['studio'] ?? '';
        $status = $_POST['status'] ?? '';
        $agerestriction = $_POST['agerestriction'] ?? '';
        $animelist = $_POST['animelist'] ?? '';
        $image = $_POST['image'] ?? '';
        $trailer = $_POST['trailer'] ?? '';
        $created_at = $_POST['created_at'] ?? '';

        $insertQuery = "INSERT INTO recommended_animes (recommended_by, name, eng_name, description, ep, studio, status, agerestriction, animelist, image, trailer, created_at, submitted) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ssssisssisss", $username, $name, $eng_name, $description, $ep, $studio, $status, $agerestriction, $animelist, $image, $trailer, $created_at);

        if ($stmt->execute()) {
            $errorMessage = "Anime ajánlat sikeresen beküldve!";
            $notificationType = "success";
        } else {
            $errorMessage = "Hiba történt az anime ajánlat beküldése során.";
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
    <title>Ajánlás</title>
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
            <a href="upload_part.php" class="sidebar-item">
                <i class="fas fa-upload"></i>
                <span>Rész feltöltés</span>
            </a>
            <a href="recommend_anime.php" class="sidebar-item active">
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
            <h1>Anime Ajánlat</h1>
            <form class="upload-form" method="POST" action="">
                <div class="form-group">
                    <label for="name">Anime Név</label>
                    <input type="text" id="name" name="name" placeholder="Anime név (Angol)" required>
                </div>
                <div class="form-group">
                    <label for="eng_name">Anime Név</label>
                    <input type="text" id="eng_name" name="eng_name" placeholder="Anime név (Japán - Angol írott karakterekkel)" required>
                </div>
                <div class="form-group">
                    <label for="studio">Stúdió</label>
                    <input type="text" id="studio" name="studio" placeholder="Anime stúdiója" required>
                </div>
                <div class="form-group">
                    <label for="ep">Rész</label>
                    <input type="number" id="ep" name="ep" placeholder="Anime maximális része" required>
                </div>
                <div class="form-group">
                    <label for="agerestriction">Besorolás</label>
                    <input type="number" id="agerestriction" name="agerestriction" placeholder="Besorolás" required>
                </div>
                <div class="form-group">
                    <label for="image">Borítókép</label>
                    <input type="text" id="image" name="image" placeholder="Borítókép linkje" required>
                </div>
                <div class="form-group">
                    <label for="status">Befejezett / Befejezetlen?</label>
                    <input type="text" id="status" name="status" placeholder="Státusz" required>
                </div>
                <div class="form-group">
                    <label for="created_at">Létrehozva?</label>
                    <input type="date" name="created_at" id="created_at">
                </div>
                <div class="form-group">
                    <label for="trailer">Trailer</label>
                    <input type="text" id="trailer" name="trailer" placeholder="Trailer (bemutató) linkje" required>
                </div>
                <div class="form-group">
                    <label for="animelist">MyAnimeList</label>
                    <input type="text" id="animelist" name="animelist" placeholder="MyAnimeList linkje" required>
                </div>
                <div class="form-group">
                    <label for="description">Anime Leírás</label>
                    <textarea id="description" name="description" placeholder="Anime leírás (Magyar)" class="textareabekuldes" rows="4" required></textarea>
                </div>
                <button type="submit" class="upload-button">
                    Anime ajánlat beküldése a(z) AnimeMate csapatnak
                </button>
            </form>
        </div>
    </div>
    <script src="../files/js/etc/dropdown.js"></script>
</body>
</html>