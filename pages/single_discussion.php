<?php
require_once "../controllers/groupController.php";
require_once "../controllers/authController.php";
require_once "../models/Discussion.php";
require_once "../models/Reply.php";
require_once "../models/Group.php";
require_once "../models/Likes.php";

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Get discussion ID
$discussionId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$discussionId) {
    header('Location: groups.php');
    exit();
}

// Get discussion details
$discussion = Discussion::getById($discussionId);
if (!$discussion) {
    header('Location: groups.php?error=not_found');
    exit();
}

// Get group details
$groupId = $discussion['group_id'];
$group = Group::getById($groupId);

// Get member role if logged in
$memberRole = null;
if ($isLoggedIn) {
    $memberRole = Group::getMemberRole($groupId, $_SESSION['user_id']);
}

// Check if user is allowed to view
if ($group['privacy'] === 'private' && !$memberRole && $group['created_by'] != $_SESSION['user_id'] && $isLoggedIn) {
    header('Location: groups.php?error=private_group');
    exit();
}

// Get all replies for this discussion
$replies = Reply::getByDiscussionId($discussionId);

// Check if user has liked this discussion
$userLiked = false;
if ($isLoggedIn) {
    $userLiked = Like::exists($_SESSION['user_id'], 'discussion', $discussionId);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($discussion['title']) ?> - Discussion</title>
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
        .reply-content img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50 py-6">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="content-container rounded-xl shadow-2xl overflow-hidden transition-all duration-300 hover:shadow-[0_20px_50px_rgba(8,_112,_184,_0.7)]">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-white line-clamp-1"><?= htmlspecialchars($discussion['title']) ?></h2>
                        <p class="text-blue-100 mt-1">Discussion in <?= htmlspecialchars($group['name']) ?></p>
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
                                    case 'reply_added':
                                        $message = "Reply added successfully!";
                                        break;
                                    case 'reply_deleted':
                                        $message = "Reply deleted successfully!";
                                        break;
                                    case 'updated':
                                        $message = "Discussion updated successfully!";
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
                                    case 'empty_reply':
                                        $message = "Reply cannot be empty.";
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
                <!-- Main Discussion -->
                <div class="bg-gray-50 rounded-lg border border-gray-200 p-5 mb-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($discussion['title']) ?></h1>
                            <div class="text-sm text-gray-500 mt-1">
                                Posted by <?= htmlspecialchars($discussion['author_name']) ?> • <?= date('M d, Y \a\t h:i A', strtotime($discussion['created_at'])) ?>
                            </div>
                        </div>
                        <?php if($isLoggedIn && ($discussion['user_id'] == $_SESSION['user_id'] || $memberRole === 'admin')): ?>
                            <div class="flex space-x-2">
                                <?php if($discussion['user_id'] == $_SESSION['user_id']): ?>
                                    <a href="edit_discussion.php?id=<?= $discussionId ?>" class="text-indigo-600 hover:text-indigo-800">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                <?php endif; ?>
                                <form action="../controllers/discussionHandler.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this discussion? This action cannot be undone.');" class="inline">
                                    <input type="hidden" name="discussion_id" value="<?= $discussionId ?>">
                                    <button type="submit" name="delete_discussion" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="prose prose-indigo max-w-none text-gray-700 mb-4">
                        <?= nl2br(htmlspecialchars($discussion['content'])) ?>
                    </div>
                    
                    <div class="flex items-center justify-between border-t border-gray-200 pt-4 mt-4">
                        <div class="flex items-center space-x-4">
                            <?php if($isLoggedIn): ?>
                                <form action="../controllers/discussionHandler.php" method="POST" class="inline">
                                    <input type="hidden" name="content_type" value="discussion">
                                    <input type="hidden" name="content_id" value="<?= $discussionId ?>">
                                    <button type="submit" name="toggle_like" class="flex items-center text-sm <?= $userLiked ? 'text-pink-600' : 'text-gray-500 hover:text-pink-600' ?> transition duration-150">
                                        <i class="<?= $userLiked ? 'fas' : 'far' ?> fa-heart mr-1"></i> Like
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="flex items-center text-sm text-gray-500">
                                    <i class="far fa-heart mr-1"></i> Like
                                </span>
                            <?php endif; ?>
                            
                            <button onclick="document.getElementById('replyForm').scrollIntoView({behavior: 'smooth'})" class="flex items-center text-sm text-gray-500 hover:text-indigo-600 transition duration-150">
                                <i class="far fa-comment-alt mr-1"></i> Reply
                            </button>
                            
                            <button onclick="shareDiscussion()" class="flex items-center text-sm text-gray-500 hover:text-indigo-600 transition duration-150">
                                <i class="fas fa-share mr-1"></i> Share
                            </button>
                        </div>
                        
                        <div class="text-sm text-gray-500">
                            <span class="mr-2"><i class="fas fa-heart text-pink-500 mr-1"></i> <?= $discussion['like_count'] ?? 0 ?></span>
                            <span><i class="fas fa-comment-alt text-indigo-500 mr-1"></i> <?= count($replies) ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Replies -->
                <div class="mb-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">
                        <i class="fas fa-reply text-indigo-600 mr-2"></i> Replies
                    </h3>
                    
                    <?php if(count($replies) > 0): ?>
                        <div class="space-y-4">
                            <?php foreach($replies as $reply): ?>
                                <div id="reply-<?= $reply['id'] ?>" class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                                    <div class="flex justify-between items-start">
                                        <div class="text-sm text-gray-500">
                                            <?= htmlspecialchars($reply['author_name']) ?> • <?= date('M d, Y \a\t h:i A', strtotime($reply['created_at'])) ?>
                                        </div>
                                        <?php if($isLoggedIn && ($reply['user_id'] == $_SESSION['user_id'] || $memberRole === 'admin')): ?>
                                            <div class="flex space-x-2">
                                                <?php if($reply['user_id'] == $_SESSION['user_id']): ?>
                                                    <a href="edit_reply.php?id=<?= $reply['id'] ?>" class="text-indigo-600 hover:text-indigo-800">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <form action="../controllers/discussionHandler.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this reply?');" class="inline">
                                                    <input type="hidden" name="reply_id" value="<?= $reply['id'] ?>">
                                                    <button type="submit" name="delete_reply" class="text-red-600 hover:text-red-800">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="prose prose-indigo max-w-none text-gray-700 mt-2 mb-2 reply-content">
                                        <?= nl2br(htmlspecialchars($reply['content'])) ?>
                                    </div>
                                    
                                    <div class="flex items-center justify-between border-t border-gray-200 pt-3 mt-3">
                                        <div class="flex items-center space-x-4">
                                            <?php 
                                            $replyLiked = false;
                                            if ($isLoggedIn) {
                                                $replyLiked = Like::exists($_SESSION['user_id'], 'reply', $reply['id']);
                                            }
                                            ?>
                                            
                                            <?php if($isLoggedIn): ?>
                                                <form action="../controllers/discussionHandler.php" method="POST" class="inline">
                                                    <input type="hidden" name="content_type" value="reply">
                                                    <input type="hidden" name="content_id" value="<?= $reply['id'] ?>">
                                                    <button type="submit" name="toggle_like" class="flex items-center text-sm <?= $replyLiked ? 'text-pink-600' : 'text-gray-500 hover:text-pink-600' ?> transition duration-150">
                                                        <i class="<?= $replyLiked ? 'fas' : 'far' ?> fa-heart mr-1"></i> Like
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="flex items-center text-sm text-gray-500">
                                                    <i class="far fa-heart mr-1"></i> Like
                                                </span>
                                            <?php endif; ?>
                                            
                                            <button onclick="quoteReply(<?= $reply['id'] ?>)" class="flex items-center text-sm text-gray-500 hover:text-indigo-600 transition duration-150">
                                                <i class="fas fa-quote-right mr-1"></i> Quote
                                            </button>
                                        </div>
                                        
                                        <div class="text-sm text-gray-500">
                                            <span class="mr-2"><i class="fas fa-heart text-pink-500 mr-1"></i> <?= $reply['like_count'] ?? 0 ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="bg-gray-50 rounded-lg p-6 text-center">
                            <i class="fas fa-comment-slash text-gray-400 text-4xl mb-3"></i>
                            <h4 class="text-lg font-semibold text-gray-700 mb-2">No Replies Yet</h4>
                            <p class="text-gray-600">Be the first to reply to this discussion!</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Reply Form -->
                <?php if($isLoggedIn && $memberRole): ?>
                    <div id="replyForm" class="bg-gray-50 rounded-lg border border-gray-200 p-5">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">
                            <i class="fas fa-reply text-indigo-600 mr-2"></i> Add Your Reply
                        </h3>
                        
                        <form action="../controllers/discussionHandler.php" method="POST">
                            <input type="hidden" name="discussion_id" value="<?= $discussionId ?>">
                            
                            <div class="mb-4">
                                <textarea name="content" id="replyContent" rows="5" required
                                          class="w-full py-2 px-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                                          placeholder="Write your reply here..."></textarea>
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="submit" name="add_reply" 
                                        class="flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-150">
                                    <i class="fas fa-paper-plane mr-2"></i> Post Reply
                                </button>
                            </div>
                        </form>
                    </div>
                <?php elseif(!$isLoggedIn): ?>
                    <div class="bg-blue-50 rounded-lg border border-blue-200 p-5 text-center">
                        <h3 class="text-lg font-semibold text-blue-800 mb-2">Join the Discussion</h3>
                        <p class="text-blue-700 mb-3">Sign in to reply to this discussion and interact with other members.</p>
                        <a href="login.php" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-150">
                            <i class="fas fa-sign-in-alt mr-2"></i> Sign In
                        </a>
                    </div>
                <?php else: ?>
                    <div class="bg-yellow-50 rounded-lg border border-yellow-200 p-5 text-center">
                        <h3 class="text-lg font-semibold text-yellow-800 mb-2">Join This Group</h3>
                        <p class="text-yellow-700 mb-3">You need to be a member of this group to participate in discussions.</p>
                        <form action="../controllers/groupController.php" method="POST" class="inline">
                            <input type="hidden" name="group_id" value="<?= $groupId ?>">
                            <button type="submit" name="join_group" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-150">
                                <i class="fas fa-user-plus mr-2"></i> Join Group
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center">
                <a href="discussions.php?group_id=<?= $groupId ?>" class="text-indigo-600 hover:text-indigo-800 font-medium transition duration-150">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Discussions
                </a>
                <a href="group.php?id=<?= $groupId ?>" class="text-indigo-600 hover:text-indigo-800 font-medium transition duration-150 mt-2 sm:mt-0">
                    <i class="fas fa-users mr-1"></i> View Group
                </a>
            </div>
        </div>
        
        <p class="text-center mt-8 text-white text-sm">
            © 2025 Discussion Platform. All rights reserved.
        </p>
    </div>

    <script>
        function quoteReply(replyId) {
            const replyElement = document.getElementById('reply-' + replyId);
            const replyContent = replyElement.querySelector('.reply-content').textContent.trim();
            const replyAuthor = replyElement.querySelector('.text-gray-500').textContent.split('•')[0].trim();
            
            const textarea = document.getElementById('replyContent');
            const quote = `> ${replyAuthor} wrote:\n> ${replyContent}\n\n`;
            
            textarea.value = quote + textarea.value;
            textarea.focus();
            textarea.scrollIntoView({behavior: 'smooth'});
        }
        
        function shareDiscussion() {
            // Copy current URL to clipboard
            navigator.clipboard.writeText(window.location.href).then(() => {
                alert('Link copied to clipboard!');
            }).catch(err => {
                console.error('Could not copy text: ', err);
            });
        }
    </script>
</body>
</html>