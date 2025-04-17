<?php
require_once __DIR__ . '/../config/db.php';

class File {
    // Upload a file and save its details to the database
    public static function upload($file, $userId, $groupId, $contentType = 'reply', $contentId = null) {
        global $pdo;
        
        try {
            // Debugging
            error_log("Upload started for file: " . print_r($file, true));
            
            // Check if uploads directory exists, create if not
            $uploadDir = __DIR__ . '/../public/uploads/resources';
            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    error_log("Failed to create directory: $uploadDir");
                    return false;
                }
            }
            
            // Get file details
            $fileName = basename($file['name']);
            $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
            $fileSize = $file['size'];
            
            // Generate unique filename to prevent overwriting
            $uniqueName = uniqid() . '_' . $fileName;
            $filePath = 'public/uploads/resources/' . $uniqueName;
            $fullPath = __DIR__ . '/../' . $filePath;
            
            error_log("Moving uploaded file from {$file['tmp_name']} to $fullPath");
            
            // Move uploaded file to destination
            if (move_uploaded_file($file['tmp_name'], $fullPath)) {
                // Set proper permissions
                chmod($fullPath, 0644);
                
                error_log("File moved successfully, now inserting into database");
                
                // Save file details to database
                $query = "INSERT INTO files (user_id, group_id, content_type, content_id, file_name, file_type, file_size, file_path) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                
                error_log("SQL Query: $query");
                error_log("Params: user_id=$userId, group_id=$groupId, content_type=$contentType, content_id=$contentId");
                
                $stmt = $pdo->prepare($query);
                $result = $stmt->execute([
                    $userId, 
                    $groupId, 
                    $contentType, 
                    $contentId, 
                    $fileName, 
                    $fileType, 
                    $fileSize, 
                    $filePath
                ]);
                
                if ($result) {
                    error_log("File inserted into database with ID: " . $pdo->lastInsertId());
                    return [
                        'id' => $pdo->lastInsertId(),
                        'name' => $fileName,
                        'path' => $filePath,
                        'type' => $fileType,
                        'size' => $fileSize
                    ];
                } else {
                    error_log("Database insert failed: " . json_encode($stmt->errorInfo()));
                }
            } else {
                $error = error_get_last();
                error_log("Move failed. Error: " . ($error ? $error['message'] : 'Unknown error'));
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("File upload PDO error: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("General exception in file upload: " . $e->getMessage());
            return false;
        }
    }
    
    // Get files by related content
    public static function getByContent($contentType, $contentId) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("SELECT f.*, u.name as uploader_name 
                                  FROM files f
                                  JOIN users u ON f.user_id = u.id
                                  WHERE f.content_type = ? AND f.content_id = ?
                                  ORDER BY f.uploaded_at DESC");
            $stmt->execute([$contentType, $contentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get files error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get file by ID
    public static function getById($id) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get file error: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete a file
    public static function delete($id) {
        global $pdo;
        
        try {
            // Get file path
            $stmt = $pdo->prepare("SELECT file_path FROM files WHERE id = ?");
            $stmt->execute([$id]);
            $file = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($file) {
                // Delete file from filesystem
                $filePath = __DIR__ . '/../' . $file['file_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                
                // Delete from database
                $stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
                return $stmt->execute([$id]);
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Delete file error: " . $e->getMessage());
            return false;
        }
    }
    
    // Format file size to readable format
    public static function formatSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    // Get appropriate icon for file type
    public static function getFileIcon($fileType) {
        $fileType = strtolower($fileType);
        
        switch ($fileType) {
            case 'pdf':
                return 'fa-file-pdf';
            case 'doc':
            case 'docx':
                return 'fa-file-word';
            case 'xls':
            case 'xlsx':
                return 'fa-file-excel';
            case 'ppt':
            case 'pptx':
                return 'fa-file-powerpoint';
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'svg':
            case 'webp':
                return 'fa-file-image';
            case 'mp4':
            case 'avi':
            case 'mov':
            case 'wmv':
                return 'fa-file-video';
            case 'mp3':
            case 'wav':
            case 'ogg':
                return 'fa-file-audio';
            case 'zip':
            case 'rar':
            case '7z':
                return 'fa-file-archive';
            case 'txt':
                return 'fa-file-alt';
            default:
                return 'fa-file';
        }
    }
}
?>