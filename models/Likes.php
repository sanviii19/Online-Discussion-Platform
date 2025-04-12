<?php
require_once __DIR__ . '/../config/db.php';

class Like {
    public static function add($userId, $contentType, $contentId) {
        global $pdo;
        try {
            // First check if the like already exists
            if (self::exists($userId, $contentType, $contentId)) {
                return false;
            }

            $stmt = $pdo->prepare("INSERT INTO likes (user_id, content_type, content_id) 
                                  VALUES (?, ?, ?)");
            $result = $stmt->execute([$userId, $contentType, $contentId]);
            
            if ($result) {
                return $pdo->lastInsertId();
            } else {
                error_log("Like addition failed: " . json_encode($stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            error_log("Like addition exception: " . $e->getMessage());
            return false;
        }
    }

    public static function remove($userId, $contentType, $contentId) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("DELETE FROM likes 
                                  WHERE user_id = ? 
                                  AND content_type = ? 
                                  AND content_id = ?");
            return $stmt->execute([$userId, $contentType, $contentId]);
        } catch (PDOException $e) {
            error_log("Remove like error: " . $e->getMessage());
            return false;
        }
    }

    public static function exists($userId, $contentType, $contentId) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT 1 FROM likes 
                                  WHERE user_id = ? 
                                  AND content_type = ? 
                                  AND content_id = ?");
            $stmt->execute([$userId, $contentType, $contentId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Check like exists error: " . $e->getMessage());
            return false;
        }
    }

    public static function getCount($contentType, $contentId) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM likes 
                                  WHERE content_type = ? 
                                  AND content_id = ?");
            $stmt->execute([$contentType, $contentId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['count'] : 0;
        } catch (PDOException $e) {
            error_log("Get like count error: " . $e->getMessage());
            return 0;
        }
    }

    public static function getUsersWhoLiked($contentType, $contentId) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT u.id, u.name FROM likes l
                                  JOIN users u ON l.user_id = u.id
                                  WHERE l.content_type = ? 
                                  AND l.content_id = ?");
            $stmt->execute([$contentType, $contentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get users who liked error: " . $e->getMessage());
            return [];
        }
    }
}