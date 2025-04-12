<?php
require_once "../controllers/groupController.php";
require_once "../controllers/authController.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get group ID
$groupId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$groupId) {
    header('Location: groups.php');
    exit();
}

// Check if user is admin/moderator
$memberRole = Group::getMemberRole($groupId, $_SESSION['user_id']);
if (!in_array($memberRole, ['admin', 'moderator'])) {
    header('Location: group.php?id=' . $groupId . '&error=unauthorized');
    exit();
}

// Get group details and members
$group = Group::getById($groupId);
$members = Group::getMembers($groupId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members - <?= htmlspecialchars($group['name']) ?></title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body class="min-h-screen bg-gray-50 py-10">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-white">Manage Members</h2>
                        <p class="text-blue-100 mt-1"><?= htmlspecialchars($group['name']) ?></p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($members as $member): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full" 
                                                 src="<?= !empty($member['avatar']) ? '..' . $member['avatar'] : '../public/uploads/avatars/default.png' ?>" 
                                                 alt="<?= htmlspecialchars($member['name']) ?>">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($member['name']) ?></div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($member['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php
                                        switch($member['role']) {
                                            case 'admin':
                                                echo 'bg-purple-100 text-purple-800';
                                                break;
                                            case 'moderator':
                                                echo 'bg-blue-100 text-blue-800';
                                                break;
                                            default:
                                                echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?= ucfirst($member['role']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php
                                        switch($member['status']) {
                                            case 'approved':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                            case 'pending':
                                                echo 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'rejected':
                                                echo 'bg-red-100 text-red-800';
                                                break;
                                        }
                                        ?>">
                                        <?= ucfirst($member['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M j, Y', strtotime($member['joined_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <?php if ($memberRole === 'admin' && $member['role'] !== 'admin'): ?>
                                        <form method="POST" action="../controllers/groupController.php" class="inline">
                                            <input type="hidden" name="group_id" value="<?= $groupId ?>">
                                            <input type="hidden" name="user_id" value="<?= $member['id'] ?>">
                                            <input type="hidden" name="action" value="<?= $member['role'] === 'moderator' ? 'demote' : 'promote' ?>">
                                            <button type="submit" name="manage_member" 
                                                    class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                <?= $member['role'] === 'moderator' ? 'Demote' : 'Promote' ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($member['status'] === 'pending'): ?>
                                        <form method="POST" action="../controllers/groupController.php" class="inline">
                                            <input type="hidden" name="group_id" value="<?= $groupId ?>">
                                            <input type="hidden" name="user_id" value="<?= $member['id'] ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" name="manage_member" 
                                                    class="text-green-600 hover:text-green-900 mr-3">
                                                Approve
                                            </button>
                                        </form>
                                        <form method="POST" action="../controllers/groupController.php" class="inline">
                                            <input type="hidden" name="group_id" value="<?= $groupId ?>">
                                            <input type="hidden" name="user_id" value="<?= $member['id'] ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" name="manage_member" 
                                                    class="text-red-600 hover:text-red-900">
                                                Reject
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($member['role'] !== 'admin'): ?>
                                        <form method="POST" action="../controllers/groupController.php" class="inline">
                                            <input type="hidden" name="group_id" value="<?= $groupId ?>">
                                            <input type="hidden" name="user_id" value="<?= $member['id'] ?>">
                                            <input type="hidden" name="action" value="remove">
                                            <button type="submit" name="manage_member" 
                                                    class="text-red-600 hover:text-red-900 ml-3"
                                                    onclick="return confirm('Are you sure you want to remove this member?')">
                                                Remove
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <a href="group.php?id=<?= $groupId ?>" class="text-indigo-600 hover:text-indigo-900">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Group
                </a>
            </div>
        </div>
    </div>
</body>
</html>