<?php
require_once "../controllers/groupController.php";
require_once "../controllers/authController.php";
require_once "../models/Discussion.php";
require_once "../models/Reply.php";
require_once "../models/Group.php";
require_once "../models/Likes.php";
require_once "../models/File.php";


// Function to make URLs clickable
function makeLinksClickable($text) {
    // Regular expression to match URLs
    $pattern = '/(https?:\/\/[^\s]+)/i';
    
    // Replace URLs with HTML anchor tags
    $replacement = '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 underline break-words">$1</a>';
    
    return preg_replace($pattern, $replacement, $text);
}

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
    <div id="success-message" class="bg-green-50 border-l-4 border-green-500 p-4">
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
                            $message = "Discussion created successfully!";
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
                        <?= nl2br(makeLinksClickable(htmlspecialchars($discussion['content']))) ?>
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
                            
                            <?php if($isLoggedIn): ?>
                                <button onclick="askAi()" class="flex items-center text-sm text-blue-600 hover:text-blue-800 transition duration-150">
                                    <i class="fas fa-robot mr-1"></i> <span id="aiButtonText">Ask AI</span>
                                </button>
                            <?php endif; ?>
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
                                        <?= nl2br(makeLinksClickable(htmlspecialchars($reply['content']))) ?>
                                        
                                        <?php
                                        // Get files attached to this reply
                                        $files = Reply::getFiles($reply['id']);
                                        if (!empty($files)):
                                        ?>
                                        <div class="mt-3 pt-3 border-t border-gray-200">
                                            <h4 class="text-sm font-medium text-gray-700 mb-2">
                                                <i class="fas fa-paperclip mr-1"></i> Attachments (<?= count($files) ?>)
                                            </h4>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                <?php foreach ($files as $file): ?>
                                                    <?php 
                                                        $fileIcon = File::getFileIcon(pathinfo($file['file_name'], PATHINFO_EXTENSION));
                                                        $isImage = in_array(strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                    ?>
                                                    <div class="flex items-center p-2 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                                                        <div class="mr-3 text-center">
                                                            <i class="fas <?= $fileIcon ?> text-2xl <?= $isImage ? 'text-green-500' : 'text-blue-500' ?>"></i>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <a href="../<?= $file['file_path'] ?>" target="_blank" class="block text-sm font-medium text-blue-600 hover:text-blue-800 truncate">
                                                                <?= htmlspecialchars($file['file_name']) ?>
                                                            </a>
                                                            <div class="flex items-center text-xs text-gray-500">
                                                                <span><?= File::formatSize($file['file_size']) ?></span>
                                                                <span class="mx-1">•</span>
                                                                <span><?= date('M d, Y', strtotime($file['uploaded_at'])) ?></span>
                                                            </div>
                                                        </div>
                                                        <?php if($isLoggedIn && ($file['user_id'] == $_SESSION['user_id'] || $memberRole === 'admin')): ?>
                                                            <button onclick="deleteFile(<?= $file['id'] ?>)" class="ml-2 text-red-500 hover:text-red-700">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
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
                    <div id="replyForm" class="bg-gray-50 rounded-lg border border-gray-200 p-5 mt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">
                            <i class="fas fa-reply text-indigo-600 mr-2"></i> Add Your Reply
                        </h3>
                        
                        <form action="../controllers/discussionHandler.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="discussion_id" value="<?= $discussionId ?>">
                            
                            <div class="mb-4">
                                <textarea name="content" id="replyContent" rows="5"
                                          class="w-full py-2 px-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                                          placeholder="Write your reply here..."></textarea>
                            </div>
                            
                            <!-- File Upload Section -->
                            <div class="mb-4">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-paperclip text-gray-500 mr-2"></i>
                                    <span class="text-sm font-medium text-gray-700">Attachments</span>
                                </div>
                                
                                <div class="file-upload-container border border-dashed border-gray-300 rounded-lg p-4 bg-gray-50 hover:bg-gray-100 transition">
                                    <div class="flex flex-col items-center justify-center cursor-pointer" id="dropzone">
                                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                        <p class="text-sm text-gray-500 mb-1">Drag and drop files here or click to browse</p>
                                        <p class="text-xs text-gray-400">Supported formats: PDF, Images, Documents (Max 10MB)</p>
                                        
                                        <input type="file" name="attachments[]" id="file-upload" class="hidden" multiple 
                                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif">
                                    </div>
                                    
                                    <div class="mt-3 hidden" id="selected-files">
                                        <div class="text-xs font-medium text-gray-700 mb-2">Selected Files:</div>
                                        <ul id="file-list" class="space-y-1"></ul>
                                    </div>
                                </div>
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
                    <div class="bg-gray-50 rounded-lg border border-gray-200 p-5 text-center mt-6">
                        <p class="text-gray-700 mb-3">You need to be logged in to reply to discussions.</p>
                        <a href="../pages/login.php" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-150">
                            Login to Reply
                        </a>
                    </div>
                <?php else: ?>
                    <div class="bg-gray-50 rounded-lg border border-gray-200 p-5 text-center mt-6">
                        <p class="text-gray-700 mb-3">You need to be a member of this group to reply to discussions.</p>
                        <form action="../controllers/groupController.php" method="POST">
                            <input type="hidden" name="group_id" value="<?= $groupId ?>">
                            <button type="submit" name="join_group" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-150">
                                Join Group
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
            © 2025 Konvo. All rights reserved.
        </p>
    </div>

    <!-- AI Response Modal (Hidden by default) -->
    <div id="aiResponseSection" class="fixed inset-0 flex items-center justify-center z-50 hidden">
        <div class="fixed inset-0 bg-black/10 bg-opacity-50" onclick="toggleAiResponse()"></div>
        <div class="content-container rounded-xl shadow-2xl overflow-hidden transition-all duration-300 max-w-2xl w-full mx-4 relative z-10">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white">
                        <i class="fas fa-robot mr-2"></i> AI Response
                    </h3>
                    <button onclick="toggleAiResponse()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="p-6 bg-white">
                <div id="aiResponseContent" class="bg-gray-50 rounded-lg border border-gray-200 p-4 prose prose-indigo max-w-none">
                    <div id="aiLoadingIndicator" class="flex items-center justify-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                        <span class="ml-3 text-gray-600">Generating AI response...</span>
                    </div>
                    <div id="aiResponseText" class="hidden"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Existing functions
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
            navigator.clipboard.writeText(window.location.href).then(() => {
                alert('Link copied to clipboard!');
            }).catch(err => {
                console.error('Could not copy text: ', err);
            });
        }

        // Simplified AI Response Functions
        const API_KEY = "AIzaSyD7roQlayvnjQRp88Ej-BsQYGMnk_Ja9xw";
        const API_URL = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent";
        let aiResponseVisible = false;

        function toggleAiResponse() {
            const aiSection = document.getElementById('aiResponseSection');
            const aiButton = document.getElementById('aiButtonText');
            
            if (aiResponseVisible) {
                aiSection.classList.add('hidden');
                aiButton.textContent = 'Ask AI';
            } else {
                aiSection.classList.remove('hidden');
                aiButton.textContent = 'Hide AI';
                getAiResponse();
            }
            
            aiResponseVisible = !aiResponseVisible;
        }

        function askAi() {
            toggleAiResponse();
        }

        async function getAiResponse() {
            // Show loading indicator
            document.getElementById('aiLoadingIndicator').classList.remove('hidden');
            document.getElementById('aiResponseText').classList.add('hidden');
            
            // Get discussion content
            const discussionTitle = "<?= addslashes(htmlspecialchars($discussion['title'])) ?>";
            const discussionContent = "<?= addslashes(str_replace("\n", " ", htmlspecialchars($discussion['content']))) ?>";
            
            // Create a custom prompt
            const customPrompt = `You are a helpful AI assistant providing insights about discussions. 
Here's the discussion:

Title: ${discussionTitle}
Content: ${discussionContent}

Please provide a concise, helpful response (maximum 1000 characters) that:
1. Summarizes the key points
2. Offers additional relevant information
3. Suggests related topics or questions for further discussion

Keep your response informative but brief.`;

            try {
                const response = await fetch(`${API_URL}?key=${API_KEY}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        contents: [
                            {
                                role: "user",
                                parts: [{ text: customPrompt }]
                            }
                        ],
                        generationConfig: {
                            temperature: 0.7,
                            maxOutputTokens: 1000,
                        }
                    })
                });

                const data = await response.json();
                
                // Hide loading indicator
                document.getElementById('aiLoadingIndicator').classList.add('hidden');
                document.getElementById('aiResponseText').classList.remove('hidden');
                
                if (data.candidates && data.candidates[0].content) {
                    const aiText = data.candidates[0].content.parts[0].text;
                    document.getElementById('aiResponseText').innerHTML = marked.parse(aiText);
                } else {
                    document.getElementById('aiResponseText').innerHTML = '<p class="text-red-600">Sorry, I couldn\'t generate a response. Please try again later.</p>';
                }
                
            } catch (error) {
                console.error('Error fetching AI response:', error);
                document.getElementById('aiLoadingIndicator').classList.add('hidden');
                document.getElementById('aiResponseText').classList.remove('hidden');
                document.getElementById('aiResponseText').innerHTML = `<p class="text-red-600">Error: ${error.message || 'Something went wrong. Please try again.'}</p>`;
            }
        }

        // File Upload Handling
        document.addEventListener('DOMContentLoaded', function() {
            const dropzone = document.getElementById('dropzone');
            const fileInput = document.getElementById('file-upload');
            const fileList = document.getElementById('file-list');
            const selectedFiles = document.getElementById('selected-files');
            
            if (dropzone && fileInput) {
                // Click to browse
                dropzone.addEventListener('click', function(e) {
                    fileInput.click();
                });
                
                // Drag and drop
                dropzone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    dropzone.classList.add('bg-blue-50', 'border-blue-300');
                });
                
                dropzone.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    dropzone.classList.remove('bg-blue-50', 'border-blue-300');
                });
                
                dropzone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    dropzone.classList.remove('bg-blue-50', 'border-blue-300');
                    
                    if (e.dataTransfer.files.length) {
                        fileInput.files = e.dataTransfer.files;
                        updateFileList();
                    }
                });
                
                // File selection change
                fileInput.addEventListener('change', updateFileList);
                
                function updateFileList() {
                    fileList.innerHTML = '';
                    
                    if (fileInput.files.length > 0) {
                        selectedFiles.classList.remove('hidden');
                        
                        for (let i = 0; i < fileInput.files.length; i++) {
                            const file = fileInput.files[i];
                            const fileSize = formatFileSize(file.size);
                            const fileType = file.name.split('.').pop().toLowerCase();
                            const fileIcon = getFileIconClass(fileType);
                            
                            const fileItem = document.createElement('li');
                            fileItem.className = 'flex items-center text-xs';
                            fileItem.innerHTML = `
                                <i class="fas ${fileIcon} text-indigo-500 mr-2"></i>
                                <span class="truncate max-w-xs">${file.name}</span>
                                <span class="ml-auto text-gray-500">${fileSize}</span>
                            `;
                            
                            fileList.appendChild(fileItem);
                        }
                    } else {
                        selectedFiles.classList.add('hidden');
                    }
                }
                
                function formatFileSize(bytes) {
                    if (bytes === 0) return '0 B';
                    const k = 1024;
                    const sizes = ['B', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                }
                
                function getFileIconClass(extension) {
                    const iconMap = {
                        'pdf': 'fa-file-pdf',
                        'doc': 'fa-file-word', 'docx': 'fa-file-word',
                        'xls': 'fa-file-excel', 'xlsx': 'fa-file-excel',
                        'ppt': 'fa-file-powerpoint', 'pptx': 'fa-file-powerpoint',
                        'jpg': 'fa-file-image', 'jpeg': 'fa-file-image', 'png': 'fa-file-image', 
                        'gif': 'fa-file-image', 'svg': 'fa-file-image', 'webp': 'fa-file-image',
                        'mp4': 'fa-file-video', 'avi': 'fa-file-video', 'mov': 'fa-file-video',
                        'mp3': 'fa-file-audio', 'wav': 'fa-file-audio', 'ogg': 'fa-file-audio',
                        'zip': 'fa-file-archive', 'rar': 'fa-file-archive', '7z': 'fa-file-archive',
                        'txt': 'fa-file-alt'
                    };
                    
                    return iconMap[extension] || 'fa-file';
                }
            }
        });

        // Add this to debug the file selection 
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('file-upload');
            
            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    console.log('Files selected:', this.files);
                });
                
                const form = fileInput.closest('form');
                if (form) {
                    form.addEventListener('submit', function() {
                        console.log('Form submitted with files:', fileInput.files);
                    });
                }
            }
        });

        // File Deletion
        function deleteFile(fileId) {
            if (confirm('Are you sure you want to delete this file? This action cannot be undone.')) {
                fetch('../controllers/discussionHandler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `delete_file=true&file_id=${fileId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Could not delete file'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the file');
                });
            }
        }
    </script>

    <!-- Add marked.js for Markdown rendering -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
</body>
</html>