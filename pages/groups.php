<?php
session_start();
require_once "../controllers/groupController.php";
require_once "../controllers/authController.php";

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;

$user = User::getUser($_SESSION['user_id']);

// Get user's joined groups if logged in
$joinedGroups = $isLoggedIn ? Group::getUserGroups($userId) : [];

// Search functionality
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterPrivacy = isset($_GET['privacy']) ? $_GET['privacy'] : 'all';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Get all groups with filtering
$groups = Group::getAllGroups($userId);

// Apply search filter if provided
if (!empty($searchTerm)) {
    $groups = array_filter($groups, function($group) use ($searchTerm) {
        return stripos($group['name'], $searchTerm) !== false || 
               stripos($group['description'], $searchTerm) !== false;
    });
}

// Apply privacy filter
if ($filterPrivacy !== 'all') {
    $groups = array_filter($groups, function($group) use ($filterPrivacy) {
        return $group['privacy'] === $filterPrivacy;
    });
}

// Apply sorting
if ($sortBy === 'newest') {
    usort($groups, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
} elseif ($sortBy === 'popular') {
    usort($groups, function($a, $b) {
        return $b['member_count'] - $a['member_count'];
    });
} elseif ($sortBy === 'alphabetical') {
    usort($groups, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
}

// Calculate dashboard metrics
$totalGroups = count($groups);
$myGroupsCount = count(array_filter($joinedGroups, function($group) {
    return $group['status'] === 'approved';
}));
$pendingRequests = count(array_filter($joinedGroups, function($group) {
    return $group['status'] === 'pending';
}));

// Get user's managed groups (where user is admin or moderator)
$managedGroups = [];
if ($isLoggedIn) {
    $managedGroups = array_filter($joinedGroups, function($group) {
        return $group['role'] === 'admin' || $group['role'] === 'moderator';
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Groups Dashboard - Discussion Platform</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            --secondary-gradient: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            --accent-gradient: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
        }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-image: url('https://images.unsplash.com/photo-1554147090-e1221a04a025?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        .content-wrapper {
           
        }
        
        .dashboard-card {
            transition: all 0.3s ease;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            background-color: white;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .search-input:focus {
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
        }
        
        .progress-ring {
            transform: rotate(-90deg);
        }
        
        .progress-ring__circle {
            stroke-dasharray: 251.2;
            stroke-dashoffset: 251.2;
            transition: stroke-dashoffset 1s ease;
        }
        
        .group-tag {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .scrollbar-thin::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 5px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(16px);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .gradient-text {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .nav-link {
            position: relative;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -4px;
            left: 0;
            background: var(--primary-gradient);
            transition: width 0.3s;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .blob {
            position: absolute;
            z-index: -1;
            filter: blur(60px);
            opacity: 0.3;
        }
        
        .blob-1 {
            top: 10%;
            right: 10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.4) 0%, rgba(168, 85, 247, 0.2) 70%);
            border-radius: 50% 30% 70% 40%;
        }
        
        .blob-2 {
            bottom: 20%;
            left: 10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.3) 0%, rgba(139, 92, 246, 0.2) 70%);
            border-radius: 40% 60% 30% 70%;
        }
    </style>
</head>
<body>
    <!-- Background Blobs -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    
    <div class="min-h-screen flex flex-col content-wrapper">
        <!-- Modern Navbar - styled like index.php -->
        <nav class="glass-card max-w-7xl mx-auto mt-6 px-6 py-4 flex items-center justify-between mb-8">
            <div class="flex items-center">
                <svg class="w-10 h-10 mr-3" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 5C11.729 5 5 11.729 5 20C5 28.271 11.729 35 20 35C28.271 35 35 28.271 35 20C35 11.729 28.271 5 20 5Z" fill="url(#paint0_linear)"/>
                    <path d="M15 16C16.1046 16 17 15.1046 17 14C17 12.8954 16.1046 12 15 12C13.8954 12 13 12.8954 13 14C13 15.1046 13.8954 16 15 16Z" fill="white"/>
                    <path d="M25 16C26.1046 16 27 15.1046 27 14C27 12.8954 26.1046 12 25 12C23.8954 12 23 12.8954 23 14C23 15.1046 23.8954 16 25 16Z" fill="white"/>
                    <path d="M15 28H25M15 22C15 24.2091 17.2386 26 20 26C22.7614 26 25 24.2091 25 22" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    <defs>
                        <linearGradient id="paint0_linear" x1="5" y1="5" x2="35" y2="35" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#6366F1"/>
                            <stop offset="1" stop-color="#A855F7"/>
                        </linearGradient>
                    </defs>
                </svg>
                <span class="text-xl font-bold gradient-text">Konvo</span>
            </div>
            
            <div class="hidden md:flex pl-96 items-center space-x-8">
  
                <?php if($isLoggedIn): ?>
                <div class="flex space-x-3 items-center">
                    <a href="profile.php" class="nav-link text-gray-700 font-medium">
                        <i class="fas fa-user mr-1"></i> Profile
                    </a>
                    <a href="./logout.php" class="px-4 py-2 rounded-lg border border-red-200 text-red-500 font-medium transition hover:bg-red-50">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                </div>
                <?php else: ?>
                <div class="flex space-x-3">
                    <a href="login.php" class="px-4 py-2 rounded-lg border border-indigo-200 text-indigo-600 font-medium transition hover:bg-indigo-50">Login</a>
                    <a href="register.php" class="px-4 py-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium hover:from-indigo-700 hover:to-purple-700 transition duration-150 transform hover:-translate-y-0.5">Sign Up</a>
                </div>
                <?php endif; ?>
            </div>
            
            <button class="md:hidden text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
            </button>
        </nav>
        
        <!-- Main Content -->
        <main class="flex-grow container mx-auto px-4 py-6">
            <h3 class="text-4xl font-bold text-gray-800 mb-6">Welcome Back,<?= $user['name'] ?>!</h3>
            
            <!-- Alerts -->
            <style>
    #success-message {
        transition: transform 0.5s ease, opacity 0.5s ease; /* Smooth transition for transform and opacity */
    }
</style>

<script>
    // Set a timeout to hide the success message after 1.5 seconds with a smooth sideways transition
    setTimeout(() => {
        const successMessage = document.getElementById('success-message');
        if (successMessage) {
            successMessage.style.transform = 'translateX(100%)'; // Move the message sideways
            successMessage.style.opacity = '0'; // Fade out the message
            setTimeout(() => {
                successMessage.style.display = 'none'; // Hide the element after the transition
            }, 500); // Match the duration of the CSS transition
        }
    }, 1500);
</script>

<?php if(isset($_GET['success'])): ?>
    <div id="success-message" class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-500"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700">
                    <?php 
                    $message = "";
                    switch($_GET['success']) {
                        case 'created':
                            $message = "Group created successfully!";
                            break;
                        case 'deleted':
                            $message = "Group deleted successfully!";
                            break;
                        case 'left':
                            $message = "You have left the group.";
                            break;
                        case 'request_sent':
                            $message = "Your request to join has been sent. Waiting for approval.";
                            break;
                        default:
                            $message = "Operation completed successfully!";
                    }
                    echo $message;
                    ?>
                </p>
            </div>
        </div>
    </div>
<?php endif; ?>
            
            <?php if(isset($_GET['error'])): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                <?php 
                                $message = "";
                                switch($_GET['error']) {
                                    case 'name_required':
                                        $message = "Group name is required!";
                                        break;
                                    case 'creation_failed':
                                        $message = "Failed to create group. Please try again.";
                                        break;
                                    case 'join_failed':
                                        $message = "Failed to join group. Please try again.";
                                        break;
                                    default:
                                        $message = "An error occurred. Please try again.";
                                }
                                echo $message;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Dashboard Header with Metrics -->
            <?php if($isLoggedIn): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="dashboard-card bg-gradient-to-tr from-indigo-500 to-purple-600 text-white p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-semibold opacity-80">My Groups</h3>
                            <p class="text-3xl font-bold"><?= $myGroupsCount ?></p>
                        </div>
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-users text-2xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 text-sm">
                        <span class="inline-flex items-center">
                            <i class="fas fa-arrow-up mr-1"></i> 
                            Active in <?= count(array_filter($joinedGroups, function($g) { 
                                return isset($g['last_activity']) && strtotime($g['last_activity']) > strtotime('-1 week'); 
                            })) ?> groups this week
                        </span>
                    </div>
                </div>
                
                <div class="dashboard-card bg-gradient-to-tr from-cyan-500 to-blue-600 text-white p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-semibold opacity-80">Total Groups</h3>
                            <p class="text-3xl font-bold"><?= $totalGroups ?></p>
                        </div>
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-globe text-2xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 text-sm">
                        <span class="inline-flex items-center">
                            <i class="fas fa-search mr-1"></i>
                            Discover new learning communities
                        </span>
                    </div>
                </div>
                
                <div class="dashboard-card bg-gradient-to-tr from-orange-500 to-pink-600 text-white p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-semibold opacity-80">Managed Groups</h3>
                            <p class="text-3xl font-bold"><?= count($managedGroups) ?></p>
                        </div>
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-user-shield text-2xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 text-sm">
                        <span class="inline-flex items-center">
                            <?php if($pendingRequests > 0): ?>
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            <?= $pendingRequests ?> pending request<?= $pendingRequests != 1 ? 's' : '' ?>
                            <?php else: ?>
                            <i class="fas fa-check-circle mr-1"></i>
                            No pending requests
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Search & Filters Bar -->
            <div class="glass-card p-4 mb-8">
                <div class="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-4">
                    <div class="flex-grow">
                        <form action="" method="GET" class="flex w-full">
                            <div class="relative flex-grow">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" name="search" value="<?= htmlspecialchars($searchTerm) ?>" placeholder="Search groups by name or description" class="search-input block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <button type="submit" class="flex items-center justify-center px-4 border border-l-0 border-gray-300 rounded-r-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Search
                            </button>
                        </form>
                    </div>
                    
                    <div class="flex space-x-2">
                        <div class="relative">
                            <select name="privacy" id="privacy-filter" class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-lg sm:text-sm" onchange="applyFilters()">
                                <option value="all" <?= $filterPrivacy === 'all' ? 'selected' : '' ?>>All Groups</option>
                                <option value="public" <?= $filterPrivacy === 'public' ? 'selected' : '' ?>>Public Only</option>
                                <option value="private" <?= $filterPrivacy === 'private' ? 'selected' : '' ?>>Private Only</option>
                            </select>
                        </div>
                        
                        <div class="relative">
                            <select name="sort" id="sort-filter" class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-lg sm:text-sm" onchange="applyFilters()">
                                <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>>Newest First</option>
                                <option value="popular" <?= $sortBy === 'popular' ? 'selected' : '' ?>>Most Members</option>
                                <option value="alphabetical" <?= $sortBy === 'alphabetical' ? 'selected' : '' ?>>Alphabetical</option>
                            </select>
                        </div>
                    </div>
                    
                    <?php if($isLoggedIn): ?>
                    <a href="./create_group.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 transform hover:-translate-y-0.5">
                        <i class="fas fa-plus mr-2"></i> Create Group
                    </a>
                    <?php else: ?>
                    <a href="login.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 transform hover:-translate-y-0.5">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login to Create
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Dashboard Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column - My Groups -->
                <div class="lg:col-span-1">
                    <div class="dashboard-card glass-card mb-6">
                        <div class="px-4 py-5 border-b border-gray-200 sm:px-6 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white">
                            <h3 class="text-lg font-medium">
                                <i class="fas fa-star mr-2"></i> My Groups
                            </h3>
                            <p class="mt-1 text-sm opacity-80">Groups you've joined or created</p>
                        </div>
                        
                        <?php if($isLoggedIn && count($joinedGroups) > 0): ?>
                            <div class="overflow-y-auto scrollbar-thin" style="max-height: 480px;">
                                <ul class="divide-y divide-gray-200">
                                    <?php foreach($joinedGroups as $group): ?>
                                        <li class="hover:bg-gray-50">
                                            <a href="group.php?id=<?= $group['id'] ?>" class="block px-4 py-4">
                                                <div class="flex items-start justify-between">
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center mb-1">
                                                            <h4 class="text-base font-semibold text-gray-800 truncate"><?= htmlspecialchars($group['name']) ?></h4>
                                                            
                                                            <?php if($group['role'] === 'admin'): ?>
                                                                <span class="ml-2 group-tag bg-red-100 text-red-800">Admin</span>
                                                            <?php elseif($group['role'] === 'moderator'): ?>
                                                                <span class="ml-2 group-tag bg-blue-100 text-blue-800">Mod</span>
                                                            <?php endif; ?>
                                                            
                                                            <?php if($group['status'] === 'pending'): ?>
                                                                <span class="ml-2 group-tag bg-yellow-100 text-yellow-800">Pending</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                        <p class="text-sm text-gray-600 line-clamp-1"><?= htmlspecialchars($group['description']) ?></p>
                                                        
                                                        <div class="mt-2 flex items-center text-xs text-gray-500">
                                                            <span class="flex items-center">
                                                                <i class="fas fa-users mr-1"></i> <?= $group['member_count'] ?> members
                                                            </span>
                                                            <span class="mx-2">•</span>
                                                            <span class="flex items-center">
                                                                <?php if($group['privacy'] === 'private'): ?>
                                                                    <i class="fas fa-lock mr-1"></i> Private
                                                                <?php else: ?>
                                                                    <i class="fas fa-globe mr-1"></i> Public
                                                                <?php endif; ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="ml-4 flex-shrink-0">
                                                        <i class="fas fa-chevron-right text-gray-400"></i>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php elseif($isLoggedIn): ?>
                            <div class="p-8 text-center">
                                <i class="fas fa-user-friends text-gray-300 text-5xl mb-4"></i>
                                <h4 class="text-xl font-semibold text-gray-700 mb-2">No Groups Yet</h4>
                                <p class="text-gray-500 mb-4">You haven't joined any study groups yet.</p>
                                <a href="create_group.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 transition duration-150 transform hover:-translate-y-0.5">
                                    <i class="fas fa-plus mr-2"></i> Create Your First Group
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="p-8 text-center">
                                <i class="fas fa-user-lock text-gray-300 text-5xl mb-4"></i>
                                <h4 class="text-xl font-semibold text-gray-700 mb-2">Login Required</h4>
                                <p class="text-gray-500 mb-4">Sign in to see your joined groups.</p>
                                <a href="login.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 transition duration-150 transform hover:-translate-y-0.5">
                                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if($isLoggedIn && count($managedGroups) > 0): ?>
                    <div class="dashboard-card glass-card">
                        <div class="px-4 py-5 border-b border-gray-200 sm:px-6 bg-gradient-to-r from-orange-500 to-pink-600 text-white">
                            <h3 class="text-lg font-medium">
                                <i class="fas fa-tasks mr-2"></i> Groups You Manage
                            </h3>
                            <p class="mt-1 text-sm opacity-80">Quick admin access</p>
                        </div>
                        <div class="p-4 space-y-3">
                            <?php foreach(array_slice($managedGroups, 0, 3) as $group): ?>
                                <div class="p-3 bg-gray-50 rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <h4 class="font-medium text-gray-800"><?= htmlspecialchars($group['name']) ?></h4>
                                        <span class="text-xs text-gray-500"><?= $group['member_count'] ?> members</span>
                                    </div>
                                    <div class="mt-2 flex space-x-2">
                                        <a href="group.php?id=<?= $group['id'] ?>" class="text-xs px-2 py-1 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200 transition-colors">
                                            <i class="fas fa-eye mr-1"></i> View
                                        </a>
                                        <a href="manage_members.php?id=<?= $group['id'] ?>" class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors">
                                            <i class="fas fa-users-cog mr-1"></i> Members
                                        </a>
                                        <a href="edit_group.php?id=<?= $group['id'] ?>" class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 transition-colors">
                                            <i class="fas fa-edit mr-1"></i> Edit
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if(count($managedGroups) > 3): ?>
                                <a href="profile.php#managed-groups" class="block text-center text-sm text-indigo-600 hover:text-indigo-900 mt-2">
                                    View all <?= count($managedGroups) ?> managed groups
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Right Column - All Groups -->
                <div class="lg:col-span-2">
                    <div class="dashboard-card glass-card">
                        <div class="px-4 py-5 border-b border-gray-200 sm:px-6 bg-gradient-to-r from-cyan-500 to-blue-600 text-white">
                            <h3 class="text-lg font-medium">
                                <i class="fas fa-globe mr-2"></i> Discover Groups
                            </h3>
                            <p class="mt-1 text-sm opacity-80">
                                <?php if(!empty($searchTerm)): ?>
                                    Search results for: "<?= htmlspecialchars($searchTerm) ?>" (<?= count($groups) ?> results)
                                <?php else: ?>
                                    Browse all available study groups
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <?php if(count($groups) > 0): ?>
                            <div class="bg-white/80 p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php foreach($groups as $group): ?>
                                    <div class="border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200 bg-white">
                                        <div class="p-4">
                                            <div class="flex justify-between items-start">
                                                <h4 class="text-lg font-semibold text-gray-800">
                                                    <?= htmlspecialchars($group['name']) ?>
                                                </h4>
                                                <div>
                                                    <?php if($group['privacy'] === 'private'): ?>
                                                        <span class="group-tag bg-yellow-100 text-yellow-800">
                                                            <i class="fas fa-lock mr-1"></i> Private
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="group-tag bg-green-100 text-green-800">
                                                            <i class="fas fa-globe mr-1"></i> Public
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <p class="text-gray-600 text-sm mt-2 line-clamp-2">
                                                <?= htmlspecialchars($group['description'] ?? 'No description available.') ?>
                                            </p>
                                            
                                            <div class="flex items-center mt-3 text-sm text-gray-500">
                                                <span class="flex items-center">
                                                    <i class="fas fa-user mr-1"></i> 
                                                    <?= htmlspecialchars($group['creator_name'] ?? 'Unknown') ?>
                                                </span>
                                                <span class="mx-2">•</span>
                                                <span class="flex items-center">
                                                    <i class="fas fa-users mr-1"></i> 
                                                    <?= intval($group['member_count']) ?> members
                                                </span>
                                            </div>
                                            
                                            <div class="mt-4 flex justify-between items-center">
                                                <span class="text-xs text-gray-500">
                                                    Created <?= date('M d, Y', strtotime($group['created_at'])) ?>
                                                </span>
                                                
                                                <div>
                                                    <?php 
                                                    // Check if user is a member
                                                    $isMember = $isLoggedIn && in_array($group['id'], array_column($joinedGroups, 'id'));
                                                    ?>
                                                    
                                                    <?php if($isMember): ?>
                                                        <a href="group.php?id=<?= $group['id'] ?>" class="inline-flex items-center text-sm px-3 py-1 border border-transparent rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                            <i class="fas fa-door-open mr-1"></i> Enter
                                                        </a>
                                                    <?php elseif($isLoggedIn): ?>
                                                        <form method="POST" action="../controllers/groupController.php" class="inline">
                                                            <input type="hidden" name="group_id" value="<?= $group['id'] ?>">
                                                            <button type="submit" name="join_group" class="inline-flex items-center text-sm px-3 py-1 border border-indigo-600 rounded-md text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                                <i class="fas fa-plus mr-1"></i> Join
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <a href="login.php" class="inline-flex items-center text-sm px-3 py-1 border border-indigo-600 rounded-md text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                            <i class="fas fa-sign-in-alt mr-1"></i> Login to Join
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="p-8 text-center">
                                <i class="fas fa-search text-gray-300 text-5xl mb-4"></i>
                                <?php if(!empty($searchTerm)): ?>
                                    <h4 class="text-xl font-semibold text-gray-700 mb-2">No Results Found</h4>
                                    <p class="text-gray-500 mb-4">We couldn't find any groups matching "<?= htmlspecialchars($searchTerm) ?>"</p>
                                    <a href="groups.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 transition duration-150 transform hover:-translate-y-0.5">
                                        <i class="fas fa-undo mr-2"></i> Clear Search
                                    </a>
                                <?php else: ?>
                                    <h4 class="text-xl font-semibold text-gray-700 mb-2">No Study Groups Found</h4>
                                    <?php if($isLoggedIn): ?>
                                        <p class="text-gray-500 mb-4">Be the first to create a study group!</p>
                                        <a href="create_group.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 transition duration-150 transform hover:-translate-y-0.5">
                                            <i class="fas fa-plus mr-2"></i> Create New Group
                                        </a>
                                    <?php else: ?>
                                        <p class="text-gray-500 mb-4">Login to create or join study groups.</p>
                                        <a href="login.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 transition duration-150 transform hover:-translate-y-0.5">
                                            <i class="fas fa-sign-in-alt mr-2"></i> Login
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
        
        <!-- Footer -->
        <footer class="bg-gray-900 bg-opacity-80 backdrop-blur-sm text-white py-6 mt-10">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row md:justify-between items-center">
                    <div class="flex items-center space-x-4 mb-4 md:mb-0">
                        <span class="text-xl font-bold gradient-text">Konvo</span>
                        <div class="text-gray-400 text-sm">
                            © 2025 All rights reserved
                        </div>
                    </div>
                    
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-github"></i>
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    <script>
        // Apply filters function
        function applyFilters() {
            const privacy = document.getElementById('privacy-filter').value;
            const sort = document.getElementById('sort-filter').value;
            const searchParams = new URLSearchParams(window.location.search);
            
            // Update or add privacy parameter
            if (privacy === 'all') {
                searchParams.delete('privacy');
            } else {
                searchParams.set('privacy', privacy);
            }
            
            // Update or add sort parameter
            searchParams.set('sort', sort);
            
            // Preserve search term if it exists
            const searchTerm = searchParams.get('search');
            if (!searchTerm) {
                searchParams.delete('search');
            }
            
            // Navigate to the new URL
            window.location.href = `${window.location.pathname}?${searchParams.toString()}`;
        }
        
        // Mobile menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.querySelector('button.md\\:hidden');
            const mobileMenu = document.createElement('div');
            mobileMenu.className = 'md:hidden fixed inset-0 z-50 bg-gray-900 bg-opacity-50 backdrop-blur-sm transform transition-transform duration-300 ease-in-out translate-x-full';
            mobileMenu.innerHTML = `
                <div class="bg-white h-full w-64 shadow-xl float-right p-6">
                    <div class="flex justify-between items-center mb-6">
                        <span class="text-xl font-bold gradient-text">Menu</span>
                        <button class="text-gray-600" id="closeMenu">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="flex flex-col space-y-4">
                        <a href="../index.php" class="py-2 px-4 text-gray-700 hover:bg-gray-100 rounded-lg">Home</a>
                        <a href="groups.php" class="py-2 px-4 text-gray-700 hover:bg-gray-100 rounded-lg font-medium bg-gray-100">Groups</a>
                        <a href="discussions.php" class="py-2 px-4 text-gray-700 hover:bg-gray-100 rounded-lg">Discussions</a>
                        <?php if($isLoggedIn): ?>
                        <a href="profile.php" class="py-2 px-4 text-gray-700 hover:bg-gray-100 rounded-lg">
                            <i class="fas fa-user mr-1"></i> Profile
                        </a>
                        <a href="../controllers/authController.php?logout=1" class="py-2 px-4 text-red-600 hover:bg-red-50 rounded-lg">
                            <i class="fas fa-sign-out-alt mr-1"></i> Logout
                        </a>
                        <?php else: ?>
                        <a href="login.php" class="py-2 px-4 text-indigo-600 hover:bg-indigo-50 rounded-lg font-medium">Login</a>
                        <a href="register.php" class="py-2 px-4 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700">Sign Up</a>
                        <?php endif; ?>
                    </div>
                </div>
            `;
            document.body.appendChild(mobileMenu);
            
            mobileMenuBtn.addEventListener('click', function() {
                mobileMenu.classList.remove('translate-x-full');
            });
            
            document.getElementById('closeMenu').addEventListener('click', function() {
                mobileMenu.classList.add('translate-x-full');
            });
            
            // Set up progress ring animations if they exist
            document.querySelectorAll('.progress-ring__circle').forEach(circle => {
                const radius = circle.r.baseVal.value;
                const circumference = radius * 2 * Math.PI;
                const percent = parseInt(circle.dataset.percent || 0);
                
                circle.style.strokeDasharray = `${circumference} ${circumference}`;
                circle.style.strokeDashoffset = circumference;
                
                setTimeout(() => {
                    const offset = circumference - (percent / 100) * circumference;
                    circle.style.strokeDashoffset = offset;
                }, 100);
            });
        });
    </script>
</body>
</html>