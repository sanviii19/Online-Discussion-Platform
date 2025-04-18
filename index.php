<?php
require_once "./controllers/authController.php";
require_once "./models/Group.php";

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

if ($isLoggedIn) {
    $userId = $_SESSION['user_id'];
    
    // Get user's groups
    $userGroups = Group::getUserGroups($userId);
    
    // If user has joined at least one group, redirect to the most recent one
    if (!empty($userGroups)) {
        $mostRecentGroup = $userGroups[0]; // First group in the list (most recent)
        header("Location: pages/group.php?id=" . $mostRecentGroup['id']);
        exit();
    } else {
        // User is logged in but hasn't joined any groups
        header("Location: pages/groups.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konvo - Study Group Collaboration</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- AOS Animation Library -->
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            --secondary-gradient: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            --accent-gradient: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
        }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8fafc;
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
        
        .gradient-text {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        
        .blob {
            position: absolute;
            z-index: -1;
            filter: blur(60px);
            opacity: 0.6;
        }
        
        .blob-1 {
            top: 5%;
            right: 5%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.4) 0%, rgba(168, 85, 247, 0.2) 70%);
            border-radius: 50% 30% 70% 40%;
            animation: blob-move 25s infinite alternate;
        }
        
        .blob-2 {
            bottom: 20%;
            left: 10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.3) 0%, rgba(139, 92, 246, 0.2) 70%);
            border-radius: 40% 60% 30% 70%;
            animation: blob-move 20s infinite alternate-reverse;
        }
        
        @keyframes blob-move {
            0% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(50px, 20px) scale(1.05); }
            50% { transform: translate(0, 40px) scale(0.95); }
            75% { transform: translate(-50px, 20px) scale(1.05); }
            100% { transform: translate(0, 0) scale(1); }
        }
        
        .cta-button {
            background: var(--primary-gradient);
            transition: all 0.3s;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }
        
        .cta-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.4);
        }
        
        .cta-button::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: -100%;
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%);
            transition: all 0.5s;
            z-index: -1;
        }
        
        .cta-button:hover::after {
            left: 100%;
        }
        
        .feature-card {
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
        }
        
        .nav-link {
            position: relative;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -4px;
            left: 0;
            background: var(--primary-gradient);
            transition: width 0.3s;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
    </style>
</head>
<body>
    <!-- Background Blobs -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <!-- Navigation -->
    <nav class="glass-card max-w-6xl mx-auto mt-6 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center">
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
            <span class="text-xl font-bold gradient-text">Konvo</span>
        </div>
        <div class="hidden md:flex items-center space-x-8">
            <a href="#features" class="nav-link text-gray-700 font-medium">Features</a>
            <a href="#how-it-works" class="nav-link text-gray-700 font-medium">How It Works</a>
            <a href="#testimonials" class="nav-link text-gray-700 font-medium">Testimonials</a>
            <div class="flex space-x-3">
                <a href="pages/login.php" class="px-4 py-2 rounded-lg border border-indigo-200 text-indigo-600 font-medium transition hover:bg-indigo-50">Login</a>
                <a href="pages/register.php" class="cta-button px-4 py-2 rounded-lg text-white font-medium">Sign Up</a>
            </div>
        </div>
        <button class="md:hidden text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
        </button>
    </nav>

    <!-- Hero Section -->
    <section class="relative max-w-6xl mx-auto pt-16 pb-24 px-6">
        <div class="flex flex-col md:flex-row items-center gap-16">
            <div class="md:flex-1" data-aos="fade-right" data-aos-duration="1000">
                <h1 class="text-5xl md:text-6xl font-bold text-gray-800 mb-6">
                    Learn Together <span class="gradient-text">Grow Together</span>
                </h1>
                <p class="text-xl text-gray-600 mb-8">Join study groups, collaborate in real-time, and elevate your academic experience with our modern Konvo.</p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="pages/register.php" class="cta-button py-4 px-8 rounded-xl text-white font-semibold text-lg text-center">
                        <i class="fas fa-user-plus mr-2"></i> Get Started Free
                    </a>
                    <a href="#how-it-works" class="glass-card py-4 px-8 rounded-xl font-semibold text-lg text-center text-gray-700 hover:shadow-lg transition-all">
                        <i class="fas fa-info-circle mr-2"></i> How It Works
                    </a>
                </div>
                <div class="mt-10 flex items-center">
                    <div class="flex -space-x-2">
                        <img src="https://randomuser.me/api/portraits/women/44.jpg" class="w-10 h-10 rounded-full border-2 border-white" alt="User">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" class="w-10 h-10 rounded-full border-2 border-white" alt="User">
                        <img src="https://randomuser.me/api/portraits/women/65.jpg" class="w-10 h-10 rounded-full border-2 border-white" alt="User">
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">Join 2,000+ students</div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            <span class="ml-1 text-gray-500">4.9 Rating</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="md:flex-1 relative" data-aos="fade-left" data-aos-duration="1000">
                <div class="relative z-10 floating">
                    <img src="https://img.freepik.com/free-vector/teamwork-puzzle-concept-illustration_114360-30041.jpg?t=st=1744872503~exp=1744876103~hmac=05920ac59276cfff77923371fb4c41a33971d47bc2273f9657a46d4bbd7a68dc&w=1380" alt="Collaboration" class="w-full h-auto">
                </div>
                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-indigo-200 rounded-full filter blur-3xl opacity-30 animate-pulse"></div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 bg-gradient-to-b from-white to-indigo-50">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl font-bold mb-4">Powerful <span class="gradient-text">Features</span></h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">Everything you need to collaborate effectively and enhance your learning experience.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="feature-card glass-card p-8 rounded-2xl" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-16 h-16 mb-6 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1-3-3.87m13-3.13V7a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v6a4 4 0 0 0 4 4h6a4 4 0 0 0 4-4z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Group Collaboration</h3>
                    <p class="text-gray-600">Create or join study groups and collaborate in real-time discussions.</p>
                </div>
                
                <div class="feature-card glass-card p-8 rounded-2xl" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-16 h-16 mb-6 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-400 flex items-center justify-center text-white">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9M12 4v16m0 0H3"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Resource Sharing</h3>
                    <p class="text-gray-600">Share notes, links, and resources with your group members easily.</p>
                </div>
                
                <div class="feature-card glass-card p-8 rounded-2xl" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-16 h-16 mb-6 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-400 flex items-center justify-center text-white">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Real-Time Chat</h3>
                    <p class="text-gray-600">Engage in instant messaging and stay connected with your peers.</p>
                </div>
                
                <div class="feature-card glass-card p-8 rounded-2xl" data-aos="fade-up" data-aos-delay="400">
                    <div class="w-16 h-16 mb-6 rounded-2xl bg-gradient-to-br from-pink-500 to-rose-400 flex items-center justify-center text-white">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Progress Tracking</h3>
                    <p class="text-gray-600">Monitor your learning progress and group activity at a glance.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How it Works Section -->
    <section id="how-it-works" class="py-24 bg-white">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl font-bold mb-4">How It <span class="gradient-text">Works</span></h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">Get started in just a few simple steps</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8 mt-16">
                <div class="relative" data-aos="fade-up" data-aos-delay="100">
                    <!-- Step connector -->
                    <div class="hidden md:block absolute top-16 right-0 w-full h-1 bg-gradient-to-r from-indigo-500 to-transparent"></div>
                    <!-- Step -->
                    <div class="relative z-10 mb-8 flex flex-col items-center">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold">1</div>
                    </div>
                    <div class="glass-card p-8 rounded-2xl">
                        <img src="https://images.unsplash.com/photo-1513258496099-48168024aec0?auto=format&fit=crop&w=400&q=80" alt="Sign Up" class="rounded-lg mb-6 w-full h-48 object-cover">
                        <h3 class="text-2xl font-bold mb-3">Create Account</h3>
                        <p class="text-gray-600">Set up your free account in seconds and customize your profile to connect with like-minded students.</p>
                    </div>
                </div>
                
                <div class="relative" data-aos="fade-up" data-aos-delay="200">
                    <!-- Step connectors -->
                    <div class="hidden md:block absolute top-16 left-0 w-full h-1 bg-gradient-to-r from-transparent to-blue-500"></div>
                    <div class="hidden md:block absolute top-16 right-0 w-full h-1 bg-gradient-to-r from-blue-500 to-transparent"></div>
                    <!-- Step -->
                    <div class="relative z-10 mb-8 flex flex-col items-center">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-r from-blue-500 to-cyan-400 flex items-center justify-center text-white text-2xl font-bold">2</div>
                    </div>
                    <div class="glass-card p-8 rounded-2xl">
                        <img src="https://images.unsplash.com/photo-1461749280684-dccba630e2f6?auto=format&fit=crop&w=400&q=80" alt="Join Groups" class="rounded-lg mb-6 w-full h-48 object-cover">
                        <h3 class="text-2xl font-bold mb-3">Join Groups</h3>
                        <p class="text-gray-600">Browse and join study groups based on your courses, interests, or create your own community.</p>
                    </div>
                </div>
                
                <div class="relative" data-aos="fade-up" data-aos-delay="300">
                    <!-- Step connector -->
                    <div class="hidden md:block absolute top-16 left-0 w-full h-1 bg-gradient-to-r from-green-500 to-transparent"></div>
                    <!-- Step -->
                    <div class="relative z-10 mb-8 flex flex-col items-center">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-r from-green-500 to-emerald-400 flex items-center justify-center text-white text-2xl font-bold">3</div>
                    </div>
                    <div class="glass-card p-8 rounded-2xl">
                        <img src="https://images.unsplash.com/photo-1503676382389-4809596d5290?auto=format&fit=crop&w=400&q=80" alt="Collaborate" class="rounded-lg mb-6 w-full h-48 object-cover">
                        <h3 class="text-2xl font-bold mb-3">Collaborate</h3>
                        <p class="text-gray-600">Start discussions, share resources, and learn together with peers in real-time conversations.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="py-24 bg-gradient-to-b from-indigo-50 to-white">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl font-bold mb-4">What Students <span class="gradient-text">Say</span></h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">Don't just take our word for it - hear from our users</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="glass-card p-8 rounded-2xl" data-aos="fade-up" data-aos-delay="100">
                    <div class="mb-6 text-indigo-400">
                        <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                        </svg>
                    </div>
                    <p class="text-gray-600 mb-6 text-lg">"This platform transformed how I study with my classmates. The real-time discussions and resource sharing made our group projects so much easier to manage!"</p>
                    <div class="flex items-center">
                        <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="User 1" class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <h4 class="font-bold">Sarah K.</h4>
                            <div class="text-indigo-500">Computer Science Student</div>
                        </div>
                    </div>
                </div>
                
                <div class="glass-card p-8 rounded-2xl" data-aos="fade-up" data-aos-delay="200">
                    <div class="mb-6 text-indigo-400">
                        <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                        </svg>
                    </div>
                    <p class="text-gray-600 mb-6 text-lg">"I found amazing study partners and improved my understanding of complex topics. The collaborative environment helped me boost my grades significantly!"</p>
                    <div class="flex items-center">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User 2" class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <h4 class="font-bold">Alex P.</h4>
                            <div class="text-indigo-500">Engineering Student</div>
                        </div>
                    </div>
                </div>
                
                <div class="glass-card p-8 rounded-2xl" data-aos="fade-up" data-aos-delay="300">
                    <div class="mb-6 text-indigo-400">
                        <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                        </svg>
                    </div>
                    <p class="text-gray-600 mb-6 text-lg">"As an online student, this platform helped me connect with peers despite the distance. The resource sharing feature has been a game-changer for my studies."</p>
                    <div class="flex items-center">
                        <img src="https://randomuser.me/api/portraits/women/65.jpg" alt="User 3" class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <h4 class="font-bold">Priya S.</h4>
                            <div class="text-indigo-500">Business Student</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="py-20 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-purple-600"></div>
        <div class="absolute inset-0 opacity-20">
            <svg width="100%" height="100%" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                        <circle cx="1" cy="1" r="1" fill="white"></circle>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid)"></rect>
            </svg>
        </div>
        
        <div class="relative max-w-4xl mx-auto text-center px-6" data-aos="zoom-in">
        <div>
            <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">Ready to Elevate Your Learning?</h2>
            <p class="text-xl text-indigo-100 mb-10">Join thousands of students who are already collaborating, sharing, and learning together.</p>
            <a href="pages/register.php" class="bg-white py-4 px-10 rounded-xl text-indigo-600 font-semibold text-lg inline-block hover:bg-gray-100 transition-all shadow-xl transform hover:-translate-y-1">
                <i class="fas fa-rocket mr-2"></i> Start Your Journey
            </a>
            </div>
            <div class="mt-10 bg-white/20 backdrop-blur-sm rounded-xl p-6 inline-block">
                <div class="text-white font-semibold mb-1">Join 2,000+ students from 50+ universities</div>
                <div class="flex justify-center space-x-4">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQdA_d-eRFCRR-hyw70w83WfUSgbHrkBdblSA&s" alt="University" class="h-10 opacity-70">
                    <img src="https://upload.wikimedia.org/wikipedia/en/thumb/1/1d/Indian_Institute_of_Technology_Bombay_Logo.svg/1200px-Indian_Institute_of_Technology_Bombay_Logo.svg.png" alt="University" class="h-10 opacity-70">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRks8whHa4TIvKbDfUTh2lj7UX787HDFm4lLA&s" alt="University" class="h-10 opacity-70">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQU4khsgMC7_a5xVAJryADbD_5cQ-gJbMKIoQ&s" alt="University" class="h-10 opacity-70">
                </div>
            </div>
            
        </div>

    </section>

    <footer class="bg-gray-900 text-white pt-16 pb-8">
        <div class="max-w-6xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                <div>
                    <div class="flex items-center mb-6">
                        <svg class="w-10 h-10 mr-3" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 5C11.729 5 5 11.729 5 20C5 28.271 11.729 35 20 35C28.271 35 35 28.271 35 20C35 11.729 28.271 5 20 5Z" fill="url(#paint1_linear)"/>
                            <path d="M15 16C16.1046 16 17 15.1046 17 14C17 12.8954 16.1046 12 15 12C13.8954 12 13 12.8954 13 14C13 15.1046 13.8954 16 15 16Z" fill="white"/>
                            <path d="M25 16C26.1046 16 27 15.1046 27 14C27 12.8954 26.1046 12 25 12C23.8954 12 23 12.8954 23 14C23 15.1046 23.8954 16 25 16Z" fill="white"/>
                            <path d="M15 28H25M15 22C15 24.2091 17.2386 26 20 26C22.7614 26 25 24.2091 25 22" stroke="white" stroke-width="2" stroke-linecap="round"/>
                            <defs>
                                <linearGradient id="paint1_linear" x1="5" y1="5" x2="35" y2="35" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#6366F1"/>
                                    <stop offset="1" stop-color="#A855F7"/>
                                </linearGradient>
                            </defs>
                        </svg>
                        <span class="text-xl font-bold gradient-text">Konvo</span>
                    </div>
                    <p class="text-gray-400 mb-4">The modern platform for student collaboration and group study.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="fab fa-linkedin"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="#features" class="text-gray-400 hover:text-white transition">Features</a></li>
                        <li><a href="#how-it-works" class="text-gray-400 hover:text-white transition">How It Works</a></li>
                        <li><a href="#testimonials" class="text-gray-400 hover:text-white transition">Testimonials</a></li>
                        <li><a href="pages/login.php" class="text-gray-400 hover:text-white transition">Login</a></li>
                        <li><a href="pages/register.php" class="text-gray-400 hover:text-white transition">Sign Up</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Resources</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Help Center</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Blog</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Tutorials</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">FAQs</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Community</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Legal</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Terms of Service</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Cookie Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Data Protection</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="pt-8 border-t border-gray-800 text-center text-gray-400">
                &copy; <?php echo date('Y'); ?> Konvo. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- Animation Libraries -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            once: true,
            duration: 800
        });

        // Simple ScrollTo for smooth navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>