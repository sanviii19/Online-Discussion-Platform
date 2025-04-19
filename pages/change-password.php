<?php
session_start();

$host = "localhost";
$dbname = "study_groups";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "<p class='text-red-600'>Database error.</p>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current, $user['password'])) {
        echo "<p class='text-red-600'>Current password is incorrect.</p>";
    } elseif ($new !== $confirm) {
        echo "<p class='text-red-600'>New passwords do not match.</p>";
    } elseif (strlen($new) < 6) {
        echo "<p class='text-red-600'>Password must be at least 6 characters long.</p>";
    } else {
        $newHashed = password_hash($new, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->execute([$newHashed, $_SESSION['user_id']]);
        echo "<p class='text-green-600'>Password changed successfully!</p>";
    }
}
?>
