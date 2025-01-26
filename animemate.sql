-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Jan 25. 23:11
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `animemate`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `animes`
--

CREATE TABLE `animes` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `eng_name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `ep` int(11) NOT NULL,
  `watched` varchar(255) NOT NULL DEFAULT '0',
  `created_at` date NOT NULL,
  `studio` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `agerestriction` int(11) NOT NULL,
  `translator` varchar(255) NOT NULL,
  `uploaded_at` date NOT NULL,
  `animelist` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `weekly` tinyint(1) NOT NULL DEFAULT 0,
  `rated` int(11) NOT NULL DEFAULT 0,
  `trailer` varchar(255) NOT NULL,
  `recommended` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `animes`
--

INSERT INTO `animes` (`id`, `name`, `eng_name`, `description`, `ep`, `watched`, `created_at`, `studio`, `status`, `agerestriction`, `translator`, `uploaded_at`, `animelist`, `image`, `weekly`, `rated`, `trailer`, `recommended`) VALUES
(1, 'Tokyo Ghoul', 'Tokyo Ghoul', 'Egy világban, ahol a ghoulok, emberhúst fogyasztó lények, az emberek között élnek, Ken Kaneki félig ghoul lesz egy tragikus esemény után. Új identitásával küszködve kell navigálnia a ghoulok és emberek veszélyes világában.', 12, '7', '2014-07-04', 'Studio Pierrot', 'Befejezett', 18, 'mate', '2025-01-24', 'https://myanimelist.net/anime/22319/Tokyo_Ghoul', 'https://cdn.myanimelist.net/images/anime/5/64449.jpg', 1, 4, 'https://www.youtube.com/embed/7aMOurgDB-o?si=orJIyHImfW110_pW', 1),
(2, 'Tokyo Ghoul √A', 'Tokyo Ghoul √A', 'Ken Kaneki továbbra is küzd ghoul identitásával, miközben megpróbálja megvédeni barátait. A ghoulok és emberek közötti feszültség fokozódik, ami erőszakos konfliktushoz vezet.', 12, '0', '2015-01-09', 'Studio Pierrot', 'Befejezett', 18, 'mate', '2025-01-24', 'https://myanimelist.net/anime/27899/Tokyo_Ghoul_√A', 'https://cdn.myanimelist.net/images/anime/10/71772.jpg', 0, 9, 'https://www.youtube.com/embed/qM8wxM_mcRw?si=AC-52HXCE44SScUh', 0),
(3, 'Tokyo Ghoul:re', 'Tokyo Ghoul:re', 'Két évvel a Tokyo Ghoul √A eseményei után, Ken Kaneki, most már Haise Sasaki néven, nyomozóként dolgozik a CCG-nél. Szembenéz múltjával és az identitásával kapcsolatos igazsággal.', 12, '0', '2018-04-03', 'Studio Pierrot', 'Befejezett', 18, 'mate', '2025-01-24', 'https://myanimelist.net/anime/36511/Tokyo_Ghoul_re', 'https://cdn.myanimelist.net/images/anime/1063/95086.jpg', 0, 7, 'https://www.youtube.com/embed/kXyRLrPWU_E?si=IhqelVeHWDOefo7z', 0),
(4, 'Tokyo Ghoul:re 2nd Season', 'Tokyo Ghoul:re 2nd Season', 'Haise Sasaki és csapata új fenyegetéseknek néz szembe, miközben a ghoulok és emberek közötti konfliktus fokozódik. Ken Kanekinek el kell döntenie, hol vannak a lojalitásai.', 12, '0', '2018-10-09', 'Studio Pierrot', 'Befejezett', 18, 'mate', '2025-01-24', 'https://myanimelist.net/anime/37987/Tokyo_Ghoul_re_2nd_Season', 'https://cdn.myanimelist.net/images/anime/1825/110716.jpg', 0, 6, 'https://www.youtube.com/embed/K55ktT_rxVg?si=Vtr07_MHqzojRmY0', 0),
(5, 'Youkoso Jitsuryoku Shijou Shugi no Kyoushitsu e', 'Classroom of the Elite', 'Ayanokouji Kiyotaka beiratkozik a presztízsű Koudo Ikusei Középiskolába, ahol a diákokat képességeik alapján rangsorolják. Az iskola kegyetlen környezetében kell navigálnia, miközben elrejti valódi képességeit.', 12, '0', '2017-07-12', 'Lerche', 'Befejezett', 16, 'mate', '2025-01-24', 'https://myanimelist.net/anime/35507/Youkoso_Jitsuryoku_Shijou_Shugi_no_Kyoushitsu_e', 'https://cdn.myanimelist.net/images/anime/5/86830.jpg', 0, 2, 'https://www.youtube.com/embed/RTvdxGyWV6c?si=hXzYMQ8O3BkglJz-', 0),
(6, 'Youkoso Jitsuryoku Shijou Shugi no Kyoushitsu e 2nd Season', 'Classroom of the Elite II', 'Ayanokouji Kiyotaka és osztálytársai új kihívásokkal néznek szembe, miközben a legjobb rangsorolásért versenyeznek. Titkok és árulások kerülnek napvilágra, ahogy a tét egyre nagyobb lesz.', 13, '0', '2022-07-04', 'Lerche', 'Befejezett', 16, 'mate', '2025-01-24', 'https://myanimelist.net/anime/48926/Youkoso_Jitsuryoku_Shijou_Shugi_no_Kyoushitsu_e_2nd_Season', 'https://cdn.myanimelist.net/images/anime/1010/124180.jpg', 0, 6, 'https://www.youtube.com/embed/LkqBsJuEids?si=jhE-vxkA-w5Q3OCF', 0),
(7, 'Youkoso Jitsuryoku Shijou Shugi no Kyoushitsu e 3rd Season', 'Classroom of the Elite III', 'A Classroom of the Elite harmadik évada folytatja Ayanokouji Kiyotaka és osztálytársai történetét, akik még intenzívebb kihívásokkal néznek szembe és mélyebb titkokat tárt fel.', 13, '0', '2024-01-03', 'Lerche', 'Aktív', 16, 'mate', '2025-01-24', 'https://myanimelist.net/anime/53446/Youkoso_Jitsuryoku_Shijou_Shugi_no_Kyoushitsu_e_3rd_Season', 'https://cdn.myanimelist.net/images/anime/1332/139318.jpg', 0, 4, 'https://www.youtube.com/embed/6Gx4pQ14HLk?si=RvdzV0NWa3nvuw85', 0),
(8, 'High School DxD', 'High School DxD', 'Issei Hyoudou, egy perverz középiskolás diák, egy bukott angyal által megölik, majd Rias Gremory, egy démon, feltámasztja. Csatlakozik a démon klánjához és természetfeletti fenyegetésekkel harcol.', 12, '0', '2012-01-06', 'TNK', 'Befejezett', 18, 'mate', '2025-01-24', 'https://myanimelist.net/anime/11617/High_School_DxD', 'https://cdn.myanimelist.net/images/anime/1331/111940.jpg', 1, 2, 'https://www.youtube.com/embed/aUwfuTHZlD0?si=Js6xM6mbsnCkzRYM', 0),
(9, 'High School DxD New', 'High School DxD New', 'Issei és barátai új kihívásokkal néznek szembe, miközben megvédik iskolájukat és az emberi világot a természetfeletti fenyegetésektől. A Gremory klán tagjai közötti kötelékek erősödnek.', 12, '0', '2013-07-07', 'TNK', 'Befejezett', 18, 'mate', '2025-01-24', 'https://myanimelist.net/anime/15451/High_School_DxD_New', 'https://cdn.myanimelist.net/images/anime/12/47729.jpg', 0, 7, 'https://www.youtube.com/embed/csKMi3tP0mw?si=xGlfFfHhUeNY1l_o', 0),
(10, 'High School DxD BorN', 'High School DxD BorN', 'Issei és barátai új ellenséggel, a Khaos Brigaddel néznek szembe, miközben továbbra is megvédik iskolájukat és az emberi világot. A tét nagyobb, mint valaha.', 12, '0', '2015-04-04', 'TNK', 'Befejezett', 18, 'mate', '2025-01-24', 'https://myanimelist.net/anime/24703/High_School_DxD_BorN', 'https://cdn.myanimelist.net/images/anime/12/73642.jpg', 0, 8, 'https://www.youtube.com/embed/qo8FHPPdca8?si=s6_dzno8GYezOTWL', 0),
(11, 'High School DxD Hero', 'High School DxD Hero', 'Issei és barátai a legnehezebb kihívásokkal néznek szembe, miközben hatalmas ellenségekkel harcolnak és sötét titkokat tárt fel a természetfeletti világról.', 13, '0', '2018-04-10', 'Passione', 'Befejezett', 18, 'mate', '2025-01-24', 'https://myanimelist.net/anime/34281/High_School_DxD_Hero', 'https://cdn.myanimelist.net/images/anime/1189/93528.jpg', 1, 8, 'https://www.youtube.com/embed/wrNvWEEHzTk?si=LrR5Vq1Jh-dIMv6o', 0),
(12, 'Kimetsu no Yaiba', 'Demon Slayer: Kimetsu no Yaiba', 'Tanjiro Kamado démonölővé válik, miután a családját démonok mészárolják le, és húgát, Nezukot démonná változtatják. Útra kel, hogy bosszút álljon családjáért és meggyógyítsa húgát.', 26, '0', '2019-04-06', 'ufotable', 'Befejezett', 16, 'mate', '2025-01-24', 'https://myanimelist.net/anime/38000/Kimetsu_no_Yaiba', 'https://cdn.myanimelist.net/images/anime/1286/99889.jpg', 1, 8, 'https://www.youtube.com/embed/pmanD_s7G3U?si=jaDEK28MsjOntheN', 0),
(13, 'Shingeki no Kyojin', 'Attack on Titan', 'Eren Yeager és barátai csatlakoznak a harchoz a Titánok ellen, óriási humanoid lények ellen, akik az emberiséget a kihalás szélére sodorták. Sötét titkokat tárt fel a Titánokról és világukról.', 25, '0', '2013-04-07', 'Wit Studio', 'Befejezett', 18, 'mate', '2025-01-24', 'https://myanimelist.net/anime/16498/Shingeki_no_Kyojin', 'https://cdn.myanimelist.net/images/anime/10/47347.jpg', 1, 4, 'https://www.youtube.com/embed/3xNH23QkNpk?si=W_eOfKk0XlhSQdXj', 0),
(14, 'Boku no Hero Academia', 'My Hero Academia', 'Midoriya Izuku, egy fiú, aki szupererők nélkül született egy olyan világban, ahol ez a norma, álmodik arról, hogy hős lesz. Örökli a legnagyobb hős erejét és beiratkozik egy presztízsű hősakadémiára.', 13, '0', '2016-04-03', 'Bones', 'Befejezett', 13, 'mate', '2025-01-24', 'https://myanimelist.net/anime/31964/Boku_no_Hero_Academia', 'https://cdn.myanimelist.net/images/anime/10/78745.jpg', 0, 3, 'https://www.youtube.com/embed/-77UEct0cZM?si=C3K6qcuoHguck21M', 0),
(15, 'Sword Art Online', 'Sword Art Online', 'Kirito és más játékosok egy virtuális valóságú MMORPG-ban ragadnak, ahol a játékban történő halál valódi halált jelent. Ki kell tisztítaniuk a játékot, hogy megszabaduljanak.', 25, '0', '2012-07-08', 'A-1 Pictures', 'Befejezett', 13, 'mate', '2025-01-24', 'https://myanimelist.net/anime/11757/Sword_Art_Online', 'https://cdn.myanimelist.net/images/anime/11/39717.jpg', 0, 4, 'https://www.youtube.com/embed/1oOBjyOKu2o?si=OKKFrhZ4rDevjshU', 0);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `anime_parts`
--

CREATE TABLE `anime_parts` (
  `id` int(11) NOT NULL,
  `part` int(11) NOT NULL,
  `anime_id` int(11) NOT NULL,
  `link` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `anime_parts`
--

INSERT INTO `anime_parts` (`id`, `part`, `anime_id`, `link`) VALUES
(1, 1, 1, 'https://1a-1791.com/video/s8/2/A/C/R/g/ACRgq.haa.mp4'),
(2, 2, 1, 'https://1a-1791.com/video/s8/2/l/C/R/g/lCRgq.haa.mp4');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `comments`
--

CREATE TABLE `comments` (
  `id` int(255) NOT NULL,
  `user_id` int(255) NOT NULL,
  `anime_id` int(255) NOT NULL,
  `comment` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `comments`
--

INSERT INTO `comments` (`id`, `user_id`, `anime_id`, `comment`) VALUES
(1, 5, 8, 'jo lett'),
(2, 5, 1, 'ez is jo');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `recommended_animes`
--

CREATE TABLE `recommended_animes` (
  `id` int(11) NOT NULL,
  `recommended_by` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `eng_name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `ep` int(11) NOT NULL,
  `created_at` varchar(255) NOT NULL,
  `studio` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `agerestriction` int(11) NOT NULL,
  `animelist` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `trailer` varchar(255) NOT NULL,
  `submitted` varchar(255) NOT NULL,
  `dontes` varchar(255) NOT NULL DEFAULT 'Várakozás...'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `recommended_animes`
--

INSERT INTO `recommended_animes` (`id`, `recommended_by`, `name`, `eng_name`, `description`, `ep`, `created_at`, `studio`, `status`, `agerestriction`, `animelist`, `image`, `trailer`, `submitted`, `dontes`) VALUES
(1, '', '', '', '32', 0, '', 'asd', '12', 0, '0', '', '12312', '', 'Várakozás...'),
(2, 'helomate3', '', '', '32', 0, '', 'asd', '12', 0, '0', '', '12312', '', 'Várakozás...'),
(3, 'helomate3', '', '', '32', 0, '', 'asd', '12', 0, '0', '', '12312', '', 'Várakozás...'),
(4, 'helomate3', 'asd', 'asd', 'asd', 12, '111111-11-11', 'asd', 'asd', 123, '0', 'asd', 'asd', '', 'Várakozás...'),
(5, 'helomate3', 'asd', 'asd', '12', 12, '111111-11-11', 'asd', '12', 12, '12', '12', '12', '2025-01-23 21:29:53', 'Várakozás...'),
(6, 'helomate3', 'asd', 'asd', '12', 12, '111111-11-11', 'asd', '12', 12, '12', '12', '12', '2025-01-25 21:32:13', 'Elfogadva');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `recommended_parts`
--

CREATE TABLE `recommended_parts` (
  `id` int(11) NOT NULL,
  `recommended_by` varchar(255) NOT NULL,
  `to_anime` int(255) NOT NULL,
  `part` int(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `submitted` varchar(255) NOT NULL,
  `dontes` varchar(255) NOT NULL DEFAULT 'Várakozás...'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `recommended_parts`
--

INSERT INTO `recommended_parts` (`id`, `recommended_by`, `to_anime`, `part`, `link`, `submitted`, `dontes`) VALUES
(1, 'helomate3', 1, 12, '12', '2025-01-25 21:43:44', 'Elutasítva');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `tag` varchar(255) NOT NULL,
  `to_anime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `tags`
--

INSERT INTO `tags` (`id`, `tag`, `to_anime`) VALUES
(1, 'Dráma', 1),
(2, 'Akció', 1),
(3, 'Horror', 1),
(4, 'Dráma', 2),
(5, 'Akció', 2),
(6, 'Pszichológiai', 2),
(7, 'Dráma', 3),
(8, 'Akció', 3),
(9, 'Rejtély', 3),
(10, 'Dráma', 4),
(11, 'Akció', 4),
(12, 'Fantasy', 4),
(13, 'Dráma', 5),
(14, 'Pszichológiai', 5),
(15, 'Iskola', 5),
(16, 'Dráma', 6),
(17, 'Pszichológiai', 6),
(18, 'Iskola', 6),
(19, 'Dráma', 7),
(20, 'Pszichológiai', 7),
(21, 'Iskola', 7),
(22, 'Ecchi', 8),
(23, 'Hárem', 8),
(24, 'Akció', 8),
(25, 'Ecchi', 9),
(26, 'Hárem', 9),
(27, 'Akció', 9),
(28, 'Ecchi', 10),
(29, 'Hárem', 10),
(30, 'Akció', 10),
(31, 'Ecchi', 11),
(32, 'Hárem', 11),
(33, 'Akció', 11),
(34, 'Akció', 12),
(35, 'Fantasy', 12),
(36, 'Dráma', 12),
(37, 'Akció', 13),
(38, 'Fantasy', 13),
(39, 'Dráma', 13),
(40, 'Akció', 14),
(41, 'Szuperhős', 14),
(42, 'Iskola', 14),
(43, 'Akció', 15),
(44, 'Fantasy', 15),
(45, 'Romantika', 15);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `nickname` varchar(255) NOT NULL,
  `bio` varchar(255) NOT NULL DEFAULT 'Nincs információ.',
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `favourites` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]',
  `last_three_watched` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`last_three_watched`)),
  `role` varchar(255) NOT NULL,
  `registered_at` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `users`
--

INSERT INTO `users` (`id`, `username`, `nickname`, `bio`, `password`, `email`, `favourites`, `last_three_watched`, `role`, `registered_at`) VALUES
(1, 'asd', '', 'Nincs információ.', '$2y$10$LptmVeSl/BhszDz6SmA2z.Wgavy9Kvtt3EqHm0CX.2VeKXlIiKM.G', 'asd@asd.com', '[1,11]', '', '', ''),
(2, 'csa', 'sa', 'Nincs információ.', '$2y$10$dgp1fUi6aXQoQJ/KXxNn8ewzato6e6btbtCHG.zvzlYKOf2.Xc1UW', 'csa@gmail.com', '[]', '', '', ''),
(3, 'helomate', '', 'Nincs információ.', '$2y$10$mMVWWhv2m3KnIkiwrAOfbuQsQCn/eLYWkybHK2qphmTVAlzn.WUky', '2mate844@gmail.com', '[]', '', '', '2025-01-25 13:52:21'),
(4, 'helomate2', 'helomate26325', 'Nincs információ.', '$2y$10$3cJPmkjxBoaZB3.7VtP/Purb3nVyYBXQBdqXJgI3YU3FIsHSBI7iO', '3mate844@gmail.com', '[]', '', '', '2025-01-25 13:58:26'),
(5, 'helomate3', 'mate', 'cssssssssss', '$2y$10$sy8WmhoUyzmgZ7pbJOmjdeR54wIMCnDSiKfc217BZFnOwgTqfzuFS', 'asd123@gmail.xn--com-w1a', '[1]', '[13,1,2]', 'admin', '2025-01-25 14:17:12');

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `animes`
--
ALTER TABLE `animes`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `anime_parts`
--
ALTER TABLE `anime_parts`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `recommended_animes`
--
ALTER TABLE `recommended_animes`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `recommended_parts`
--
ALTER TABLE `recommended_parts`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `animes`
--
ALTER TABLE `animes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT a táblához `anime_parts`
--
ALTER TABLE `anime_parts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT a táblához `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT a táblához `recommended_animes`
--
ALTER TABLE `recommended_animes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT a táblához `recommended_parts`
--
ALTER TABLE `recommended_parts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT a táblához `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT a táblához `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
