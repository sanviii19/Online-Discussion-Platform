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

    // Get files attached to a reply
    public static function getFiles($replyId) {
        require_once __DIR__ . '/File.php';
        return File::getByContent('reply', $replyId);
    }

    // Create a reply with file attachments
    public static function createWithFiles($discussionId, $userId, $content, $files = [], $parentId = null) {
        global $pdo;
        require_once __DIR__ . '/File.php';
        
        try {
            $pdo->beginTransaction();
            
            // Create the reply
            $stmt = $pdo->prepare("INSERT INTO replies (discussion_id, user_id, content, parent_id) 
                                  VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([$discussionId, $userId, $content, $parentId]);
            
            if ($result) {
                $replyId = $pdo->lastInsertId();
                
                // Get group_id from discussion for file uploads
                $stmt = $pdo->prepare("SELECT group_id FROM discussions WHERE id = ?");
                $stmt->execute([$discussionId]);
                $discussion = $stmt->fetch(PDO::FETCH_ASSOC);
                $groupId = $discussion['group_id'];
                
                // Debug log
                error_log("Processing files for reply ID: $replyId, Group ID: $groupId");
                
                // Process uploaded files if any
                if (!empty($files) && isset($files['name']) && !empty($files['name'][0])) {
                    $fileCount = count($files['name']);
                    error_log("Found $fileCount files to upload");
                    
                    for ($i = 0; $i < $fileCount; $i++) {
                        if ($files['error'][$i] === 0) {
                            $fileData = [
                                'name' => $files['name'][$i],
                                'type' => $files['type'][$i],
                                'tmp_name' => $files['tmp_name'][$i],
                                'error' => $files['error'][$i],
                                'size' => $files['size'][$i]
                            ];
                            
                            $result = File::upload($fileData, $userId, $groupId, 'reply', $replyId);
                            error_log("File upload result: " . ($result ? json_encode($result) : "failed"));
                        } else {
                            error_log("File upload error code: " . $files['error'][$i]);
                        }
                    }
                } else {
                    error_log("No files to upload or files array is empty");
                }
                
                $pdo->commit();
                return $replyId;
            }
            
            $pdo->rollBack();
            return false;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Reply with files creation error: " . $e->getMessage());
            return false;
        }
    }
}