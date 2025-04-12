<?php
require_once 'models/Discussion.php';
require_once 'models/Reply.php';
require_once 'models/Likes.php';
require_once 'models/Group.php';

class DiscussionController {
    // List all discussions for a group
    public function index($groupId) {
        // Check if the user is a member of this group
        if (!Group::getMemberRole($groupId, $_SESSION['user_id'])) {
            $_SESSION['message'] = 'You are not a member of this group';
            header('Location: index.php?page=groups');
            return;
        }
        
        $group = Group::getById($groupId);
        $discussions = Discussion::getByGroupId($groupId);
        
        include 'views/discussions/index.php';
    }

    // Show a single discussion with replies
    public function show($discussionId) {
        $discussion = Discussion::getById($discussionId);
        
        if (!$discussion) {
            $_SESSION['message'] = 'Discussion not found';
            header('Location: index.php?page=groups');
            return;
        }
        
        // Check if the user is a member of this group
        if (!Group::getMemberRole($discussion['group_id'], $_SESSION['user_id'])) {
            $_SESSION['message'] = 'You are not a member of this group';
            header('Location: index.php?page=groups');
            return;
        }
        
        $replies = Reply::getByDiscussionId($discussionId);
        $group = Group::getById($discussion['group_id']);
        $isLiked = Like::exists($_SESSION['user_id'], 'discussion', $discussionId);
        
        include 'views/discussions/show.php';
    }

    // Display form to create a new discussion
    public function create($groupId) {
        // Check if the user is a member of this group
        if (!Group::getMemberRole($groupId, $_SESSION['user_id'])) {
            $_SESSION['message'] = 'You are not a member of this group';
            header('Location: index.php?page=groups');
            return;
        }
        
        $group = Group::getById($groupId);
        include 'views/discussions/create.php';
    }

    // Process form submission to create a discussion
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=groups');
            return;
        }
        
        $groupId = $_POST['group_id'];
        $userId = $_SESSION['user_id'];
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        
        // Simple validation
        if (empty($title) || empty($content)) {
            $_SESSION['message'] = 'Please fill in all fields';
            header("Location: index.php?page=discussions&action=create&group_id=$groupId");
            return;
        }
        
        // Create the discussion
        $discussionId = Discussion::create($groupId, $userId, $title, $content);
        
        if ($discussionId) {
            $_SESSION['message'] = 'Discussion created successfully';
            header("Location: index.php?page=discussions&action=show&id=$discussionId");
        } else {
            $_SESSION['message'] = 'Failed to create discussion';
            header("Location: index.php?page=discussions&action=create&group_id=$groupId");
        }
    }

    // Display form to edit a discussion
    public function edit($discussionId) {
        $discussion = Discussion::getById($discussionId);
        
        if (!$discussion) {
            $_SESSION['message'] = 'Discussion not found';
            header('Location: index.php?page=groups');
            return;
        }
        
        // Check if the user is the owner
        if ($discussion['user_id'] != $_SESSION['user_id']) {
            $_SESSION['message'] = 'You can only edit your own discussions';
            header("Location: index.php?page=discussions&action=show&id=$discussionId");
            return;
        }
        
        $group = Group::getById($discussion['group_id']);
        include 'views/discussions/edit.php';
    }

    // Process form submission to update a discussion
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=groups');
            return;
        }
        
        $discussionId = $_POST['discussion_id'];
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        
        $discussion = Discussion::getById($discussionId);
        
        // Check ownership
        if ($discussion['user_id'] != $_SESSION['user_id']) {
            $_SESSION['message'] = 'You can only edit your own discussions';
            header("Location: index.php?page=discussions&action=show&id=$discussionId");
            return;
        }
        
        // Simple validation
        if (empty($title) || empty($content)) {
            $_SESSION['message'] = 'Please fill in all fields';
            header("Location: index.php?page=discussions&action=edit&id=$discussionId");
            return;
        }
        
        // Update the discussion
        if (Discussion::update($discussionId, $title, $content)) {
            $_SESSION['message'] = 'Discussion updated successfully';
        } else {
            $_SESSION['message'] = 'Failed to update discussion';
        }
        header("Location: index.php?page=discussions&action=show&id=$discussionId");
    }

    // Delete a discussion
    public function delete($discussionId) {
        $discussion = Discussion::getById($discussionId);
        
        if (!$discussion) {
            $_SESSION['message'] = 'Discussion not found';
            header('Location: index.php?page=groups');
            return;
        }
        
        // Check ownership or admin status
        $isAdmin = Group::getMemberRole($discussion['group_id'], $_SESSION['user_id']) === 'admin';
        
        if ($discussion['user_id'] != $_SESSION['user_id'] && !$isAdmin) {
            $_SESSION['message'] = 'You can only delete your own discussions';
            header("Location: index.php?page=discussions&action=show&id=$discussionId");
            return;
        }
        
        // Delete the discussion
        if (Discussion::delete($discussionId)) {
            $_SESSION['message'] = 'Discussion deleted successfully';
        } else {
            $_SESSION['message'] = 'Failed to delete discussion';
        }
        header("Location: index.php?page=groups&action=show&id=" . $discussion['group_id']);
    }

    // Add a reply to a discussion
    public function addReply() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=groups');
            return;
        }
        
        $discussionId = $_POST['discussion_id'];
        $userId = $_SESSION['user_id'];
        $content = trim($_POST['content']);
        $parentId = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
        
        // Simple validation
        if (empty($content)) {
            $_SESSION['message'] = 'Reply cannot be empty';
            header("Location: index.php?page=discussions&action=show&id=$discussionId");
            return;
        }
        
        // Create the reply
        if (Reply::create($discussionId, $userId, $content, $parentId)) {
            $_SESSION['message'] = 'Reply added successfully';
        } else {
            $_SESSION['message'] = 'Failed to add reply';
        }
        header("Location: index.php?page=discussions&action=show&id=$discussionId");
    }

    // Edit a reply
    public function editReply($replyId) {
        $reply = Reply::getById($replyId);
        
        if (!$reply) {
            $_SESSION['message'] = 'Reply not found';
            header('Location: index.php?page=groups');
            return;
        }
        
        // Check ownership
        if ($reply['user_id'] != $_SESSION['user_id']) {
            $_SESSION['message'] = 'You can only edit your own replies';
            header("Location: index.php?page=discussions&action=show&id=" . $reply['discussion_id']);
            return;
        }
        
        $discussion = Discussion::getById($reply['discussion_id']);
        include 'views/discussions/edit_reply.php';
    }

    // Update a reply
    public function updateReply() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=groups');
            return;
        }
        
        $replyId = $_POST['reply_id'];
        $content = trim($_POST['content']);
        
        $reply = Reply::getById($replyId);
        
        // Check ownership
        if ($reply['user_id'] != $_SESSION['user_id']) {
            $_SESSION['message'] = 'You can only edit your own replies';
            header("Location: index.php?page=discussions&action=show&id=" . $reply['discussion_id']);
            return;
        }
        
        // Simple validation
        if (empty($content)) {
            $_SESSION['message'] = 'Reply cannot be empty';
            header("Location: index.php?page=discussions&action=editReply&id=$replyId");
            return;
        }
        
        // Update the reply
        if (Reply::update($replyId, $content)) {
            $_SESSION['message'] = 'Reply updated successfully';
        } else {
            $_SESSION['message'] = 'Failed to update reply';
        }
        header("Location: index.php?page=discussions&action=show&id=" . $reply['discussion_id']);
    }

    // Delete a reply
    public function deleteReply($replyId) {
        $reply = Reply::getById($replyId);
        
        if (!$reply) {
            $_SESSION['message'] = 'Reply not found';
            header('Location: index.php?page=groups');
            return;
        }
        
        $discussion = Discussion::getById($reply['discussion_id']);
        
        // Check ownership or admin status
        $isAdmin = Group::getMemberRole($discussion['group_id'], $_SESSION['user_id']) === 'admin';
        
        if ($reply['user_id'] != $_SESSION['user_id'] && !$isAdmin) {
            $_SESSION['message'] = 'You can only delete your own replies';
            header("Location: index.php?page=discussions&action=show&id=" . $reply['discussion_id']);
            return;
        }
        
        // Delete the reply
        if (Reply::delete($replyId)) {
            $_SESSION['message'] = 'Reply deleted successfully';
        } else {
            $_SESSION['message'] = 'Failed to delete reply';
        }
        header("Location: index.php?page=discussions&action=show&id=" . $reply['discussion_id']);
    }

    // Like or unlike a discussion or reply
    public function toggleLike() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=groups');
            return;
        }
        
        $contentType = $_POST['content_type']; // 'discussion' or 'reply'
        $contentId = $_POST['content_id'];
        $userId = $_SESSION['user_id'];
        
        // Check if already liked
        if (Like::exists($userId, $contentType, $contentId)) {
            // Unlike
            Like::remove($userId, $contentType, $contentId);
            $message = 'Like removed';
        } else {
            // Like
            Like::add($userId, $contentType, $contentId);
            $message = 'Like added';
        }
        
        // If it's an AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $count = Like::getCount($contentType, $contentId);
            echo json_encode(['message' => $message, 'count' => $count]);
            exit;
        }
        
        // If it's a regular form submission
        if ($contentType == 'discussion') {
            header("Location: index.php?page=discussions&action=show&id=$contentId");
        } else {
            $reply = Reply::getById($contentId);
            header("Location: index.php?page=discussions&action=show&id=" . $reply['discussion_id']);
        }
    }
}