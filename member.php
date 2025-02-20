<?php
session_start();
require 'config.php'; // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];

// Fetch current user's data for navbar
$user_query = $conn->prepare("SELECT first_name, last_name, profile_picture FROM users WHERE user_id = ?");
$user_query->bind_param("i", $current_user_id);
$user_query->execute();
$current_user_result = $user_query->get_result();
$current_user = $current_user_result->fetch_assoc();

// Get the user ID from the URL
if (!isset($_GET['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$viewed_user_id = (int)$_GET['user_id'];

// Fetch viewed user's details (excluding email and password)
$user_query = $conn->prepare("SELECT first_name, last_name, profile_picture, bio, location, role, created_at FROM users WHERE user_id = ?");
$user_query->bind_param("i", $viewed_user_id);
$user_query->execute();
$user_result = $user_query->get_result();

if ($user_result->num_rows === 0) {
    header('Location: dashboard.php');
    exit();
}

$viewed_user = $user_result->fetch_assoc();

// Fetch user's trips (without joined_at)
$trips_query = $conn->prepare("
    SELECT t.trip_id, t.title, t.destination 
    FROM trip_members tm 
    JOIN trips t ON tm.trip_id = t.trip_id 
    WHERE tm.user_id = ?
");
$trips_query->bind_param("i", $viewed_user_id);
$trips_query->execute();
$trips_result = $trips_query->get_result();

// Fetch user's communities (with joined_at)
$communities_query = $conn->prepare("
    SELECT c.community_id, c.name, cm.role, cm.joined_at 
    FROM community_members cm 
    JOIN communities c ON cm.community_id = c.community_id 
    WHERE cm.user_id = ?
");
$communities_query->bind_param("i", $viewed_user_id);
$communities_query->execute();
$communities_result = $communities_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?= htmlspecialchars($viewed_user['first_name'] . " " . $viewed_user['last_name']) ?> - Syngo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #22C55E; border-radius: 3px; }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#22C55E',
                        secondary: '#E5E7EB'
                    },
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                    borderRadius: {
                        'none': '0px',
                        'sm': '4px',
                        DEFAULT: '8px',
                        'md': '12px',
                        'lg': '16px',
                        'xl': '20px',
                        '2xl': '24px',
                        '3xl': '32px',
                        'full': '9999px',
                        'button': '8px'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans min-h-screen">
    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 h-16 bg-white shadow-sm z-50">
        <div class="max-w-7xl mx-auto px-4 h-full flex items-center justify-between">
            <div class="flex items-center gap-4 sm:gap-8">
                <a href="index.php" class="text-xl sm:text-2xl font-['Pacifico'] text-primary">Syngo</a>
                <div class="relative hidden sm:block">
                    <input type="text" id="search-bar" placeholder="Search destinations or trips..." class="w-40 sm:w-64 md:w-[400px] h-10 pl-10 pr-4 text-sm rounded-full border border-gray-200 focus:outline-none focus:border-primary">
                    <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            <div class="flex items-center gap-2 sm:gap-4">
                <a href="create_trip.php" class="!rounded-button px-3 sm:px-4 h-9 sm:h-10 bg-primary text-white text-sm flex items-center gap-2 whitespace-nowrap">Create Trip</a>
                <div class="w-8 sm:w-10 h-8 sm:h-10 flex items-center justify-center relative cursor-pointer">
                    <i class="ri-notification-3-line text-gray-600 text-lg sm:text-xl"></i>
                    <span class="absolute top-0 sm:top-1 right-0 sm:right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </div>
                <div class="flex items-center gap-2 sm:gap-3 cursor-pointer">
                    <img src="<?= $current_user['profile_picture'] ?: 'https://public.readdy.ai/ai/img_res/cb6fd3e7b68878d46ddacbc3d3415e6d.jpg' ?>" class="w-8 sm:w-10 h-8 sm:h-10 rounded-full object-cover">
                    <span class="text-xs sm:text-sm font-medium hidden sm:block"><?= htmlspecialchars($current_user['first_name'] . " " . $current_user['last_name']) ?></span>
                    <a href="logout.php" class="text-xs sm:text-sm text-red-600 ml-2">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Profile Container -->
    <div class="max-w-4xl mx-auto px-4 pt-20 pb-8">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row items-center gap-6">
                <img src="<?= $viewed_user['profile_picture'] ?: 'https://public.readdy.ai/ai/img_res/cb6fd3e7b68878d46ddacbc3d3415e6d.jpg' ?>" class="w-24 h-24 sm:w-32 sm:h-32 rounded-full object-cover">
                <div class="text-center sm:text-left">
                    <h2 class="text-xl sm:text-2xl font-semibold text-gray-800"><?= htmlspecialchars($viewed_user['first_name'] . " " . $viewed_user['last_name']) ?></h2>
                    <p class="text-xs sm:text-sm text-gray-500"><?= htmlspecialchars($viewed_user['role']) ?></p>
                    <?php if ($viewed_user['location']) : ?>
                        <p class="text-xs sm:text-sm text-gray-600"><i class="ri-map-pin-line mr-1"></i><?= htmlspecialchars($viewed_user['location']) ?></p>
                    <?php endif; ?>
                    <p class="text-xs sm:text-sm text-gray-500 mt-2">Joined: <?= date('M d, Y', strtotime($viewed_user['created_at'])) ?></p>
                </div>
            </div>

            <?php if ($viewed_user['bio']) : ?>
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Bio</h3>
                    <p class="text-xs sm:text-sm text-gray-600"><?= htmlspecialchars($viewed_user['bio']) ?></p>
                </div>
            <?php endif; ?>

            <!-- Trips Section -->
            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Trips</h3>
                <?php if ($trips_result->num_rows > 0) : ?>
                    <ul class="space-y-3 max-h-64 overflow-y-auto custom-scrollbar">
                        <?php while ($trip = $trips_result->fetch_assoc()) : ?>
                            <li class="p-2 bg-gray-50 rounded-md">
                                <a href="trip_details.php?trip_id=<?= $trip['trip_id'] ?>" class="text-xs sm:text-sm text-primary hover:underline"><?= htmlspecialchars($trip['title']) ?></a>
                                <p class="text-xs text-gray-600"><?= htmlspecialchars($trip['destination']) ?></p>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else : ?>
                    <p class="text-xs sm:text-sm text-gray-600">No trips joined yet.</p>
                <?php endif; ?>
            </div>

            <!-- Communities Section -->
            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Communities</h3>
                <?php if ($communities_result->num_rows > 0) : ?>
                    <ul class="space-y-3 max-h-64 overflow-y-auto custom-scrollbar">
                        <?php while ($community = $communities_result->fetch_assoc()) : ?>
                            <li class="p-2 bg-gray-50 rounded-md">
                                <a href="community_chat.php?community_id=<?= $community['community_id'] ?>" class="text-xs sm:text-sm text-primary hover:underline"><?= htmlspecialchars($community['name']) ?></a>
                                <p class="text-xs text-gray-600">Role: <?= htmlspecialchars($community['role']) ?></p>
                                <p class="text-xs text-gray-500">Joined: <?= date('M d, Y', strtotime($community['joined_at'])) ?></p>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else : ?>
                    <p class="text-xs sm:text-sm text-gray-600">No communities joined yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>