<?php
require_once __DIR__ . '/../config/db.php';

class Discussion {
    public static function create($groupId, $userId, $title, $content) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("INSERT INTO discussions (group_id, user_id, title, content) 
                                   VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([$groupId, $userId, $title, $content]);
            
            if ($result) {
                return $pdo->lastInsertId();
            } else {
                error_log("Discussion creation failed: " . json_encode($stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            error_log("Discussion creation exception: " . $e->getMessage());
            return false;
        }
    }

    public static function getById($discussionId) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT d.*, u.name as author_name 
                                  FROM discussions d 
                                  JOIN users u ON d.user_id = u.id 
                                  WHERE d.id = ?");
            $stmt->execute([$discussionId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get discussion error: " . $e->getMessage());
            return false;
        }
    }

    public static function getByGroupId($groupId) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT d.*, u.name as author_name, 
                                 (SELECT COUNT(*) FROM replies WHERE discussion_id = d.id) as reply_count,
                                 (SELECT COUNT(*) FROM likes WHERE content_type = 'discussion' AND content_id = d.id) as like_count
                                 FROM discussions d 
                                 JOIN users u ON d.user_id = u.id 
                                 WHERE d.group_id = ? 
                                 ORDER BY d.created_at DESC");
            $stmt->execute([$groupId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get discussions by group error: " . $e->getMessage());
            return [];
        }
    }

    public static function update($id, $title, $content) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("UPDATE discussions 
                                   SET title = ?, content = ? 
                                   WHERE id = ?");
            return $stmt->execute([$title, $content, $id]);
        } catch (PDOException $e) {
            error_log("Update discussion error: " . $e->getMessage());
            return false;
        }
    }

    public static function delete($id) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("DELETE FROM discussions WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Delete discussion error: " . $e->getMessage());
            return false;
        }
    }

    public static function isOwner($discussionId, $userId) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT 1 FROM discussions 
                                   WHERE id = ? AND user_id = ?");
            $stmt->execute([$discussionId, $userId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Check discussion owner error: " . $e->getMessage());
            return false;
        }
    }
}