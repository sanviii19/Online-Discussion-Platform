<?php require_once "../controllers/authController.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join the Discussion - Sign Up</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-image: url('https://images.unsplash.com/photo-1554147090-e1221a04a025?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            height: 100vh;
        }
        .form-container {
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.85);
        }
    </style>
</head>
<body class="flex justify-center items-center min-h-screen bg-gray-50">
    <div class="w-full max-w-lg p-8 mx-4">
        <div class="form-container rounded-xl shadow-2xl overflow-hidden transition-all duration-300 hover:shadow-[0_20px_50px_rgba(8,_112,_184,_0.7)]">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-white">Join the Discussion</h2>
                    <p class="text-blue-100 mt-1">Create your account to get started</p>
                </div>
                <div class="bg-white rounded-full p-3 shadow-lg">
                    <svg width="40" height="40" viewBox="0 0 82 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-labelledby="logoTitle logoDesc" role="img">
<path d="M80.2329 5.23828C80.2329 5.17252 80.1763 5.12165 80.1106 5.12523C80.05 5.12854 80 5.17748 80 5.23821V5.34621C80 5.58561 79.8059 5.77969 79.5665 5.77969C79.3271 5.77969 79.1331 5.58561 79.1331 5.34621C79.1331 4.97132 78.923 4.63501 78.6603 4.36759C78.1825 3.88126 77.8926 3.19021 78.0361 2.5724C78.2913 1.47429 78.8953 1 79.9998 1C81.1044 1 81.9998 1.89543 81.9998 3C81.9998 3.34845 81.9107 3.67609 81.7541 3.96137C81.376 4.6498 80.8237 4.82405 80.8237 5.60947V6.28487C80.8237 6.44803 80.6915 6.58028 80.5283 6.58028C80.3652 6.58028 80.2329 6.44802 80.2329 6.28487V5.23828Z" fill="#283841"></path>
<path d="M44.1379 8.47245C50.1268 9.49758 56.7104 8.87237 62.5284 10.3048C69.2521 12.3078 73.0441 17.7852 62.0806 19.0682C56.2747 19.519 50.4007 18.5652 44.669 19.5793C38.5986 20.8192 32.3657 22.9076 25.7754 21.8957C19.7385 20.965 14.551 17.2814 14.7102 13.3647C14.6766 9.51719 20.3337 7.31906 25.8121 6.61623C32.1072 5.97034 38.2318 7.31609 44.1379 8.47245ZM44.4888 7.7695C12.7164 0.405219 10.7057 11.7502 10.6709 13.3221C10.4111 18.0115 16.6376 22.9074 24.0335 23.9452C31.7158 25.0233 38.3193 21.9888 45.0969 20.343C51.7091 19.1929 58.0985 20.6789 64.7151 20.1151C75.7367 19.1759 73.2713 11.1096 65.4738 9.27206C58.8566 7.71268 51.3011 8.93479 44.4888 7.7695Z" fill="#283841"></path>
<path d="M43.4994 10.2966C48.0627 10.8847 60.2024 11.679 59.8389 15.1157C59.3749 17.1224 49.9027 16.8816 43.7806 17.537C38.4388 18.0667 25.3007 18.968 25.4668 13.5278C25.4233 8.5358 38.8059 9.78382 43.4994 10.2966ZM43.6879 9.62583C39.0218 8.96282 33.9702 8.25048 29.1853 8.87414C25.5181 9.35216 21.4345 10.8623 21.5215 13.4622C21.363 16.173 25.287 18.299 29.2117 19.0002C34.1074 19.8748 39.2579 18.9536 44.0639 18.2928C50.7603 17.4837 62.8029 18.2837 63.2834 15.4524C63.7486 11.0258 49.3675 10.3968 43.6879 9.62583Z" fill="#283841"></path>
<path d="M43.4552 12.2781C45.7803 12.4048 51.3363 12.9854 50.8021 14.3169C50.1541 15.5982 45.1892 15.5982 43.5241 15.6495C41.4613 15.6655 35.9911 15.7246 35.7875 13.8409C35.8206 11.9623 41.6284 12.2208 43.4552 12.2781ZM43.528 11.5678C40.711 11.3928 32.0883 10.858 32.0579 13.7815C31.8312 16.5419 40.8153 16.3968 43.6659 16.4245C45.7802 16.3968 53.3273 16.334 54.3058 14.6503C55.0008 12.6951 46.3713 11.8242 43.528 11.5678Z" fill="#283841"></path>
<path fill-rule="evenodd" clip-rule="evenodd" d="M40.7609 3.97138C42.4079 4.57062 44.0673 5.17438 45.7804 5.73221C49.2406 7.0955 54.2189 6.5366 59.425 5.95209C64.1786 5.41837 69.1222 4.86332 73.2736 5.73102C77.7162 6.65977 80.1193 10.7879 79.995 14.8028L79.9994 14.797V23.7169C80.026 27.8836 77.3505 32.035 71.4281 32.4793C67.8681 32.7464 64.3959 32.2199 60.9484 31.6971C56.1374 30.9677 51.3747 30.2456 46.4897 31.6975C43.9386 32.6131 41.5658 33.8312 39.1889 35.0515C33.7526 37.8424 28.2931 40.6451 20.623 39.8687C8.41911 38.6332 -0.0860136 31.9113 0.000656343 22.5699C0.00431845 22.3945 0.0160372 22.2202 0.0321505 22.0464V14.4226C0.00749228 14.0517 -0.00251749 13.6768 0.000900483 13.297C0.146164 6.40211 10.1235 0.688269 20.9557 0.0607974C28.7695 -0.391778 34.6801 1.75883 40.7609 3.97138ZM57.8747 6.97562C53.1076 7.26658 48.4631 7.55006 45.227 6.53812C44.1845 6.21332 43.1186 5.86884 42.049 5.5232C39.4225 4.67432 36.7741 3.8184 34.3964 3.22938C19.1691 -0.535394 4.73088 5.10273 4.00871 13.2886C3.46062 18.7192 11.2626 26.7029 21.6867 27.9019C26.412 28.3167 30.4645 27.5324 34.581 26.1343C36.2426 25.5171 37.8427 24.8418 39.4421 24.1667C41.5551 23.2749 43.6672 22.3834 45.9198 21.6265C50.7487 20.4676 55.4384 20.9999 59.976 21.5149C63.0461 21.8634 66.0463 22.2039 68.9726 22.0074C71.7887 21.6863 74.0744 20.9872 75.4887 19.3734C78.3591 15.5231 78.0719 9.03998 70.393 7.16712C66.7929 6.43134 62.2814 6.70667 57.8747 6.97562Z" fill="#283841"></path>
</svg>
                </div>
            </div>
            
            <form method="POST" class="p-6 space-y-4 bg-white">
                <?php if(isset($errors)): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">
                                    <?php echo $errors; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="text" name="name" id="name" placeholder="John Doe" required 
                               class="pl-10 w-full py-2 px-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150">
                    </div>
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" name="email" id="email" placeholder="your@email.com" required 
                               class="pl-10 w-full py-2 px-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150">
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password" id="password" placeholder="At least 8 characters" required 
                               class="pl-10 w-full py-2 px-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Must contain at least 8 characters</p>
                </div>
                
                <div class="pt-2">
                    <button type="submit" name="register" 
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-150 transform hover:-translate-y-0.5">
                        <i class="fas fa-user-plus mr-2"></i> Create Account
                    </button>
                </div>
            </form>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center">
                <p class="text-sm text-gray-600 mb-2 sm:mb-0">Already have an account?</p>
                <a href="login.php" class="text-indigo-600 hover:text-indigo-800 font-medium transition duration-150">
                    Sign in <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
        
        <p class="text-center mt-8 text-white text-sm">
            © 2025 Discussion Platform. All rights reserved.
        </p>
    </div>
</body>
</html>