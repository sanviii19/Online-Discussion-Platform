<?php
require_once "../controllers/groupController.php";
require_once "../controllers/authController.php";
require_once "../models/Reply.php";
require_once "../models/Discussion.php";
require_once "../models/Group.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get reply ID
$replyId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$replyId) {
    header('Location: groups.php');
    exit();
}

// Get reply details
$reply = Reply::getById($replyId);
if (!$reply) {
    header('Location: groups.php?error=not_found');
    exit();
}

// Check if user is the owner of the reply
if ($reply['user_id'] != $_SESSION['user_id']) {
    header('Location: single_discussion.php?id=' . $reply['discussion_id'] . '&error=unauthorized');
    exit();
}

// Get discussion details
$discussion = Discussion::getById($reply['discussion_id']);
$groupId = $discussion['group_id'];
$group = Group::getById($groupId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Reply - <?= htmlspecialchars(substr($reply['content'], 0, 30)) . '...' ?></title>
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
                        <h2 class="text-2xl font-bold text-white">Edit Reply</h2>
                        <p class="text-blue-100 mt-1">Update your reply in <?= htmlspecialchars($discussion['title']) ?></p>
                    </div>
                    <div class="bg-white rounded-full p-3 shadow-lg">
                        <svg width="40" height="40" viewBox="0 0 82 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-labelledby="logoTitle logoDesc" role="img">
                            <title id="logoTitle">Discussion Platform Logo</title>
                            <desc id="logoDesc">An abstract logo representing the discussion platform</desc>
                            <path d="M80.2329 5.23828C80.2329 5.17252 80.1763 5.12165 80.1106 5.12523C80.05 5.12854 80 5.17748 80 5.23821V5.34621C80 5.58561 79.8059 5.77969 79.5665 5.77969C79.3271 5.77969 79.1331 5.58561 79.1331 5.34621C79.1331 4.97132 78.923 4.63501 78.6603 4.36759C78.1825 3.88126 77.8926 3.19021 78.0361 2.5724C78.2913 1.47429 78.8953 1 79.9998 1C81.1044 1 81.9998 1.89543 81.9998 3"></path>
                            <path d="M44.1379 8.47245C50.1268 9.49758 56.7104 8.87237 62.5284 10.3048C69.2521 12.3078 73.0441 17.7852 62.0806 19.0682C56.2747 19.519 50.4007 18.5652 44.669 19.5793C38.5986 20.8192 32.3657 22.9076 25.7754 21.8957C19.7385 20.965 14.551 17.2814 14.7102 13.3647C14.6766 9.51719 20.3337 7.31906 25.8121 6.61623C32.1072 5.97034 38.2318 7.31609 44.1379 8.47245Z"></path>
                            <path d="M43.4994 10.2966C48.0627 10.8847 60.2024 11.679 59.8389 15.1157C59.3749 17.1224 49.9027 16.8816 43.7806 17.537C38.4388 18.0667 25.3007 18.968 25.4668 13.5278C25.4233 8.5358 38.8059 9.78382 43.4994 10.2966Z"></path>
                            <path d="M43.4552 12.2781C45.7803 12.4048 51.3363 12.9854 50.8021 14.3169C50.1541 15.5982 45.1892 15.5982 43.5241 15.6495C41.4613 15.6655 35.9911 15.7246 35.7875 13.8409C35.8206 11.9623 41.6284 12.2208 43.4552 12.2781Z"></path>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M40.7609 3.97138C42.4079 4.57062 44.0673 5.17438 45.7804 5.73221C49.2406 7.0955 54.2189 6.5366 59.425 5.95209C64.1786 5.41837 69.1222 4.86332 73.2736 5.73102C77.7162 6.65977 80.1193 10.7879 79.995 14.8028L79.9994 14.797V23.7169C80.026 27.8836 77.3505 32.035 71.4281 32.4793C67.8681 32.7464 64.3959 32.2199 60.9484 31.6971C56.1374 30.9677 51.3747 30.2456 46.3757 31.2248C42.4239 32.0214 38.9046 33.5632 35.6125 35.0037L35.5194 35.0465C28.0637 38.5154 13.3519 35.2316 6.58647 25.7511C-0.0845763 16.2749 3.11471 3.4116 17.5303 1.58135C28.027 0.246031 35.9172 2.54276 40.7609 3.97138Z"></path>
                        </svg>
                    </div>
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
                                    case 'empty_fields':
                                        echo "Please fill in all required fields.";
                                        break;
                                    case 'update_failed':
                                        echo "Failed to update reply. Please try again.";
                                        break;
                                    default:
                                        echo "An error occurred. Please try again.";
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <form action="../controllers/discussionHandler.php" method="POST" class="p-6 bg-white space-y-6">
                <input type="hidden" name="reply_id" value="<?= $replyId ?>">
                
                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Reply Content <span class="text-red-500">*</span></label>
                    <textarea name="content" id="content" rows="8" required
                              class="w-full py-2 px-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                              placeholder="Write your reply here..."><?= htmlspecialchars($reply['content']) ?></textarea>
                </div>
                
                <div class="pt-4 flex space-x-4">
                    <button type="submit" name="update_reply" 
                            class="flex-grow justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-150 transform hover:-translate-y-0.5 flex items-center">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                    
                    <a href="single_discussion.php?id=<?= $reply['discussion_id'] ?>" 
                       class="flex-grow justify-center py-3 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-150 flex items-center text-center">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                </div>
            </form>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                <a href="single_discussion.php?id=<?= $reply['discussion_id'] ?>" class="text-indigo-600 hover:text-indigo-800 font-medium transition duration-150">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Discussion
                </a>
                <a href="discussions.php?group_id=<?= $groupId ?>" class="text-indigo-600 hover:text-indigo-800 font-medium transition duration-150">
                    <i class="fas fa-list mr-1"></i> All Discussions
                </a>
            </div>
        </div>
        
        <p class="text-center mt-8 text-white text-sm">
            © 2025 Discussion Platform. All rights reserved.
        </p>
    </div>
</body>
</html>