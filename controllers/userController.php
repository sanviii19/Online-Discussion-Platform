<?php
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['updateProfile'])) {
    $avatar = null;
    
    if (!empty($_FILES['avatar']['name'])) {
        // Get document root path and create relative path to uploads directory
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Online-Discussion-Platform/public/uploads/avatars/';
        
        // Make sure directory exists with proper permissions
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                header("Location: profile.php?error=directory");
                exit;
            }
        }
        
        // Show all errors for debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        // Get file details
        $fileExtension = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
        $uniqueFilename = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $fileExtension;
        $targetFilePath = $uploadDir . $uniqueFilename;
        
        // Validate file type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($fileExtension, $allowedTypes)) {
            header("Location: profile.php?error=filetype");
            exit;
        }
        
        // Check if we can move the file
        if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $targetFilePath)) {
            // Store web path for database (not file system path)
            $avatar = "/public/uploads/avatars/" . $uniqueFilename;
            chmod($targetFilePath, 0644); // Make readable
        } else {
            // Detailed error information
            $error = error_get_last();
            error_log("Move failed. Error: " . ($error ? $error['message'] : 'Unknown error'));
            error_log("From: " . $_FILES["avatar"]["tmp_name"] . " To: " . $targetFilePath);
            header("Location: profile.php?error=move");
            exit;
        }
    }
    
    // Update user profile
    if (User::updateProfile($_SESSION['user_id'], $_POST['name'], $_POST['email'], $avatar)) {
        header("Location: profile.php?success=updated");
    } else {
        header("Location: profile.php?error=database");
    }
    exit;
}
?>