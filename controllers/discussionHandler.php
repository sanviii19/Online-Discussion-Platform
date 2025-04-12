<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if a session hasn't been started yet
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "../models/Discussion.PHP";
require_once "../models/Reply.php";
require_once "../models/Likes.php";
require_once "../models/Group.php";

// Debug info - uncomment this to see what's coming in
file_put_contents('debug_post.txt', print_r($_POST, true));

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

// Handle create discussion form submission
if (isset($_POST['create_discussion'])) {
    $groupId = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
    $userId = $_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
    // Validate input
    if (empty($title) || empty($content)) {
        header("Location: ../pages/create_discussion.php?group_id=$groupId&error=empty_fields");
        exit();
    }
    
    // Create discussion
    $discussionId = Discussion::create($groupId, $userId, $title, $content);
    
    if ($discussionId) {
        header("Location: ../pages/single_discussion.php?id=$discussionId&success=created");
    } else {
        header("Location: ../pages/create_discussion.php?group_id=$groupId&error=creation_failed");
    }
    exit();
}

// Handle update discussion form submission
if (isset($_POST['update_discussion'])) {
    $discussionId = isset($_POST['discussion_id']) ? intval($_POST['discussion_id']) : 0;
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
    // Validate input
    if (empty($title) || empty($content)) {
        header("Location: ../pages/edit_discussion.php?id=$discussionId&error=empty_fields");
        exit();
    }
    
    // Check ownership
    $discussion = Discussion::getById($discussionId);
    if (!$discussion || $discussion['user_id'] != $_SESSION['user_id']) {
        header("Location: ../pages/single_discussion.php?id=$discussionId&error=unauthorized");
        exit();
    }
    
    // Update discussion
    if (Discussion::update($discussionId, $title, $content)) {
        header("Location: ../pages/single_discussion.php?id=$discussionId&success=updated");
    } else {
        header("Location: ../pages/edit_discussion.php?id=$discussionId&error=update_failed");
    }
    exit();
}

// Handle delete discussion
if (isset($_POST['delete_discussion'])) {
    $discussionId = isset($_POST['discussion_id']) ? intval($_POST['discussion_id']) : 0;
    
    // Get discussion details
    $discussion = Discussion::getById($discussionId);
    if (!$discussion) {
        header("Location: ../pages/discussions.php");
        exit();
    }
    
    $groupId = $discussion['group_id'];
    
    // Check ownership or admin status
    $isAdmin = Group::getMemberRole($groupId, $_SESSION['user_id']) === 'admin';
    if ($discussion['user_id'] != $_SESSION['user_id'] && !$isAdmin) {
        header("Location: ../pages/single_discussion.php?id=$discussionId&error=unauthorized");
        exit();
    }
    
    // Delete discussion
    if (Discussion::delete($discussionId)) {
        header("Location: ../pages/discussions.php?group_id=$groupId&success=deleted");
    } else {
        header("Location: ../pages/single_discussion.php?id=$discussionId&error=delete_failed");
    }
    exit();
}

// Handle add reply
if (isset($_POST['add_reply'])) {
    $discussionId = isset($_POST['discussion_id']) ? intval($_POST['discussion_id']) : 0;
    $content = trim($_POST['content'] ?? '');
    
    // Validate input
    if (empty($content)) {
        header("Location: ../pages/single_discussion.php?id=$discussionId&error=empty_reply");
        exit();
    }
    
    // Check if user is member of the group via the discussion
    $discussion = Discussion::getById($discussionId);
    if (!$discussion) {
        header("Location: ../pages/discussions.php");
        exit();
    }
    
    $groupId = $discussion['group_id'];
    $memberRole = Group::getMemberRole($groupId, $_SESSION['user_id']);
    if (!$memberRole) {
        header("Location: ../pages/single_discussion.php?id=$discussionId&error=unauthorized");
        exit();
    }
    
    // Add reply
    if (Reply::create($discussionId, $_SESSION['user_id'], $content)) {
        header("Location: ../pages/single_discussion.php?id=$discussionId&success=reply_added");
    } else {
        header("Location: ../pages/single_discussion.php?id=$discussionId&error=reply_failed");
    }
    exit();
}

// Handle update reply
if (isset($_POST['update_reply'])) {
    $replyId = isset($_POST['reply_id']) ? intval($_POST['reply_id']) : 0;
    $content = trim($_POST['content'] ?? '');
    
    // Debug info
    file_put_contents('debug_update_reply.txt', "Reply ID: $replyId, Content: $content");
    
    // Validate input
    if (empty($content)) {
        header("Location: ../pages/edit_reply.php?id=$replyId&error=empty_fields");
        exit();
    }
    
    // Check ownership
    $reply = Reply::getById($replyId);
    if (!$reply || $reply['user_id'] != $_SESSION['user_id']) {
        header("Location: ../pages/single_discussion.php?id=" . $reply['discussion_id'] . "&error=unauthorized");
        exit();
    }
    
    // Update reply
    if (Reply::update($replyId, $content)) {
        header("Location: ../pages/single_discussion.php?id=" . $reply['discussion_id'] . "&success=reply_updated");
    } else {
        header("Location: ../pages/edit_reply.php?id=$replyId&error=update_failed");
    }
    exit();
}

// Handle delete reply
if (isset($_POST['delete_reply'])) {
    $replyId = isset($_POST['reply_id']) ? intval($_POST['reply_id']) : 0;
    
    // Get reply details
    $reply = Reply::getById($replyId);
    if (!$reply) {
        header("Location: ../pages/discussions.php");
        exit();
    }
    
    $discussionId = $reply['discussion_id'];
    $discussion = Discussion::getById($discussionId);
    
    // Check ownership or admin status
    $isAdmin = Group::getMemberRole($discussion['group_id'], $_SESSION['user_id']) === 'admin';
    if ($reply['user_id'] != $_SESSION['user_id'] && !$isAdmin) {
        header("Location: ../pages/single_discussion.php?id=$discussionId&error=unauthorized");
        exit();
    }
    
    // Delete reply
    if (Reply::delete($replyId)) {
        header("Location: ../pages/single_discussion.php?id=$discussionId&success=reply_deleted");
    } else {
        header("Location: ../pages/single_discussion.php?id=$discussionId&error=delete_failed");
    }
    exit();
}

// Handle toggle like
if (isset($_POST['toggle_like'])) {
    $contentType = $_POST['content_type'] ?? '';
    $contentId = isset($_POST['content_id']) ? intval($_POST['content_id']) : 0;
    $userId = $_SESSION['user_id'];
    
    if (!in_array($contentType, ['discussion', 'reply']) || !$contentId) {
        header("Location: ../pages/discussions.php");
        exit();
    }
    
    // Get redirect URL
    $redirectUrl = '';
    if ($contentType === 'discussion') {
        $redirectUrl = "../pages/single_discussion.php?id=$contentId";
    } else {
        $reply = Reply::getById($contentId);
        if ($reply) {
            $redirectUrl = "../pages/single_discussion.php?id=" . $reply['discussion_id'];
        } else {
            $redirectUrl = "../pages/discussions.php";
        }
    }
    
    // Toggle like
    if (Like::exists($userId, $contentType, $contentId)) {
        Like::remove($userId, $contentType, $contentId);
    } else {
        Like::add($userId, $contentType, $contentId);
    }
    
    header("Location: $redirectUrl");
    exit();
}

// If we get here, no action was matched
file_put_contents('debug_no_action.txt', "No form action matched for: " . print_r($_POST, true));
header("Location: ../pages/index.php?error=no_action");
exit();