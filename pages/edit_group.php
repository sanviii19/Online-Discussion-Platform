<?php
require_once "../controllers/groupController.php";
require_once "../controllers/authController.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

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

// Check if user is admin of the group
$memberRole = Group::getMemberRole($groupId, $_SESSION['user_id']);
if ($memberRole !== 'admin') {
    header('Location: group.php?id=' . $groupId . '&error=unauthorized');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Group - Konvo</title>
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
        .form-container {
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.85);
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50 py-10">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="form-container rounded-xl shadow-2xl overflow-hidden transition-all duration-300 hover:shadow-[0_20px_50px_rgba(8,_112,_184,_0.7)] max-w-2xl mx-auto">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-white">Edit Group</h2>
                        <p class="text-blue-100 mt-1">Update <?= htmlspecialchars($group['name']) ?> settings</p>
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
            
            <?php if(isset($_GET['error'])): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mx-6 mt-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                <?php
                                switch($_GET['error']) {
                                    case 'name_required':
                                        echo "Group name is required.";
                                        break;
                                    case 'already_exists':
                                        echo "A group with this name already exists.";
                                        break;
                                    default:
                                        echo "There was an error updating the group. Please try again.";
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="../controllers/groupController.php" class="p-6 bg-white space-y-6">
                <input type="hidden" name="group_id" value="<?= $groupId ?>">
                
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Group Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" required 
                           class="w-full py-2 px-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                           value="<?= htmlspecialchars($group['name']) ?>">
                </div>
                
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="4" 
                              class="w-full py-2 px-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"><?= htmlspecialchars($group['description'] ?? '') ?></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Privacy Setting</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="relative bg-white border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-gray-50 transition-all duration-200" onclick="selectPrivacy('public')">
                            <input type="radio" id="privacy_public" name="privacy" value="public" class="absolute opacity-0" <?= $group['privacy'] === 'public' ? 'checked' : '' ?>>
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-8 w-8 text-center">
                                    <i class="fas fa-globe text-green-500 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <label for="privacy_public" class="block text-sm font-medium text-gray-900 cursor-pointer">Public</label>
                                    <p class="text-xs text-gray-500 mt-1">Anyone can see and join this group</p>
                                </div>
                                <div class="ml-auto flex items-center justify-center border-2 border-green-500 rounded-full h-5 w-5 opacity-0 privacy-indicator" id="public_indicator">
                                    <i class="fas fa-check text-green-500 text-xs"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="relative bg-white border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-gray-50 transition-all duration-200" onclick="selectPrivacy('private')">
                            <input type="radio" id="privacy_private" name="privacy" value="private" class="absolute opacity-0" <?= $group['privacy'] === 'private' ? 'checked' : '' ?>>
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-8 w-8 text-center">
                                    <i class="fas fa-lock text-yellow-500 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <label for="privacy_private" class="block text-sm font-medium text-gray-900 cursor-pointer">Private</label>
                                    <p class="text-xs text-gray-500 mt-1">Only members can see and access the group</p>
                                </div>
                                <div class="ml-auto flex items-center justify-center border-2 border-green-500 rounded-full h-5 w-5 opacity-0 privacy-indicator" id="private_indicator">
                                    <i class="fas fa-check text-green-500 text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="pt-4">
                    <button type="submit" name="update_group" 
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-150 transform hover:-translate-y-0.5">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                </div>
            </form>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                <a href="group.php?id=<?= $groupId ?>" class="text-indigo-600 hover:text-indigo-800 font-medium transition duration-150">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Group
                </a>
            </div>
        </div>
        
        <p class="text-center mt-8 text-white text-sm">
            © 2025 Konvo. All rights reserved.
        </p>
    </div>
    
    <script>
        // Function to select privacy option
        function selectPrivacy(option) {
            // Reset all indicators
            document.querySelectorAll('.privacy-indicator').forEach(indicator => {
                indicator.classList.add('opacity-0');
            });
            
            // Show indicator for selected option
            document.getElementById(option + '_indicator').classList.remove('opacity-0');
            
            // Set radio button
            document.getElementById('privacy_' + option).checked = true;
        }
        
        // Initialize the UI based on the selected privacy
        document.addEventListener('DOMContentLoaded', function() {
            <?php if($group['privacy'] === 'private'): ?>
                selectPrivacy('private');
            <?php else: ?>
                selectPrivacy('public');
            <?php endif; ?>
        });
    </script>
</body>
</html>