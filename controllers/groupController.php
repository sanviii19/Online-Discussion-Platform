<?php
require_once __DIR__ . '/../models/Group.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Create a new group
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_group'])) {
    if (!isLoggedIn()) {
        header('Location: ../pages/login.php');
        exit();
    }
    
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $privacy = $_POST['privacy'] ?? 'public';
    
    // Log the received form data
    error_log("Form data: name=$name, privacy=$privacy, user_id=" . $_SESSION['user_id']);
    
    if (empty($name)) {
        header('Location: ../pages/create_group.php?error=name_required');
        exit();
    }
    
    $groupId = Group::create($name, $description, $privacy, $_SESSION['user_id']);
    
    // Log the create result
    error_log("Group::create returned: " . ($groupId ? $groupId : "false"));
    
    if ($groupId) {
        error_log("Redirecting to: ../pages/group.php?id=$groupId&success=created");
        header('Location: ../pages/group.php?id=' . $groupId . '&success=created');
    } else {
        error_log("Redirecting to: ../pages/create_group.php?error=creation_failed");
        header('Location: ../pages/create_group.php?error=creation_failed');
    }
    exit();
}

// Update group
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_group'])) {
    if (!isLoggedIn()) {
        header('Location: ../pages/login.php');
        exit();
    }
    
    $groupId = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $privacy = $_POST['privacy'] ?? 'public';
    
    if (empty($name)) {
        header('Location: ../pages/edit_group.php?id=' . $groupId . '&error=name_required');
        exit();
    }
    
    if (Group::update($groupId, $name, $description, $privacy)) {
        header('Location: ../pages/group.php?id=' . $groupId . '&success=updated');
    } else {
        header('Location: ../pages/edit_group.php?id=' . $groupId . '&error=update_failed');
    }
    exit();
}

// Join group
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_group'])) {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
    
    $groupId = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
    
    if ($groupId <= 0) {
        header('Location: ../pages/groups.php?error=invalid_group');
        exit();
    }
    
    // Add debugging
    error_log("Joining group: $groupId for user: " . $_SESSION['user_id']);
    
    $result = Group::joinGroup($groupId, $_SESSION['user_id']);
    
    // Log the join result
    error_log("Join result: " . ($result ? $result : "false"));
    
    if ($result === 'approved') {
        header('Location: ../pages/group.php?id=' . $groupId . '&success=joined');
    } elseif ($result === 'pending') {
        header('Location: ../pages/groups.php?success=request_sent');
    } elseif ($result === 'already_member') {
        header('Location: ../pages/group.php?id=' . $groupId . '&info=already_member');
    } else {
        header('Location: ../pages/groups.php?error=join_failed&reason=' . urlencode($result));
    }
    exit();
}

// Leave group
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leave_group'])) {
    if (!isLoggedIn()) {
        header('Location: ../pages/login.php');
        exit();
    }
    
    $groupId = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
    
    if (Group::leaveGroup($groupId, $_SESSION['user_id'])) {
        header('Location: ../pages/groups.php?success=left_group');
    } else {
        header('Location: ../pages/group.php?id=' . $groupId . '&error=leave_failed');
    }
    exit();
}

// Delete group
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_group'])) {
    if (!isLoggedIn()) {
        header('Location: ../pages/login.php');
        exit();
    }
    
    $groupId = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
    
    // Check if user is admin
    $memberRole = Group::getMemberRole($groupId, $_SESSION['user_id']);
    if ($memberRole !== 'admin') {
        header('Location: ../pages/group.php?id=' . $groupId . '&error=unauthorized');
        exit();
    }
    
    if (Group::delete($groupId)) {
        header('Location: ../pages/groups.php?success=deleted');
    } else {
        header('Location: ../pages/group.php?id=' . $groupId . '&error=delete_failed');
    }
    exit();
}

// Manage members
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manage_member'])) {
    if (!isLoggedIn()) {
        header('Location: ../pages/login.php');
        exit();
    }
    
    $groupId = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $action = $_POST['action'] ?? '';
    
    // Check if user is admin or moderator
    $memberRole = Group::getMemberRole($groupId, $_SESSION['user_id']);
    if ($memberRole !== 'admin' && $memberRole !== 'moderator') {
        header('Location: ../pages/group.php?id=' . $groupId . '&error=unauthorized');
        exit();
    }
    
    $success = false;
    
    switch ($action) {
        case 'approve':
            $success = Group::updateMemberStatus($groupId, $userId, 'approved');
            break;
        case 'reject':
            $success = Group::updateMemberStatus($groupId, $userId, 'rejected');
            break;
        case 'promote':
            // Only admins can promote
            if ($memberRole === 'admin') {
                $success = Group::updateMemberRole($groupId, $userId, 'moderator');
            }
            break;
        case 'demote':
            // Only admins can demote
            if ($memberRole === 'admin') {
                $success = Group::updateMemberRole($groupId, $userId, 'member');
            }
            break;
        case 'remove':
            $success = Group::leaveGroup($groupId, $userId);
            break;
    }
    
    if ($success) {
        header('Location: ../pages/group.php?id=' . $groupId . '&success=member_updated');
    } else {
        header('Location: ../pages/group.php?id=' . $groupId . '&error=member_update_failed');
    }
    exit();
}


// Add this to your existing groupController.php where other POST handlers are
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manage_member'])) {
    if (!isLoggedIn()) {
        header('Location: ../pages/login.php');
        exit();
    }
    
    $groupId = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $action = $_POST['action'] ?? '';
    
    if (!$groupId || !$userId || !$action) {
        header('Location: ../pages/manage_members.php?id=' . $groupId . '&error=invalid_request');
        exit();
    }
    
    // Check if user has permission
    $memberRole = Group::getMemberRole($groupId, $_SESSION['user_id']);
    if (!in_array($memberRole, ['admin', 'moderator'])) {
        header('Location: ../pages/group.php?id=' . $groupId . '&error=unauthorized');
        exit();
    }
    
    $success = false;
    switch ($action) {
        case 'promote':
            if ($memberRole === 'admin') {
                $success = Group::updateMemberRole($groupId, $userId, 'moderator');
            }
            break;
        case 'demote':
            if ($memberRole === 'admin') {
                $success = Group::updateMemberRole($groupId, $userId, 'member');
            }
            break;
        case 'approve':
            $success = Group::updateMemberStatus($groupId, $userId, 'approved');
            break;
        case 'reject':
            $success = Group::updateMemberStatus($groupId, $userId, 'rejected');
            break;
        case 'remove':
            $success = Group::removeMember($groupId, $userId);
            break;
    }
    
    if ($success) {
        header('Location: ../pages/manage_members.php?id=' . $groupId . '&success=' . $action);
    } else {
        header('Location: ../pages/manage_members.php?id=' . $groupId . '&error=action_failed');
    }
    exit();
}

?>