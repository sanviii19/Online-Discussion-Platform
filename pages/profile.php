<?php 
require_once "../controllers/userController.php"; 
require_once "../controllers/authController.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user data
$user = User::getUser($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Profile - Konvo</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-image: url('https://images.unsplash.com/photo-1554147090-e1221a04a025?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
        }
        .form-container {
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.85);
        }
        .avatar-preview {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .avatar-upload {
            position: relative;
        }
        .avatar-upload input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            cursor: pointer;
        }
        .avatar-edit {
            position: absolute;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50 py-10">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="form-container rounded-xl shadow-2xl overflow-hidden transition-all duration-300 hover:shadow-[0_20px_50px_rgba(8,_112,_184,_0.7)]">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-white">Your Profile</h2>
                    <p class="text-blue-100 mt-1">Manage your account settings</p>
                </div>
                <!-- <div class="bg-white rounded-full p-3 shadow-lg"> -->
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
            <!-- </div> -->
            
            <?php if(isset($_GET['success'])): ?>
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mx-6 mt-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">
                                Profile updated successfully!
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
                                $errorMessage = "There was an error updating your profile.";
                                switch ($_GET['error']) {
                                    case 'directory':
                                        $errorMessage = "Couldn't create the upload directory. Please contact the administrator.";
                                        break;
                                    case 'filetype':
                                        $errorMessage = "Invalid file type. Only JPG, PNG, and GIF images are allowed.";
                                        break;
                                    case 'upload':
                                        $errorMessage = "File upload failed. The file may be too large or there was a server error.";
                                        break;
                                    case 'move':
                                        $errorMessage = "Couldn't save the uploaded file. Server permissions issue.";
                                        break;
                                    case 'database':
                                        $errorMessage = "Database error while updating your profile.";
                                        break;
                                }
                                echo $errorMessage;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="p-6 bg-white">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Profile Avatar Column -->
                    <div class="col-span-1 flex flex-col items-center">
                        <div class="avatar-upload relative mb-4">
                        <img src="<?= !empty($user['avatar']) ? '..' . $user['avatar'] : '../public/uploads/avatars/default.png' ?>" 
                        class="avatar-preview" id="avatarPreview" alt="Profile avatar">
                            <div class="avatar-edit">
                                <i class="fas fa-camera"></i>
                                <input type="file" name="avatar" id="avatarUpload" accept="image/*" onchange="previewAvatar(this)">
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 mb-4 text-center">Click the camera icon to change your avatar</p>
                        
                        <div class="bg-blue-50 rounded-lg p-4 w-full border border-blue-100 mt-4">
                            <h3 class="text-lg font-medium text-blue-800 mb-2 flex items-center">
                                <i class="fas fa-info-circle mr-2"></i> Account Info
                            </h3>
                            <div class="space-y-2">
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-user-tag w-5 text-blue-500"></i>
                                    <span class="text-gray-600 ml-2">Role:</span>
                                    <span class="ml-2 font-medium"><?= ucfirst($user['role']) ?></span>
                                </div>
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-calendar w-5 text-blue-500"></i>
                                    <span class="text-gray-600 ml-2">Member since:</span>
                                    <span class="ml-2 font-medium"><?= date('M d, Y', strtotime($user['created_at'])) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Profile Details Column -->
                    <div class="col-span-1 md:col-span-2 space-y-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-user-edit mr-2 text-indigo-600"></i> Profile Details
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-user text-gray-400"></i>
                                        </div>
                                        <input type="text" name="name" id="name" value="<?= $user['name'] ?>" required 
                                               class="pl-10 w-full py-2 px-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150">
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-envelope text-gray-400"></i>
                                        </div>
                                        <input type="email" name="email" id="email" value="<?= $user['email'] ?>" required 
                                               class="pl-10 w-full py-2 px-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-shield-alt mr-2 text-indigo-600"></i> Account Security
                            </h3>
                            
                            <p class="text-gray-600 mb-4">
                                For security reasons, we recommend updating your password regularly.
                            </p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <a href="change-password.php" class="inline-flex items-center justify-center px-4 py-2 border border-indigo-300 text-sm font-medium rounded-md text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition duration-150">
                                    <i class="fas fa-key mr-2"></i> Change Password
                                </a>
                                
                                <a href="account-activity.php" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition duration-150">
                                    <i class="fas fa-history mr-2"></i> View Account Activity
                                </a>
                            </div>
                        </div>
                        
                        <div class="pt-4">
                            <button type="submit" name="updateProfile" 
                                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-150 transform hover:-translate-y-0.5">
                                <i class="fas fa-save mr-2"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center">
                <a href="discussions.php" class="text-indigo-600 hover:text-indigo-800 font-medium transition duration-150">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Home
                </a>
                <a href="logout.php" class="text-red-600 hover:text-red-800 font-medium transition duration-150 mt-2 sm:mt-0">
    <i class="fas fa-sign-out-alt mr-1"></i> Logout
</a>
            </div>
        </div>
        
        <p class="text-center mt-8 text-white text-sm">
            © 2025 Konvo. All rights reserved.
        </p>
    </div>

    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>