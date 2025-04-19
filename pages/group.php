<?php
require_once "../controllers/groupController.php";
require_once "../controllers/authController.php";
require_once "../models/Discussion.php"; // Add this line

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;

// Get group ID from request
$groupId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($groupId <= 0) {
    header('Location: groups.php?error=invalid_group');
    exit();
}

// Get group details
$group = Group::getById($groupId);

if (!$group) {
    header('Location: groups.php?error=group_not_found');
    exit();
}

// Check if user can access this group (public or member)
$memberRole = false;
if ($isLoggedIn) {
    $memberRole = Group::getMemberRole($groupId, $userId);
}

// If private group and not a member, redirect
if ($group['privacy'] === 'private' && !$memberRole && $userId !== $group['created_by']) {
    header('Location: groups.php?error=private_group');
    exit();
}

// Get group members
$members = Group::getMembers($groupId);

// Get group discussions
$discussions = Discussion::getByGroupId($groupId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($group['name']) ?> - Konvo</title>
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
            min-height: 100vh;
        }
        .content-container {
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.85);
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-10">
        <div class="content-container rounded-xl shadow-2xl overflow-hidden transition-all duration-300 hover:shadow-[0_20px_50px_rgba(8,_112,_184,_0.7)]">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                    <div>
                        <div class="flex items-center">
                            <h2 class="text-2xl font-bold text-white mr-2"><?= htmlspecialchars($group['name']) ?></h2>
                            <?php if($group['privacy'] === 'private'): ?>
                                <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">
                                    <i class="fas fa-lock mr-1"></i> Private
                                </span>
                            <?php else: ?>
                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                    <i class="fas fa-globe mr-1"></i> Public
                                </span>
                            <?php endif; ?>
                        </div>
                        <p class="text-blue-100 mt-1">Created by <?= htmlspecialchars($group['creator_name']) ?> on <?= date('M d, Y', strtotime($group['created_at'])) ?></p>
                    </div>
                    
                    <div class="mt-4 md:mt-0 flex flex-wrap gap-2">
                        <?php if($isLoggedIn): ?>
                            <?php if($memberRole): ?>
                                <?php if($memberRole === 'admin'): ?>
                                    <a href="edit_group.php?id=<?= $groupId ?>" class="bg-white text-indigo-700 hover:bg-gray-100 font-medium px-3 py-1.5 rounded-lg shadow-sm transition-all duration-200 text-sm">
                                        <i class="fas fa-edit mr-1"></i> Edit Group
                                    </a>
                                    <form action="../controllers/groupController.php" method="POST">
                                        <input type="hidden" name="group_id" value="<?= $groupId ?>">
                                        <button type="submit" name="delete_group" class="bg-red-50 text-red-700 hover:bg-red-100 font-medium px-3 py-1.5 rounded-lg shadow-sm transition-all duration-200 text-sm" data-modal-toggle="deleteGroupModal">
                                            <i class="fas fa-trash-alt mr-1"></i> Delete Group
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <form action="../controllers/groupController.php" method="POST" class="inline">
                                    <input type="hidden" name="group_id" value="<?= $groupId ?>">
                                    <button type="submit" name="leave_group" class="bg-red-50 text-red-700 hover:bg-red-100 font-medium px-3 py-1.5 rounded-lg shadow-sm transition-all duration-200 text-sm">
                                        <i class="fas fa-sign-out-alt mr-1"></i> Leave Group
                                    </button>
                                </form>
                            <?php else: ?>
                                <form action="../controllers/groupController.php" method="POST" class="inline">
                                    <input type="hidden" name="group_id" value="<?= $groupId ?>">
                                    <button type="submit" name="join_group" class="bg-white text-indigo-700 hover:bg-gray-100 font-medium px-3 py-1.5 rounded-lg shadow-sm transition-all duration-200 text-sm">
                                        <i class="fas fa-user-plus mr-1"></i> 
                                        <?= $group['privacy'] === 'private' ? 'Request to Join' : 'Join Group' ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="login.php" class="bg-white text-indigo-700 hover:bg-gray-100 font-medium px-3 py-1.5 rounded-lg shadow-sm transition-all duration-200 text-sm">
                                <i class="fas fa-sign-in-alt mr-1"></i> Login to Join
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <?php if(isset($_GET['success'])): ?>
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mx-6 mt-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">
                                <?php 
                                $message = "";
                                switch($_GET['success']) {
                                    case 'joined':
                                        $message = "You have joined the group!";
                                        break;
                                    case 'created':
                                        $message = "Group created successfully!";
                                        break;
                                    case 'updated':
                                        $message = "Group updated successfully!";
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
                                    case 'leave_failed':
                                        $message = "Failed to leave the group. Please try again.";
                                        break;
                                    case 'delete_failed':
                                        $message = "Failed to delete the group. Please try again.";
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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="md:col-span-2">
                        <div class="bg-gray-50 p-5 rounded-lg mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">About This Group</h3>
                            <p class="text-gray-700">
                                <?= nl2br(htmlspecialchars($group['description'] ?? 'No description available.')) ?>
                            </p>
                        </div>
                        
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-800">
                                    <i class="fas fa-comments text-indigo-600 mr-2"></i> Discussions
                                </h3>
                                
                                <?php if($memberRole): ?>
                                    <a href="create_discussion.php?group_id=<?= $groupId ?>" class="bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white font-medium px-3 py-1.5 rounded-lg shadow-sm transition-all duration-200 text-sm">
                                        <i class="fas fa-plus mr-1"></i> New Discussion
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Discussions list -->
                            <?php if(!empty($discussions)): ?>
                                <div class="space-y-4">
                                    <?php foreach($discussions as $discussion): ?>
                                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden">
                                            <div class="p-4">
                                                <div class="flex justify-between">
                                                    <h4 class="text-lg font-medium text-gray-900 mb-1">
                                                        <a href="single_discussion.php?id=<?= $discussion['id'] ?>" class="hover:text-indigo-700 transition">
                                                            <?= htmlspecialchars($discussion['title']) ?>
                                                        </a>
                                                    </h4>
                                                    <div class="flex items-center space-x-2 text-gray-500 text-sm">
                                                        <span><i class="far fa-comment-alt mr-1"></i> <?= $discussion['reply_count'] ?></span>
                                                        <span><i class="far fa-heart mr-1"></i> <?= $discussion['like_count'] ?></span>
                                                    </div>
                                                </div>
                                                
                                                <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                                                    <?= htmlspecialchars(substr($discussion['content'], 0, 150)) . (strlen($discussion['content']) > 150 ? '...' : '') ?>
                                                </p>
                                                
                                                <div class="flex items-center justify-between text-xs text-gray-500">
                                                    <div class="flex items-center">
                                                        <!-- <img class="h-6 w-6 rounded-full object-cover mr-2" 
                                                             src="../public/uploads/avatars/default.png" 
                                                             alt="<?= htmlspecialchars($discussion['author_name']) ?>"> -->
                                                        <span>Started by <?= htmlspecialchars($discussion['author_name']) ?></span>
                                                    </div>
                                                    <span><?= date('M d, Y', strtotime($discussion['created_at'])) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <!-- Display this when no discussions are available -->
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                                    <i class="fas fa-comments text-gray-400 text-5xl mb-4"></i>
                                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Discussions Yet</h3>
                                    <?php if($memberRole): ?>
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
                    </div>
                    
                    <!-- Sidebar -->
                    <div class="md:col-span-1">
                        <div class="bg-gray-50 rounded-lg overflow-hidden border border-gray-200">
                            <div class="bg-indigo-50 p-4 border-b border-gray-200">
                                <h3 class="font-semibold text-indigo-800">
                                    <i class="fas fa-users mr-2"></i> Members (<?= count($members) ?>)
                                </h3>
                            </div>
                            
                            <div class="p-4">
                                <ul class="divide-y divide-gray-100">
                                    <?php foreach(array_slice($members, 0, 5) as $member): ?>
                                        <li class="py-2">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    <img class="h-8 w-8 rounded-full object-cover" src="<?= !empty($member['avatar']) ? '..' . $member['avatar'] : '../public/uploads/avatars/default.png' ?>" alt="<?= htmlspecialchars($member['name']) ?>">
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($member['name']) ?></p>
                                                    <div class="flex items-center">
                                                        <?php if($member['role'] === 'admin'): ?>
                                                            <span class="text-xs px-1.5 py-0.5 bg-red-100 text-red-800 rounded">Admin</span>
                                                        <?php elseif($member['role'] === 'moderator'): ?>
                                                            <span class="text-xs px-1.5 py-0.5 bg-blue-100 text-blue-800 rounded">Mod</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                
                                <?php if(count($members) > 5): ?>
                                    <div class="mt-3 text-center">
                                        <a href="members.php?id=<?= $groupId ?>" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                            View All Members
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if(count($members) === 0): ?>
                                    <div class="py-4 text-center text-gray-500">
                                        No members yet.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if($memberRole === 'admin' || $memberRole === 'moderator'): ?>
                            <div class="mt-6 bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                                <h4 class="font-medium text-yellow-800 mb-2">
                                    <i class="fas fa-shield-alt mr-1"></i> Admin Controls
                                </h4>
                                <ul class="space-y-2">
                                <a href="manage_members.php?id=<?= $groupId ?>" class="text-sm text-yellow-700 hover:text-yellow-900 flex items-center">
                                        <i class="fas fa-users-cog mr-2"></i>Manage Members
                                    </a>
                                    <?php if($memberRole === 'admin'): ?>
                                        <li>
                                            <a href="edit_group.php?id=<?= $groupId ?>" class="text-sm text-yellow-700 hover:text-yellow-900 flex items-center">
                                                <i class="fas fa-edit mr-2"></i> Edit Group Settings
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center">
                <a href="groups.php" class="text-indigo-600 hover:text-indigo-800 font-medium transition duration-150">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Home
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
    
    <!-- Delete Group Modal -->
    <?php if($isLoggedIn && $memberRole === 'admin'): ?>
    <div id="deleteGroupModal" aria-hidden="true" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div id="deleteGroupModalBackdrop" class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Group</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to delete this group? This action cannot be undone.
                                    All discussions, replies, and files will be permanently deleted.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form action="../controllers/groupController.php" method="POST">
                        <input type="hidden" name="group_id" value="<?= $groupId ?>">
                        <button type="submit" name="delete_group" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Delete Group
                        </button>
                    </form>
                    <button type="button" id="cancelDeleteGroup" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Modal toggle functions
        const toggleModal = (modalId) => {
            const modal = document.getElementById(modalId);
            modal.classList.toggle('hidden');
        };

        // Get all elements with data-modal-toggle attribute
        document.querySelectorAll('[data-modal-toggle]').forEach(button => {
            const modalId = button.getAttribute('data-modal-toggle');
            button.addEventListener('click', () => toggleModal(modalId));
        });

        // Cancel button for delete group modal
        document.getElementById('cancelDeleteGroup').addEventListener('click', () => {
            toggleModal('deleteGroupModal');
        });

        // Close modal when clicking on backdrop
        document.getElementById('deleteGroupModalBackdrop').addEventListener('click', () => {
            toggleModal('deleteGroupModal');
        });
    </script>
    <?php endif; ?>
</body>
</html>