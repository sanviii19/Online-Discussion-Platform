<?php
require_once __DIR__ . '/../config/db.php';

class Group {
    // Create a new study group
    
    public static function create($name, $description, $privacy, $created_by) {
        global $pdo;
        try {
            // Log the input parameters for debugging
            error_log("Creating group: Name=$name, Privacy=$privacy, Created by=$created_by");
            
            // Begin transaction for better error handling
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO study_groups (name, description, privacy, created_by) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([$name, $description, $privacy, $created_by]);
            
            if ($result) {
                $groupId = $pdo->lastInsertId();
                error_log("Group created with ID: $groupId");
                
                // Add creator as admin member
                $memberStmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id, role, status) VALUES (?, ?, 'admin', 'approved')");
                $memberResult = $memberStmt->execute([$groupId, $created_by]);
                
                if (!$memberResult) {
                    // If adding member fails, log the error and roll back
                    error_log("Failed to add creator as admin: " . json_encode($memberStmt->errorInfo()));
                    $pdo->rollBack();
                    return false;
                }
                
                // Commit the transaction
                $pdo->commit();
                error_log("Group creation successful. Creator added as admin.");
                return $groupId;
            } else {
                // Log the SQL error
                error_log("Group insertion failed: " . json_encode($stmt->errorInfo()));
                $pdo->rollBack();
                return false;
            }
        } catch (PDOException $e) {
            // Catch and log any exceptions
            error_log("Group creation exception: " . $e->getMessage());
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return false;
        }
    }

    // Get group by ID
    public static function getById($id) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT g.*, u.name as creator_name 
                                  FROM study_groups g
                                  JOIN users u ON g.created_by = u.id
                                  WHERE g.id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get group error: " . $e->getMessage());
            return false;
        }
    }
    
    // Update group details
    public static function update($id, $name, $description, $privacy) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("UPDATE study_groups SET name = ?, description = ?, privacy = ? WHERE id = ?");
            return $stmt->execute([$name, $description, $privacy, $id]);
        } catch (PDOException $e) {
            error_log("Update group error: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete a group
    public static function delete($id) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("DELETE FROM study_groups WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Delete group error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get all groups (with proper privacy filtering)
    public static function getAllGroups($userId = null) {
        global $pdo;
        try {
            // If user is logged in, show public groups and private groups they're a member of
            if ($userId) {
                $stmt = $pdo->prepare("
                    SELECT g.*, u.name as creator_name, 
                           COUNT(DISTINCT gm.user_id) as member_count,
                           (SELECT COUNT(*) FROM group_members WHERE group_id = g.id AND user_id = ?) as is_member
                    FROM study_groups g
                    JOIN users u ON g.created_by = u.id
                    LEFT JOIN group_members gm ON g.id = gm.group_id
                    WHERE g.privacy = 'public' OR g.id IN (
                        SELECT group_id FROM group_members WHERE user_id = ?
                    )
                    GROUP BY g.id
                    ORDER BY g.created_at DESC
                ");
                $stmt->execute([$userId, $userId]);
            } else {
                // If not logged in, show only public groups
                $stmt = $pdo->prepare("
                    SELECT g.*, u.name as creator_name, 
                           COUNT(DISTINCT gm.user_id) as member_count,
                           0 as is_member
                    FROM study_groups g
                    JOIN users u ON g.created_by = u.id
                    LEFT JOIN group_members gm ON g.id = gm.group_id
                    WHERE g.privacy = 'public'
                    GROUP BY g.id
                    ORDER BY g.created_at DESC
                ");
                $stmt->execute();
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get all groups error: " . $e->getMessage());
            return [];
        }
    }
    

    public static function joinGroup($groupId, $userId) {
        global $pdo;
        
        try {
            // Check if user is already a member
            $stmt = $pdo->prepare("SELECT * FROM group_members WHERE group_id = ? AND user_id = ?");
            $stmt->execute([$groupId, $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return "already_member";
            }
            
            // Check group privacy
            $stmt = $pdo->prepare("SELECT privacy FROM study_groups WHERE id = ?");
            $stmt->execute([$groupId]);
            $group = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$group) {
                return false; // Group doesn't exist
            }
            
            $status = ($group['privacy'] === 'private') ? 'pending' : 'approved';
            $role = 'member';
            
            // Insert membership record
            $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id, role, status) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$groupId, $userId, $role, $status])) {
                return $status;
            } else {
                error_log("Join group failed: " . json_encode($pdo->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            error_log("Join group error: " . $e->getMessage());
            return false;
        }
    }
    
    // Leave a group
    public static function leaveGroup($groupId, $userId) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("DELETE FROM group_members WHERE group_id = ? AND user_id = ?");
            return $stmt->execute([$groupId, $userId]);
        } catch (PDOException $e) {
            error_log("Leave group error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get group members
    public static function getMembers($groupId) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("
                SELECT u.id, u.name, u.email, u.avatar, gm.role, gm.status, gm.joined_at
                FROM group_members gm
                JOIN users u ON gm.user_id = u.id
                WHERE gm.group_id = ?
                ORDER BY 
                    CASE gm.role
                        WHEN 'admin' THEN 1
                        WHEN 'moderator' THEN 2
                        ELSE 3
                    END, 
                    gm.joined_at
            ");
            $stmt->execute([$groupId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get members error: " . $e->getMessage());
            return [];
        }
    }
    
    // Check if user is a member and their role
    public static function getMemberRole($groupId, $userId) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT role, status FROM group_members WHERE group_id = ? AND user_id = ?");
            $stmt->execute([$groupId, $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['status'] === 'approved') {
                return $result['role'];
            }
            return false;
        } catch (PDOException $e) {
            error_log("Get member role error: " . $e->getMessage());
            return false;
        }
    }
    
    // Approve or reject pending members
    public static function updateMemberStatus($groupId, $userId, $status) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("UPDATE group_members SET status = ? WHERE group_id = ? AND user_id = ?");
            return $stmt->execute([$status, $groupId, $userId]);
        } catch (PDOException $e) {
            error_log("Update member status error: " . $e->getMessage());
            return false;
        }
    }
    
    // Update member role
    public static function updateMemberRole($groupId, $userId, $role) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("UPDATE group_members SET role = ? WHERE group_id = ? AND user_id = ?");
            return $stmt->execute([$role, $groupId, $userId]);
        } catch (PDOException $e) {
            error_log("Update member role error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get user's groups
    public static function getUserGroups($userId) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("
                SELECT g.*, gm.role, gm.status, u.name as creator_name,
                       (SELECT COUNT(*) FROM group_members WHERE group_id = g.id) as member_count
                FROM group_members gm
                JOIN study_groups g ON gm.group_id = g.id
                JOIN users u ON g.created_by = u.id
                WHERE gm.user_id = ?
                ORDER BY gm.joined_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user groups error: " . $e->getMessage());
            return [];
        }
    }

    public static function removeMember($groupId, $userId) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("
                DELETE FROM group_members 
                WHERE group_id = ? AND user_id = ?
            ");
            return $stmt->execute([$groupId, $userId]);
        } catch (PDOException $e) {
            error_log("Remove member error: " . $e->getMessage());
            return false;
        }
    }
    
}






?>