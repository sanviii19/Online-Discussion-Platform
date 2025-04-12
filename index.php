<?php
require_once "./controllers/authController.php";
require_once "./models/Group.php";

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

if ($isLoggedIn) {
    $userId = $_SESSION['user_id'];
    
    // Get user's groups
    $userGroups = Group::getUserGroups($userId);
    
    // If user has joined at least one group, redirect to the most recent one
    if (!empty($userGroups)) {
        $mostRecentGroup = $userGroups[0]; // First group in the list (most recent)
        header("Location: pages/group.php?id=" . $mostRecentGroup['id']);
        exit();
    } else {
        // User is logged in but hasn't joined any groups
        header("Location: pages/groups.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discussion Platform - Study Group Collaboration</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-image: url('https://images.unsplash.com/photo-1554147090-e1221a04a025?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            height: 100vh;
        }
        .hero-container {
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.85);
        }
    </style>
</head>
<body class="h-screen flex items-center justify-center">
    <div class="hero-container mx-auto p-8 rounded-xl shadow-2xl max-w-4xl transition-all duration-300 hover:shadow-[0_20px_50px_rgba(8,_112,_184,_0.7)]">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-4">Welcome to Discussion Platform</h1>
            <p class="text-lg text-gray-600">Join study groups, collaborate, and learn together</p>
        </div>
        
        <div class="flex flex-col md:flex-row gap-6 justify-center">
            <a href="pages/login.php" class="bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white font-bold py-3 px-6 rounded-lg text-center shadow-lg transition-all duration-200 transform hover:-translate-y-1">
                <i class="fas fa-sign-in-alt mr-2"></i> Login
            </a>
            <a href="pages/register.php" class="bg-white text-indigo-600 hover:bg-gray-100 font-bold py-3 px-6 rounded-lg text-center shadow-lg border border-indigo-200 transition-all duration-200 transform hover:-translate-y-1">
                <i class="fas fa-user-plus mr-2"></i> Register
            </a>
        </div>
        
        <div class="mt-10 text-center">
            <p class="text-gray-600">Already registered? <a href="pages/groups.php" class="text-indigo-600 hover:text-indigo-800 font-medium">Explore Groups</a></p>
        </div>
    </div>
</body>
</html>