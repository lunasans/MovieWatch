-- MovieWatch - Moderne Datenbankstruktur
-- Version 2.0 - Komplett neu strukturiert

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

-- Alte Tabellen löschen (falls vorhanden)
-- DROP TABLE IF EXISTS `movie_tags`;
-- DROP TABLE IF EXISTS `movie_votes`;
-- DROP TABLE IF EXISTS `watch_logs`;
-- DROP TABLE IF EXISTS `user_tokens`;
-- DROP TABLE IF EXISTS `user_sessions`;
-- DROP TABLE IF EXISTS `movies`;
-- DROP TABLE IF EXISTS `tags`;
-- DROP TABLE IF EXISTS `users`;

-- ====================================
-- BENUTZER-VERWALTUNG
-- ====================================

-- Users Tabelle
CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_active` (`is_active`),
  KEY `idx_last_login` (`last_login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Tokens (Remember Me)
CREATE TABLE `user_tokens` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `token` char(64) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `fk_user_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Sessions (aktive Sessions)
CREATE TABLE `user_sessions` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `session_id` varchar(128) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_last_activity` (`last_activity`),
  CONSTRAINT `fk_user_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- FILM-VERWALTUNG
-- ====================================

-- Movies Tabelle
CREATE TABLE `movies` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `original_title` varchar(255) DEFAULT NULL,
  `release_year` year(4) DEFAULT NULL,
  `runtime_minutes` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `poster_url` varchar(500) DEFAULT NULL,
  `imdb_id` varchar(20) DEFAULT NULL,
  `tmdb_id` int(11) DEFAULT NULL,
  `rating_imdb` decimal(3,1) DEFAULT NULL,
  `personal_rating` decimal(3,1) DEFAULT NULL,
  `is_favorite` tinyint(1) NOT NULL DEFAULT 0,
  `watch_status` enum('not_watched','watched','want_to_watch','watching') NOT NULL DEFAULT 'not_watched',
  `added_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_title` (`title`),
  KEY `idx_release_year` (`release_year`),
  KEY `idx_watch_status` (`watch_status`),
  KEY `idx_is_favorite` (`is_favorite`),
  KEY `idx_added_by` (`added_by`),
  KEY `idx_imdb_id` (`imdb_id`),
  KEY `idx_tmdb_id` (`tmdb_id`),
  CONSTRAINT `fk_movies_added_by` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  FULLTEXT KEY `ft_search` (`title`, `original_title`, `description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- TAGS & KATEGORIEN
-- ====================================

-- Tags Tabelle
CREATE TABLE `tags` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `color` varchar(7) DEFAULT '#3498db',
  `description` text DEFAULT NULL,
  `usage_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_usage_count` (`usage_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Movie-Tag Verknüpfungen
CREATE TABLE `movie_tags` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `movie_id` int(11) UNSIGNED NOT NULL,
  `tag_id` int(11) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_movie_tag` (`movie_id`, `tag_id`),
  KEY `movie_id` (`movie_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `fk_movie_tags_movie` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_movie_tags_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- BEWERTUNGEN & AKTIVITÄTEN
-- ====================================

-- Movie Votes (Like/Dislike/Neutral)
CREATE TABLE `movie_votes` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `movie_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `vote` enum('like','neutral','dislike') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_movie_vote` (`movie_id`, `user_id`),
  KEY `movie_id` (`movie_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_vote` (`vote`),
  CONSTRAINT `fk_movie_votes_movie` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_movie_votes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Watch Logs (Wann wurde ein Film geschaut)
CREATE TABLE `watch_logs` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `movie_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `watched_at` date NOT NULL,
  `watch_time_minutes` int(11) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `rating` decimal(3,1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `movie_id` (`movie_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_watched_at` (`watched_at`),
  KEY `idx_rating` (`rating`),
  CONSTRAINT `fk_watch_logs_movie` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_watch_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- ERWEITERTE FEATURES
-- ====================================

-- Movie Collections/Listen
CREATE TABLE `collections` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` int(11) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_is_public` (`is_public`),
  CONSTRAINT `fk_collections_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Collection Movies
CREATE TABLE `collection_movies` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `collection_id` int(11) UNSIGNED NOT NULL,
  `movie_id` int(11) UNSIGNED NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_collection_movie` (`collection_id`, `movie_id`),
  KEY `collection_id` (`collection_id`),
  KEY `movie_id` (`movie_id`),
  KEY `idx_sort_order` (`sort_order`),
  CONSTRAINT `fk_collection_movies_collection` FOREIGN KEY (`collection_id`) REFERENCES `collections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_collection_movies_movie` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Aktivitätslogs
CREATE TABLE `activity_logs` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) UNSIGNED DEFAULT NULL,
  `details` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_entity` (`entity_type`, `entity_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_activity_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- SAMPLE DATA
-- ====================================

-- Standard Admin User einfügen
INSERT INTO `users` (`username`, `email`, `password_hash`, `is_active`) VALUES
('admin', 'admin@moviewatch.local', '$2y$10$O2oqvtrLlaFRsdJYBG6b0utcypGm4lzUUUNrlsQMoDJi6F88/6Llm', 1);

-- Standard Tags
INSERT INTO `tags` (`name`, `slug`, `color`, `description`) VALUES
('Action', 'action', '#e74c3c', 'Action-Filme mit viel Spannung'),
('Drama', 'drama', '#9b59b6', 'Dramatische Filme mit emotionaler Tiefe'),
('Komödie', 'comedy', '#f39c12', 'Lustige Filme zum Entspannen'),
('Thriller', 'thriller', '#2c3e50', 'Spannende Filme die fesseln'),
('Horror', 'horror', '#8e44ad', 'Gruselfilme für Hartgesottene'),
('Sci-Fi', 'sci-fi', '#3498db', 'Science Fiction und Zukunftsvisionen'),
('Fantasy', 'fantasy', '#27ae60', 'Magische Welten und Abenteuer'),
('Dokumentation', 'documentary', '#34495e', 'Dokumentarfilme und Wissenswertes'),
('Animation', 'animation', '#ff6b8a', 'Animierte Filme für alle Altersgruppen'),
('Krimi', 'crime', '#16a085', 'Kriminalfilme und Detektivgeschichten');

-- Standard Collections
INSERT INTO `collections` (`name`, `description`, `is_public`, `created_by`) VALUES
('Favoriten', 'Meine absoluten Lieblingsfilme', 0, 1),
('Watchlist', 'Filme die ich noch schauen möchte', 0, 1),
('Klassiker', 'Zeitlose Filmklassiker', 1, 1);

-- ====================================
-- VIEWS FÜR BESSERE PERFORMANCE
-- ====================================

-- View: Movie Statistics
CREATE VIEW `view_movie_stats` AS
SELECT 
    m.id,
    m.title,
    m.release_year,
    m.watch_status,
    m.is_favorite,
    COUNT(DISTINCT wl.id) as watch_count,
    MAX(wl.watched_at) as last_watched,
    AVG(wl.rating) as avg_watch_rating,
    COUNT(DISTINCT CASE WHEN mv.vote = 'like' THEN mv.id END) as likes,
    COUNT(DISTINCT CASE WHEN mv.vote = 'neutral' THEN mv.id END) as neutral,
    COUNT(DISTINCT CASE WHEN mv.vote = 'dislike' THEN mv.id END) as dislikes,
    COUNT(DISTINCT mt.tag_id) as tag_count,
    m.created_at,
    m.updated_at
FROM movies m
LEFT JOIN watch_logs wl ON m.id = wl.movie_id
LEFT JOIN movie_votes mv ON m.id = mv.movie_id
LEFT JOIN movie_tags mt ON m.id = mt.movie_id
GROUP BY m.id;

-- View: User Statistics
CREATE VIEW `view_user_stats` AS
SELECT 
    u.id,
    u.username,
    COUNT(DISTINCT wl.movie_id) as movies_watched,
    COUNT(DISTINCT wl.id) as total_watches,
    COUNT(DISTINCT mv.id) as total_votes,
    COUNT(DISTINCT CASE WHEN mv.vote = 'like' THEN mv.id END) as likes_given,
    AVG(wl.rating) as avg_rating_given,
    MAX(wl.watched_at) as last_watch_date,
    u.created_at,
    u.last_login
FROM users u
LEFT JOIN watch_logs wl ON u.id = wl.user_id
LEFT JOIN movie_votes mv ON u.id = mv.user_id
GROUP BY u.id;

-- ====================================
-- TRIGGERS FÜR AUTOMATISCHE UPDATES
-- ====================================

-- Trigger: Tag Usage Count aktualisieren
DELIMITER $$
CREATE TRIGGER `update_tag_usage_count_insert` 
AFTER INSERT ON `movie_tags` 
FOR EACH ROW 
BEGIN
    UPDATE tags SET usage_count = usage_count + 1 WHERE id = NEW.tag_id;
END$$

CREATE TRIGGER `update_tag_usage_count_delete` 
AFTER DELETE ON `movie_tags` 
FOR EACH ROW 
BEGIN
    UPDATE tags SET usage_count = usage_count - 1 WHERE id = OLD.tag_id;
END$$
DELIMITER ;

-- ====================================
-- INDIZES FÜR PERFORMANCE
-- ====================================

-- Zusätzliche Performance-Indizes
CREATE INDEX `idx_movies_title_year` ON `movies` (`title`, `release_year`);
CREATE INDEX `idx_watch_logs_date_movie` ON `watch_logs` (`watched_at`, `movie_id`);
CREATE INDEX `idx_movie_votes_vote_movie` ON `movie_votes` (`vote`, `movie_id`);

SET FOREIGN_KEY_CHECKS = 1;

-- Performance-Einstellungen
SET SESSION query_cache_type = ON;
SET SESSION query_cache_size = 1048576;

-- ====================================
-- BACKUP & MAINTENANCE
-- ====================================

-- Event für automatische Bereinigung alter Sessions (falls Events aktiviert sind)
-- CREATE EVENT IF NOT EXISTS `cleanup_old_sessions`
-- ON SCHEDULE EVERY 1 DAY
-- DO DELETE FROM user_sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- CREATE EVENT IF NOT EXISTS `cleanup_expired_tokens`
-- ON SCHEDULE EVERY 1 HOUR
-- DO DELETE FROM user_tokens WHERE expires_at < NOW();