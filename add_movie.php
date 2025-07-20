<?php
// ===== add_movie.php =====
ini_set('display_errors', 0);
error_reporting(0);

require 'config/config.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $title = trim($data['title'] ?? '');
    $tags = $data['tags'] ?? '';
    $originalTitle = trim($data['original_title'] ?? '');
    $releaseYear = (int)($data['release_year'] ?? 0) ?: null;
    $personalRating = isset($data['personal_rating']) ? (float)$data['personal_rating'] : null;
    
    if (empty($title)) {
        throw new Exception('Titel ist erforderlich');
    }
    
    $userId = $_SESSION['user_id'] ?? 1;
    
    $pdo->beginTransaction();
    
    // Film hinzufügen
    $stmt = $pdo->prepare("
        INSERT INTO movies (title, original_title, release_year, personal_rating, added_by, watch_status) 
        VALUES (?, ?, ?, ?, ?, 'not_watched')
    ");
    $stmt->execute([$title, $originalTitle ?: null, $releaseYear, $personalRating, $userId]);
    $movieId = $pdo->lastInsertId();
    
    // Tags verarbeiten
    if (!empty($tags)) {
        $tagArray = is_string($tags) ? array_map('trim', explode(',', $tags)) : $tags;
        $tagArray = array_unique(array_filter($tagArray));
        
        foreach ($tagArray as $tagName) {
            // Tag erstellen oder finden
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '-', $tagName));
            $slug = trim($slug, '-');
            
            $stmt = $pdo->prepare("
                INSERT INTO tags (name, slug) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)
            ");
            $stmt->execute([$tagName, $slug]);
            $tagId = $pdo->lastInsertId();
            
            // Tag mit Film verknüpfen
            $stmt = $pdo->prepare("INSERT IGNORE INTO movie_tags (movie_id, tag_id) VALUES (?, ?)");
            $stmt->execute([$movieId, $tagId]);
        }
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Film erfolgreich hinzugefügt',
        'id' => $movieId,
        'data' => ['id' => $movieId, 'title' => $title]
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

===== update_movie.php =====
<?php
ini_set('display_errors', 0);
error_reporting(0);

require 'config/config.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $id = (int)($data['id'] ?? 0);
    $title = trim($data['title'] ?? '');
    $count = (int)($data['count'] ?? 0);
    $date = $data['date'] ?? null;
    $tags = $data['tags'] ?? [];
    $personalRating = isset($data['personal_rating']) ? (float)$data['personal_rating'] : null;
    
    if ($id <= 0) throw new Exception('Ungültige Film-ID');
    if (empty($title)) throw new Exception('Titel ist erforderlich');
    
    $userId = $_SESSION['user_id'] ?? 1;
    
    $pdo->beginTransaction();
    
    // Film aktualisieren
    $stmt = $pdo->prepare("
        UPDATE movies 
        SET title = ?, personal_rating = ?, watch_status = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $watchStatus = $count > 0 ? 'watched' : 'not_watched';
    $stmt->execute([$title, $personalRating, $watchStatus, $id]);
    
    // Watch Logs aktualisieren
    $stmt = $pdo->prepare("DELETE FROM watch_logs WHERE movie_id = ?");
    $stmt->execute([$id]);
    
    if ($count > 0) {
        $watchDate = $date ?: date('Y-m-d');
        $stmt = $pdo->prepare("
            INSERT INTO watch_logs (movie_id, user_id, watched_at) 
            VALUES (?, ?, ?)
        ");
        for ($i = 0; $i < $count; $i++) {
            $stmt->execute([$id, $userId, $watchDate]);
        }
    }
    
    // Tags aktualisieren
    $stmt = $pdo->prepare("DELETE FROM movie_tags WHERE movie_id = ?");
    $stmt->execute([$id]);
    
    if (!empty($tags) && is_array($tags)) {
        foreach ($tags as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName)) continue;
            
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '-', $tagName));
            $slug = trim($slug, '-');
            
            $stmt = $pdo->prepare("
                INSERT INTO tags (name, slug) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)
            ");
            $stmt->execute([$tagName, $slug]);
            $tagId = $pdo->lastInsertId();
            
            $stmt = $pdo->prepare("INSERT IGNORE INTO movie_tags (movie_id, tag_id) VALUES (?, ?)");
            $stmt->execute([$id, $tagId]);
        }
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Film erfolgreich aktualisiert',
        'data' => ['id' => $id, 'title' => $title, 'count' => $count, 'date' => $date]
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

===== rate_movie.php =====
<?php
ini_set('display_errors', 0);
error_reporting(0);

require 'config/config.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
    exit;
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $movieId = (int)($data['id'] ?? 0);
    $vote = trim($data['type'] ?? '');
    $userId = (int)$_SESSION['user_id'];
    
    if ($movieId <= 0) throw new Exception('Ungültige Film-ID');
    if (!in_array($vote, ['like', 'neutral', 'dislike'])) throw new Exception('Ungültiger Vote-Typ');
    
    // Vote einfügen oder aktualisieren
    $stmt = $pdo->prepare("
        INSERT INTO movie_votes (movie_id, user_id, vote) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        vote = VALUES(vote), 
        updated_at = NOW()
    ");
    $stmt->execute([$movieId, $userId, $vote]);
    
    // Aktuelle Zähler abrufen
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN vote = 'like' THEN 1 ELSE 0 END) as likes,
            SUM(CASE WHEN vote = 'neutral' THEN 1 ELSE 0 END) as neutral,
            SUM(CASE WHEN vote = 'dislike' THEN 1 ELSE 0 END) as dislikes
        FROM movie_votes 
        WHERE movie_id = ?
    ");
    $stmt->execute([$movieId]);
    $counts = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => 'Bewertung gespeichert',
        'likes' => (int)$counts['likes'],
        'neutral' => (int)$counts['neutral'],
        'dislikes' => (int)$counts['dislikes']
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

===== delete_movie.php =====
<?php
ini_set('display_errors', 0);
error_reporting(0);

require 'config/config.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
    exit;
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $id = (int)($data['id'] ?? 0);
    
    if ($id <= 0) throw new Exception('Ungültige Film-ID');
    
    // Film löschen (Cascade löscht automatisch verknüpfte Daten)
    $stmt = $pdo->prepare("DELETE FROM movies WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Film nicht gefunden');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Film erfolgreich gelöscht',
        'data' => ['id' => $id]
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

===== get_tags.php =====
<?php
ini_set('display_errors', 0);
error_reporting(0);

require 'config/config.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $stmt = $pdo->query("
        SELECT name, color, usage_count 
        FROM tags 
        WHERE usage_count > 0 
        ORDER BY usage_count DESC, name ASC
    ");
    $tags = $stmt->fetchAll();
    
    $tagList = [];
    foreach ($tags as $tag) {
        $tagList[] = [
            'value' => $tag['name'],
            'color' => $tag['color'] ?? '#3498db',
            'count' => (int)$tag['usage_count']
        ];
    }
    
    echo json_encode($tagList);
    
} catch (Exception $e) {
    echo json_encode([]);
}
?>