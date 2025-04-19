<?php require_once "../controllers/authController.php"; 

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join the Discussion - Sign Up</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            --secondary-gradient: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            --accent-gradient: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
        }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(16px);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .background-gradient {
            background: linear-gradient(120deg, #6366f1, #a855f7, #3b82f6, #8b5cf6);
            background-size: 300% 300%;
            animation: gradient-shift 15s ease infinite;
        }
        
        @keyframes gradient-shift {
            0% { background-position: 0% 50% }
            50% { background-position: 100% 50% }
            100% { background-position: 0% 50% }
        }
        
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        
        .floating-reverse {
            animation: float 7s ease-in-out infinite reverse;
        }
        
        .floating-delay {
            animation: float 8s ease-in-out 2s infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        
        .blob {
            position: absolute;
            filter: blur(40px);
            opacity: 0.4;
            z-index: 0;
        }
        
        .rotating {
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .pulse {
            animation: pulse 3s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .gradient-text {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .feature-icon {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .feature-icon:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }
        
        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            pointer-events: none;
        }
        
        @keyframes particle-animation {
            0% {
                opacity: 0;
                transform: translateY(0) scale(0);
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                transform: translateY(-100px) scale(1);
            }
        }
        
        .morph-animation {
            animation: morph 8s ease-in-out infinite;
            border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
            transition: all 1s ease-in-out;
        }
        
        @keyframes morph {
            0% { border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; }
            50% { border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%; }
            100% { border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; }
        }
        
        .ring {
            position: absolute;
            opacity: 0;
            transform: scale(0.1);
            border-radius: 50%;
            border: 5px solid rgba(255, 255, 255, 0.2);
        }
        
        @keyframes ripple {
            0% {
                opacity: 0.5;
                transform: scale(0.1);
            }
            100% {
                opacity: 0;
                transform: scale(1);
            }
        }
    </style>
</head>
<body>
    <!-- Background blobs -->
    <div class="blob top-0 left-0 w-96 h-96 bg-indigo-300 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
    <div class="blob bottom-0 right-0 w-96 h-96 bg-purple-300 rounded-full translate-x-1/2 translate-y-1/2"></div>
    
    <div class="container mx-auto px-4 py-10 min-h-screen flex items-center justify-center">
        <div class="w-full max-w-6xl flex flex-col lg:flex-row rounded-3xl overflow-hidden shadow-2xl relative z-10 bg-white">
            
            <!-- Left column - Interactive Elements -->
            <div class="lg:w-1/2 background-gradient p-8 lg:p-12 relative overflow-hidden flex flex-col justify-between">
                <!-- Animated Shapes -->
                <div class="absolute inset-0 overflow-hidden">
                    <div class="absolute w-72 h-72 top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 morph-animation bg-white/10"></div>
                </div>
                
                <!-- Decorative SVG elements -->
                <div class="absolute top-20 left-20 w-40 h-40 rotating opacity-20">
                    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#FFFFFF" d="M47.5,-51.2C59.9,-37.7,67.3,-18.9,67.1,-0.2C66.9,18.5,59.1,36.9,46.7,50.8C34.2,64.6,17.1,73.8,-1.1,74.9C-19.3,76,-38.5,69,-52.1,55.2C-65.7,41.5,-73.5,20.8,-72.3,1.2C-71.2,-18.3,-61.1,-36.6,-47.3,-50C-33.4,-63.5,-16.7,-72.1,0.9,-73.2C18.5,-74.2,37.1,-67.8,47.5,-51.2Z" transform="translate(100 100)" />
                    </svg>
                </div>
                
                <!-- Animated particles -->
                <div id="particles" class="absolute inset-0 overflow-hidden"></div>
                
                <!-- Main content -->
                <div class="relative z-10 mb-12">
                    <a href="../index.php" class="inline-flex items-center text-white mb-8 hover:opacity-80 transition-opacity">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Home
                    </a>
                    
                    <h1 class="text-4xl font-bold text-white mb-4">Join our community of learners</h1>
                    <p class="text-indigo-100 text-lg mb-6">Connect with peers, share knowledge, and accelerate your learning journey.</p>
                    
                    <!-- 3D-like Feature Icons -->
                    
                    
                    <!-- Animated illustration -->
                    <div class="mt-12 relative flex justify-center items-center">
                        <div class="w-36 h-36 bg-white/10 rounded-full flex items-center justify-center relative overflow-hidden">
                            <div class="absolute inset-0">
                                <!-- Ripple effect circles -->
                                <div class="ring absolute inset-2" id="ring1"></div>
                                <div class="ring absolute inset-2" id="ring2"></div>
                                <div class="ring absolute inset-2" id="ring3"></div>
                            </div>
                            <svg class="w-16 h-16 text-white relative z-10" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <!-- Stats section -->
                <div class="grid grid-cols-3 gap-4 relative z-10">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white mb-1">2,000+</div>
                        <div class="text-indigo-100 text-sm">Active Users</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white mb-1">500+</div>
                        <div class="text-indigo-100 text-sm">Study Groups</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white mb-1">50+</div>
                        <div class="text-indigo-100 text-sm">Universities</div>
                    </div>
                </div>
            </div>
            
            <!-- Right column - Registration Form -->
            <div class="lg:w-1/2 p-8 lg:p-12 flex items-center">
                <div class="w-full max-w-md mx-auto">
                    <div class="mb-8">
                        <h2 class="text-3xl font-bold text-gray-800 mb-2">Create your account</h2>
                        <p class="text-gray-600">Start collaborating with peers in just a few steps</p>
                    </div>
                    
                    <form method="POST" class="space-y-4">
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
                                      class="pl-10 w-full py-3 px-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150">
                            </div>
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input type="email" name="email" id="email" placeholder="your@email.com" required 
                                      class="pl-10 w-full py-3 px-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150">
                            </div>
                        </div>
                        
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input type="password" name="password" id="password" placeholder="At least 8 characters" required 
                                      class="pl-10 w-full py-3 px-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Must contain at least 8 characters</p>
                        </div>
                        
                        <div class="pt-2">
                            <button type="submit" name="register" 
                                   class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-base font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 transform hover:-translate-y-1">
                                <i class="fas fa-user-plus mr-2"></i> Create Account
                            </button>
                        </div>
                        
                    
                    </form>
                    
                    <div class="text-center mt-8">
                        <p class="text-gray-600">Already have an account? 
                            <a href="login.php" class="font-medium text-indigo-600 hover:text-indigo-800 transition duration-150">
                                Sign in <i class="fas fa-arrow-right ml-1 text-xs"></i>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Particle animation
        document.addEventListener('DOMContentLoaded', function() {
            const particlesContainer = document.getElementById('particles');
            
            // Create particles
            for (let i = 0; i < 20; i++) {
                createParticle();
            }
            
            function createParticle() {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                // Random position, size and animation duration
                const size = Math.random() * 6 + 3; // 3-9px
                const left = Math.random() * 100; // 0-100%
                const animDuration = Math.random() * 10 + 10; // 10-20s
                const delay = Math.random() * 10; // 0-10s
                
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.bottom = '-5px';
                particle.style.left = `${left}%`;
                particle.style.animation = `particle-animation ${animDuration}s linear ${delay}s infinite`;
                
                particlesContainer.appendChild(particle);
            }
            
            // Ripple effect animation
            const rings = [
                document.getElementById('ring1'),
                document.getElementById('ring2'),
                document.getElementById('ring3')
            ];
            
            function animateRings() {
                rings.forEach((ring, index) => {
                    setTimeout(() => {
                        ring.style.animation = 'none';
                        ring.offsetHeight; // Force reflow
                        ring.style.animation = `ripple 3s ease-out ${index * 0.8}s`;
                    }, index * 800);
                });
                
                setTimeout(animateRings, 4000);
            }
            
            animateRings();
        });
    </script>
</body>
</html>