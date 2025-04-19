<?php
require_once "../controllers/groupController.php";
require_once "../controllers/authController.php";
require_once "../models/Discussion.php";
require_once "../models/Group.php";

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Get group ID
$groupId = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;
if (!$groupId) {
    header('Location: groups.php');
    exit();
}

// Get group details
$group = Group::getById($groupId);
if (!$group) {
    header('Location: groups.php?error=not_found');
    exit();
}

// Check if user is a member if the group is private
if ($group['privacy'] === 'private' && $isLoggedIn) {
    $memberRole = Group::getMemberRole($groupId, $_SESSION['user_id']);
    if (!$memberRole && $group['created_by'] != $_SESSION['user_id']) {
        header('Location: groups.php?error=private_group');
        exit();
    }
}

// Get all discussions for this group
$discussions = Discussion::getByGroupId($groupId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discussions - <?= htmlspecialchars($group['name']) ?></title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-image: url('https://images.unsplash.com/photo-1554147090-e1221a04a025?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .content-container {
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.85);
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50 py-6">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="content-container rounded-xl shadow-2xl overflow-hidden transition-all duration-300 hover:shadow-[0_20px_50px_rgba(8,_112,_184,_0.7)]">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-white"><?= htmlspecialchars($group['name']) ?> Discussions</h2>
                        <p class="text-blue-100 mt-1">Browse and participate in group discussions</p>
                    </div>
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
                </div>
            </div>


            <style>
    #success-message {
        transition: transform 0.5s ease, opacity 0.5s ease; /* Smooth transition for transform and opacity */
    }
</style>



<?php if(isset($_GET['success'])): ?>
    <div id="success-message" class="bg-green-50 border-l-4 border-green-500 p-4 ">
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
                            $message = "Discussion created successfully!";
                            break;
                        case 'deleted':
                            $message = "Discussion deleted successfully!";
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
<?php endif; ?>

    
            
            
            
            <?php if(isset($_GET['error'])): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mx-6 mt-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                <?php 
                                $message = "";
                                switch($_GET['error']) {
                                    case 'unauthorized':
                                        $message = "You are not authorized to perform this action.";
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
            
            <div class="p-6 bg-white">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-800">
                        <i class="fas fa-comments text-indigo-600 mr-2"></i> All Discussions
                    </h3>
                    
                    <?php if($isLoggedIn && isset($memberRole)): ?>
                        <a href="create_discussion.php?group_id=<?= $groupId ?>" class="bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white font-medium px-4 py-2 rounded-lg shadow-md transition-all duration-200 transform hover:-translate-y-0.5">
                            <i class="fas fa-plus mr-2"></i> New Discussion
                        </a>
                    <?php endif; ?>
                </div>
                
                <?php if(count($discussions) > 0): ?>
                    <div class="space-y-4">
                        <?php foreach($discussions as $discussion): ?>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 hover:shadow-md transition-all duration-200">
                                <div class="flex justify-between">
                                    <div>
                                        <a href="single_discussion.php?id=<?= $discussion['id'] ?>" class="text-lg font-semibold text-indigo-700 hover:text-indigo-900 transition duration-150">
                                            <?= htmlspecialchars($discussion['title']) ?>
                                        </a>
                                        <div class="text-sm text-gray-500 mt-1">
                                            Started by <?= htmlspecialchars($discussion['author_name']) ?> • <?= date('M d, Y', strtotime($discussion['created_at'])) ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <div class="text-sm text-gray-600">
                                            <i class="fas fa-comment-alt mr-1"></i> <?= $discussion['reply_count'] ?>
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            <i class="fas fa-heart mr-1"></i> <?= $discussion['like_count'] ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 text-gray-700 line-clamp-2">
                                    <?= htmlspecialchars(substr($discussion['content'], 0, 150)) ?><?= strlen($discussion['content']) > 150 ? '...' : '' ?>
                                </div>
                                <div class="mt-3">
                                    <a href="single_discussion.php?id=<?= $discussion['id'] ?>" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 transition duration-150">
                                        Read more <i class="fas fa-chevron-right ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-gray-50 rounded-lg p-8 text-center">
                        <i class="fas fa-comments text-gray-400 text-5xl mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Discussions Yet</h3>
                        <?php if($isLoggedIn && isset($memberRole)): ?>
                            <p class="text-gray-600 mb-4">Start a new discussion to get the conversation going!</p>
                            <a href="create_discussion.php?group_id=<?= $groupId ?>" class="bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white font-medium px-4 py-2 rounded-lg shadow-md transition-all duration-200 inline-block">
                                <i class="fas fa-plus mr-2"></i> New Discussion
                            </a>
                        <?php else: ?>
                            <p class="text-gray-600">Join this group to participate in discussions!</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center">
                <a href="group.php?id=<?= $groupId ?>" class="text-indigo-600 hover:text-indigo-800 font-medium transition duration-150">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Group
                </a>
                <?php if($isLoggedIn): ?>
                    <a href="profile.php" class="text-indigo-600 hover:text-indigo-800 font-medium transition duration-150 mt-2 sm:mt-0">
                        <i class="fas fa-user mr-1"></i> View Profile
                    </a>
                <?php else: ?>
                    <a href="login.php" class="text-indigo-600 hover:text-indigo-800 font-medium transition duration-150 mt-2 sm:mt-0">
                        <i class="fas fa-sign-in-alt mr-1"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <p class="text-center mt-8 text-white text-sm">
            © 2025 Konvo. All rights reserved.
        </p>
    </div>
</body>
</html>