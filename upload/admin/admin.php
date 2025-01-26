<?php
session_start();
require('../../backend/connection.php');

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "";
$profileImage = "../../profiles/". $username . "/default.png";

if ($isLoggedIn == false) {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $id = $_POST['id']; 
    $tipus = $_POST['tipus'];

    if ($tipus === 'Rész') {
        if ($action === 'accept') {
            $fetchQuery = "SELECT to_anime, part, link FROM recommended_parts WHERE id = ?";
            $stmt = $conn->prepare($fetchQuery);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            $insertQuery = "INSERT INTO anime_parts (part, anime_id, link) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("iis", $row['part'], $row['to_anime'], $row['link']);
            $stmt->execute();
            $stmt->close();

            $updateQuery = "UPDATE recommended_parts SET dontes = 'Elfogadva' WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        } else {
            $updateQuery = "UPDATE recommended_parts SET dontes = 'Elutasítva' WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
    } elseif ($tipus === 'Ajánlás') {
        if ($action === 'accept') {
            $fetchQuery = "SELECT eng_name, name, description, ep, studio, status, agerestriction, animelist, image, trailer FROM recommended_animes WHERE id = ?";
            $stmt = $conn->prepare($fetchQuery);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            $insertQuery = "INSERT INTO animes (name, eng_name, description, ep, studio, status, agerestriction, animelist, image, trailer) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("sssisssiss", $row['name'], $row['eng_name'], $row['description'], $row['ep'], $row['studio'], $row['status'], $row['agerestriction'], $row['animelist'], $row['image'], $row['trailer']);
            $stmt->execute();
            $stmt->close();

            $updateQuery = "UPDATE recommended_animes SET dontes = 'Elfogadva' WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        } else {
            $updateQuery = "UPDATE recommended_animes SET dontes = 'Elutasítva' WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

$submittedContent = [];

$partsQuery = "SELECT rp.id, rp.to_anime, rp.part, rp.submitted, rp.dontes, a.eng_name 
               FROM recommended_parts rp
               JOIN animes a ON rp.to_anime = a.id
               WHERE rp.recommended_by = ?";
$stmt = $conn->prepare($partsQuery);
$stmt->bind_param("s", $username);
$stmt->execute();
$partsResult = $stmt->get_result();

while ($row = $partsResult->fetch_assoc()) {
    $submittedContent[] = [
        'id' => $row['id'],
        'tipus' => 'Rész',
        'name' => $row['eng_name'] . ' ' . $row['part'] . '. rész',
        'submitted_at' => $row['submitted'],
        'dontes' => $row['dontes']
    ];
}
$stmt->close();

$animesQuery = "SELECT id, eng_name, submitted, dontes
                FROM recommended_animes 
                WHERE recommended_by = ?";
$stmt = $conn->prepare($animesQuery);
$stmt->bind_param("s", $username);
$stmt->execute();
$animesResult = $stmt->get_result();

while ($row = $animesResult->fetch_assoc()) {
    $submittedContent[] = [
        'id' => $row['id'],
        'tipus' => 'Ajánlás',
        'name' => $row['eng_name'],
        'submitted_at' => $row['submitted'],
        'dontes' => $row['dontes']
    ];
}
$stmt->close();

usort($submittedContent, function ($a, $b) {
    return strtotime($b['submitted_at']) - strtotime($a['submitted_at']);
});

$showPendingOnly = isset($_GET['pending']) && $_GET['pending'] === 'true';
if ($showPendingOnly) {
    $submittedContent = array_filter($submittedContent, function ($content) {
        return $content['dontes'] !== 'Elfogadva' && $content['dontes'] !== 'Elutasítva';
    });
}

$rowsPerPage = 5;
$totalRows = count($submittedContent);
$totalPages = ceil($totalRows / $rowsPerPage);
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $rowsPerPage;
$paginatedContent = array_slice($submittedContent, $offset, $rowsPerPage);

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../files/css/core.css">
    <link rel="stylesheet" href="../../files/css/upload/upload_part.css">
    <link rel="stylesheet" href="../../files/css/upload/history.css">
    <link rel="stylesheet" href="../../files/css/auth/login/login.css">
    <link rel="stylesheet" href="../../files/css/index.css">
    <script type="module" src="https://kit.fontawesome.com/7159432989.js"></script>
    <title>Beküldött tartalmak</title>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-left">
            <a href="../../index.php" class="logo">AM</a>
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
                            <a class="dropdown-item dropdown-first" href="../../settings/profile.php?id=<?php echo htmlspecialchars($_SESSION['user_id']) ?>">
                                <i class="fas fa-user"></i>
                                <span>Profil</span>
                            </a>
                            <a class="dropdown-item" href="../../settings/favourites.php">
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
                            <a class="dropdown-item logout" href="../../auth/logout/logout.php">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Kijelentkezés</span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <a href="../../auth/login/login.php" class="login-button">Bejelentkezés</a>
                <a href="../../auth/register/register.php" class="nav-link register-button">Regisztráció</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="upload-container">
        <div class="sidebar">
            <p style="color: #9147ff; font-weight: 550; margin-top: 0px">Felhasználó</p>
            <a href="../upload_part.php" class="sidebar-item">
                <i class="fas fa-upload"></i>
                <span>Rész feltöltés</span>
            </a>
            <a href="../recommend_anime.php" class="sidebar-item">
                <i class="fas fa-video"></i>
                <span>Ajánlás</span>
            </a>
            <a href="../history.php" class="sidebar-item">
                <i class="fas fa-history"></i>
                <span>Beküldött tartalmak</span>
            </a>
            <?php if($isAdmin): ?>
            <p style="color: #9147ff; font-weight: 550; margin-top: 0px">Adminisztrátor</p>
            <a href="admin.php" class="sidebar-item active">
                <i class="fa-solid fa-lock"></i>
                <span>Animék</span>
            </a>
            <?php endif; ?>
        </div>
        <div class="upload-content">
            <h1>Beküldött tartalmak <small style="font-size: 13px; font-weight: normal;"></small></h1>
            <form method="GET" action="" class="szures">
                <label>
                    <input type="checkbox" name="pending" value="true" <?php echo $showPendingOnly ? 'checked' : ''; ?>>
                    Csak függőben lévők megjelenítése
                </label>
                <button type="submit" class="accept-btn">Szűrés</button>
            </form>
            <div class="table-container">
                <table class="twitch-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Típus</th>
                            <th>Név</th>
                            <th>Beküldve</th>
                            <th>Státusz</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($paginatedContent)): ?>
                            <?php foreach ($paginatedContent as $content): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($content['id']); ?></td>
                                    <td><?php echo htmlspecialchars($content['tipus']); ?></td>
                                    <td><?php echo htmlspecialchars($content['name']); ?></td>
                                    <td><?php echo htmlspecialchars($content['submitted_at']); ?></td>
                                    <td>
                                        <?php if ($content['dontes'] === 'Elfogadva' || $content['dontes'] === 'Elutasítva'): ?>
                                            <?php echo htmlspecialchars($content['dontes']); ?>
                                        <?php else: ?>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="id" value="<?php echo $content['id']; ?>">
                                                <input type="hidden" name="tipus" value="<?php echo $content['tipus']; ?>">
                                                <button type="submit" name="action" class="accept-btn" value="accept">Elfogadás</button>
                                                <button type="submit" name="action" class="reject-btn" value="reject">Elutasítás</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">Nincs beküldött tartalom.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?php echo $currentPage - 1; ?>&pending=<?php echo $showPendingOnly ? 'true' : 'false'; ?>"><<</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&pending=<?php echo $showPendingOnly ? 'true' : 'false'; ?>" <?php echo $i === $currentPage ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
                <?php endfor; ?>
                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?php echo $currentPage + 1; ?>&pending=<?php echo $showPendingOnly ? 'true' : 'false'; ?>">>></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="../../files/js/etc/dropdown.js"></script>
</body>
</html>