<?php
require_once __DIR__ . '/../config/db.php';

class Reply {
    public static function create($discussionId, $userId, $content, $parentId = null) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("INSERT INTO replies (discussion_id, user_id, content, parent_id) 
                                   VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([$discussionId, $userId, $content, $parentId]);
            
            if ($result) {
                return $pdo->lastInsertId();
            } else {
                error_log("Reply creation failed: " . json_encode($stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            error_log("Reply creation exception: " . $e->getMessage());
            return false;
        }
    }

    public static function getById($replyId) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT r.*, u.name as author_name 
                                  FROM replies r 
                                  JOIN users u ON r.user_id = u.id 
                                  WHERE r.id = ?");
            $stmt->execute([$replyId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get reply error: " . $e->getMessage());
            return false;
        }
    }

    public static function getByDiscussionId($discussionId) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT r.*, u.name as author_name, 
                                 (SELECT COUNT(*) FROM likes WHERE content_type = 'reply' AND content_id = r.id) as like_count
                                 FROM replies r 
                                 JOIN users u ON r.user_id = u.id 
                                 WHERE r.discussion_id = ? 
                                 ORDER BY r.created_at ASC");
            $stmt->execute([$discussionId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get replies by discussion error: " . $e->getMessage());
            return [];
        }
    }

    public static function update($id, $content) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("UPDATE replies 
                                   SET content = ? 
                                   WHERE id = ?");
            return $stmt->execute([$content, $id]);
        } catch (PDOException $e) {
            error_log("Update reply error: " . $e->getMessage());
            return false;
        }
    }

    public static function delete($id) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("DELETE FROM replies WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Delete reply error: " . $e->getMessage());
            return false;
        }
    }
}