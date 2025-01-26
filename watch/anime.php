<?php
session_start();
require('../backend/connection.php');

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "";
$profileImage = "../profiles/" . $username . "/default.png";

$notificationType = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_favorites'])) {
        if (!$isLoggedIn) {
            $notificationType = 'error';
            $errorMessage = 'Kérlek jelentkezz be ehhez az interakcióhoz!';
        } else {
            $animeId = isset($_POST['anime_id']) ? intval($_POST['anime_id']) : 0;

            if ($animeId <= 0) {
                $notificationType = 'error';
                $errorMessage = 'Érvénytelen anime ID!';
            } else {
                $userId = $_SESSION['user_id'];
                $stmt = $conn->prepare("SELECT favourites FROM users WHERE id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();

                if (!$user) {
                    $notificationType = 'error';
                    $errorMessage = 'Felhasználó nem található!';
                } else {
                    $favourites = json_decode($user['favourites'], true) ?? [];

                    if (in_array($animeId, $favourites)) {
                        $index = array_search($animeId, $favourites);
                        if ($index !== false) {
                            unset($favourites[$index]);
                            $favourites = array_values($favourites);

                            $favouritesJson = json_encode($favourites);
                            $stmt = $conn->prepare("UPDATE users SET favourites = ? WHERE id = ?");
                            $stmt->bind_param("si", $favouritesJson, $userId);
                            $stmt->execute();

                            $notificationType = 'success';
                            $errorMessage = 'Sikeresen törölted az animét a kedvencek közül!';
                        }
                    } else {
                        $favourites[] = $animeId;

                        $favouritesJson = json_encode($favourites);
                        $stmt = $conn->prepare("UPDATE users SET favourites = ? WHERE id = ?");
                        $stmt->bind_param("si", $favouritesJson, $userId);
                        $stmt->execute();

                        $notificationType = 'success';
                        $errorMessage = 'Sikeresen hozzáadva a kedvencekhez!';
                    }
                }
            }
        }
    }

    if (isset($_POST['submit_comment'])) {
        if (!$isLoggedIn) {
            $notificationType = 'error';
            $errorMessage = 'Kérlek jelentkezz be a hozzászóláshoz!';
        } else {
            $animeId = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $userId = $_SESSION['user_id'];
            $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

            if ($animeId <= 0) {
                $notificationType = 'error';
                $errorMessage = 'Érvénytelen anime ID!';
            } elseif (empty($comment)) {
                $notificationType = 'error';
                $errorMessage = 'A hozzászólás nem lehet üres!';
            } else {
                $stmt = $conn->prepare("INSERT INTO comments (user_id, anime_id, comment) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $userId, $animeId, $comment);
                if ($stmt->execute()) {
                    $notificationType = 'success';
                    $errorMessage = 'Hozzászólás sikeresen hozzáadva!';
                } else {
                    $notificationType = 'error';
                    $errorMessage = 'Hiba történt a hozzászólás hozzáadása közben!';
                }
                $stmt->close();
            }
        }
    }
}

$playing = isset($_GET['playing']) && $_GET['playing'] === 'true';
$animeId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$animeDetails = [];
if ($animeId > 0) {
    $query = "SELECT * FROM animes WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $animeId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $animeDetails = $result->fetch_assoc();
    }
    $stmt->close();

    $stmt = $conn->prepare("UPDATE animes SET watched = watched + 1 WHERE id = ?");
    $stmt->bind_param("i", $animeId);

    $stmt->execute();
    $stmt->close();
}

if (empty($animeDetails)) {
    header("Location: ../index.php");
    exit();
}

$tags = [];
if ($animeId > 0) {
    $query = "SELECT tag FROM tags WHERE to_anime = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $animeId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $tags[] = $row['tag'];
    }
    $stmt->close();
}

$animeParts = [];
if ($animeId > 0) {
    $query = "SELECT part, link FROM anime_parts WHERE anime_id = ? ORDER BY part ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $animeId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $animeParts[] = $row;
    }
    $stmt->close();
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

$isFavourite = in_array($animeId, $userFavourites);

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $animeId = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($animeId > 0) {
        $stmt = $conn->prepare("SELECT last_three_watched FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $lastThreeWatched = json_decode($user['last_three_watched'], true) ?? [];

            if (!in_array($animeId, $lastThreeWatched)) {
                array_unshift($lastThreeWatched, $animeId);

                $lastThreeWatched = array_slice($lastThreeWatched, 0, 3);

                $lastThreeWatchedJson = json_encode($lastThreeWatched);
                $updateStmt = $conn->prepare("UPDATE users SET last_three_watched = ? WHERE username = ?");
                $updateStmt->bind_param("ss", $lastThreeWatchedJson, $username);
                $updateStmt->execute();
                $updateStmt->close();
            }
        }
        $stmt->close();
    }
}

$comments = [];
if ($animeId > 0) {
    $query = "SELECT c.id, c.comment, u.username, u.id AS user_id FROM comments c JOIN users u ON c.user_id = u.id WHERE c.anime_id = ? ORDER BY c.id DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $animeId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }
    $stmt->close();
}

if (isset($_POST['delete_comment'])) {
    if (!$isLoggedIn) {
        $notificationType = 'error';
        $errorMessage = 'Kérlek jelentkezz be a hozzászólás törléséhez!';
    } else {
        $commentId = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;

        if ($commentId <= 0) {
            $notificationType = 'error';
            $errorMessage = 'Érvénytelen hozzászólás ID!';
        } else {
            $stmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
            $stmt->bind_param("i", $commentId);
            $stmt->execute();
            $result = $stmt->get_result();
            $comment = $result->fetch_assoc();

            if (!$comment) {
                $notificationType = 'error';
                $errorMessage = 'Hozzászólás nem található!';
            } elseif ($comment['user_id'] !== $_SESSION['user_id']) {
                $notificationType = 'error';
                $errorMessage = 'Csak a saját hozzászólásaidat törölheted!';
            } else {
                $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
                $stmt->bind_param("i", $commentId);
                if ($stmt->execute()) {
                    header("Location: " . $_SERVER['REQUEST_URI']);
                    $notificationType = 'success';
                    $errorMessage = 'Hozzászólás sikeresen törölve!';
                } else {
                    $notificationType = 'error';
                    $errorMessage = 'Hiba történt a hozzászólás törlése közben!';
                }
                $stmt->close();
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
    <title><?php echo htmlspecialchars($animeDetails['name']); ?></title>
    <link rel="stylesheet" href="../files/css/core.css">
    <link rel="stylesheet" href="../files/css/watch/anime.css">
    <script src="../../files/js/login/input_eye.js"></script>
    <script src="../../files/js/etc/button_handle.js"></script>
    <script type="module" src="https://kit.fontawesome.com/7159432989.js"></script>
    <script src="../files/js/etc/notification.js"></script>
    <script src="files/js/etc/dropdown.js"></script>
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
            <a href="../upload/upload_part.php" class="login-button">Feltöltés</a>
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

<?php if ($playing): ?>
    <div class="playing-container">
        <div class="parts">
            <h2>Részek</h2>
            <p style="text-align: center;" class="no-part">Nincsenek elérhető részek.</p>
            <div class="each-part">
                <?php if (!empty($animeParts)): ?>
                    <?php foreach ($animeParts as $part): ?>
                        <a href="?id=<?php echo $animeId; ?>&playing=true&part=<?php echo $part['part']; ?>">
                            <?php echo $part['part']; ?>. Rész
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <style>
                        .no-part {
                            display: block;
                        }
                    </style>
                <?php endif; ?>
            </div>
        </div>
        <div class="video-container">
            <?php
            $selectedPartLink = "";
            if (isset($_GET['part'])) {
                $selectedPart = intval($_GET['part']);
                foreach ($animeParts as $part) {
                    if ($part['part'] == $selectedPart) {
                        $selectedPartLink = $part['link'];
                        break;
                    }
                }
            } else {
                $selectedPartLink = !empty($animeParts) ? $animeParts[0]['link'] : "";
            }
            ?>
            <iframe
                width="700px"
                height="400px"
                src="<?php echo htmlspecialchars($selectedPartLink); ?>"
                frameborder="0"
                allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen>
            </iframe>
        </div>
    </div>
    <div class="other">
        <div class="divider-container">
            <div class="divider">
                <span>Egyéb</span>
            </div>
        </div>
        <h4 class="not-found">Nem ez az anime amit keresel?</h4>
        <div class="search">
            <div class="search-box2">
                <form action="../watch/search.php" method="GET">
                    <input type="text" name="search" placeholder="Anime Keresés" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="search-icon">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="info-container">
        <div class="anime-info">
        <div class="titles">
            <h2><?php echo htmlspecialchars($animeDetails['name']); ?></h2>
            <h3><?php echo htmlspecialchars($animeDetails['eng_name']); ?></h3>
        </div>
            <div class="description">
                <div class="tags-m">
                    <?php foreach ($tags as $tag): ?>
                        <a href="../watch/search.php?tag=<?php echo urlencode($tag); ?>"><?php echo htmlspecialchars($tag); ?></a>
                    <?php endforeach; ?>
                    <?php if ($isFavourite): ?>
                        <a href="../settings/favourites.php" class="favourite">Kedvenc</a>
                    <?php endif; ?>
                </div>
                <img src="<?php echo htmlspecialchars($animeDetails['image']); ?>" alt="<?php echo htmlspecialchars($animeDetails['eng_name']); ?>">
                <div class="texts">
                    <div class="tags">
                        <?php foreach ($tags as $tag): ?>
                            <a href="../watch/search.php?tag=<?php echo urlencode($tag); ?>"><?php echo htmlspecialchars($tag); ?></a>
                        <?php endforeach; ?>
                        <?php if ($isFavourite): ?>
                            <a href="../settings/favourites.php" class="favourite">Kedvenc</a>
                        <?php endif; ?>
                    </div>
                    <div class="anime-description">
                        <span class="rating"><i class="fa-solid fa-star" style="color: yellow;"></i> <?php echo htmlspecialchars($animeDetails['rated']); ?>/10</span>
                        <p><?php echo htmlspecialchars($animeDetails['description']); ?></p>
                        <div class="link-buttons">
                            <a href="?id=<?php echo $animeId; ?>&playing=true&part=1" class="watch-button">Megtekintés</a>
                            <or>—</or>
                            <a href="https://www.youtube.com/embed/<?php echo htmlspecialchars(basename($animeDetails['trailer'])); ?>" target="_blank" class="watch-button">Előzetes</a>
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="anime_id" value="<?php echo $animeId; ?>">
                            <?php if ($isFavourite): ?>
                                <button type="submit" style="border: none; outline: none; cursor: pointer;" name="add_to_favorites" class="favourite-button">Kedvencekből törlés</button>
                            <?php else: ?>
                                <button type="submit" style="border: none; outline: none; cursor: pointer;" name="add_to_favorites" class="favourite-button">Kedvencekhez adás</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="statics">
            <div class="table-container">
                <div class="table-title">Statisztika</div>
                <table>
                    <tr>
                        <th>Megnevezés</th>
                        <th>Adat</th>
                    </tr>
                    <tr>
                        <td>MEGTEKINTÉS</td>
                        <td><?php echo htmlspecialchars($animeDetails['watched']); ?></td>
                    </tr>
                    <tr>
                        <td>TÍPUS</td>
                        <td>Sorozat</td>
                    </tr>
                    <tr>
                        <td>KIADÁS</td>
                        <td><?php echo htmlspecialchars($animeDetails['created_at']); ?></td>
                    </tr>
                    <tr>
                        <td>STÚDIÓ</td>
                        <td><?php echo htmlspecialchars($animeDetails['studio']); ?></td>
                    </tr>
                    <tr>
                        <td>STÁTUSZ</td>
                        <td><?php echo htmlspecialchars($animeDetails['status']); ?></td>
                    </tr>
                    <tr>
                        <td>BESOROLÁS</td>
                        <td><?php echo htmlspecialchars($animeDetails['agerestriction']); ?>+</td>
                    </tr>
                    <tr>
                        <td>RÉSZEK</td>
                        <td><?php echo htmlspecialchars($animeDetails['ep']); ?></td>
                    </tr>
                    <tr>
                        <td>FORDÍTÓ</td>
                        <td><?php echo htmlspecialchars($animeDetails['translator']); ?></td>
                    </tr>
                    <tr>
                        <td>FELTÖLTVE</td>
                        <td><?php echo htmlspecialchars($animeDetails['uploaded_at']); ?></td>
                    </tr>
                    <tr>
                        <td>LINKEK</td>
                        <td><a href="<?php echo htmlspecialchars($animeDetails['animelist']); ?>" target="_blank">MAL</a></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="other2">
        <div class="divider-container">
            <div class="divider">
                <span>Egyéb</span>
            </div>
        </div>
        <h2 class="trailer-text">Előzetes</h2>
    <div class="video-container trailer-player">
        <?php if (!empty($animeDetails['trailer'])): ?>
            <iframe
                width="50%"
                height="400"
                src="https://www.youtube.com/embed/<?php echo htmlspecialchars(basename($animeDetails['trailer'])); ?>"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen>
            </iframe>
        <?php else: ?>
            <p>Nincs elérhető előzetes.</p>
        <?php endif; ?>
    </div>
    </div>
    <h2 class="trailer-text">Kommentek</h2>
<div class="comment-section">
    <div class="own-comment">
        <form method="POST" action="">
            <textarea name="comment" class="commentarea" placeholder="Írd ide a hozzászólásod..." required maxlength="72"></textarea>
            <button type="submit" name="submit_comment"><i class="fa-solid fa-message"></i></button>
        </form>
    </div>
    <div class="comments">
        <?php if (!empty($comments)): ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <a href="../settings/profile.php?id=<?php echo htmlspecialchars($comment['user_id']); ?>">
                        <img src="../profiles/<?php echo htmlspecialchars($comment['username']); ?>/default.png" alt="">
                    </a>
                    <div class="comment-texts">
                        <p class="commenter-name"><?php echo htmlspecialchars($comment['username']);  ?> <small style="font-weight: normal; font-size: 12px">(#<?php echo htmlspecialchars($comment['id']);  ?>)</small></p>
                        <p class="commenter-velemeny"><?php echo htmlspecialchars($comment['comment']); ?></p>
                    </div>
                    <?php if ($isLoggedIn && $comment['user_id'] === $_SESSION['user_id']): ?>
                        <form method="POST" action="" style="display: block;">
                            <input type="hidden" name="comment_id" value="<?php echo htmlspecialchars($comment['id']); ?>">
                            <button type="submit" name="delete_comment" class="delete-comment" style="margin-left: 15px;">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center;">Nincsenek hozzászólások.</p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
<script src="../files/js/etc/dropdown.js"></script>
</body>
</html>