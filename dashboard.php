<?php
session_start();
require 'config.php'; // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$user_query = $conn->prepare("SELECT first_name, last_name, email, profile_picture FROM users WHERE user_id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();


// Fetch trips for all users
$trips_query = "SELECT * FROM trips ORDER BY created_at DESC";
$trips_stmt = $conn->prepare($trips_query);
$trips_stmt->execute();
$trips_result = $trips_stmt->get_result();

$trips_query = "SELECT trip_id, title, destination, start_date, end_date, group_size, description, trip_image FROM trips";
$trips_stmt = $conn->prepare($trips_query);
$trips_stmt->execute();
$trips_result = $trips_stmt->get_result();


// Fetch communities
$communities_query = "SELECT * FROM communities";
$communities_query_stmt = $conn->prepare($communities_query);
$communities_query_stmt->execute();
$communities_result = $communities_query_stmt->get_result();


// Fetch recent activities
$activity_query = "SELECT activity_log.*, users.first_name, users.last_name, users.profile_picture 
                   FROM activity_log
                   JOIN users ON activity_log.user_id = users.user_id
                   WHERE activity_log.user_id = ? ORDER BY activity_log.created_at DESC LIMIT 5";
$activity_query_stmt = $conn->prepare($activity_query);
$activity_query_stmt->bind_param("i", $user_id);
$activity_query_stmt->execute();
$activity_result = $activity_query_stmt->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syngo - Find Your Travel Companions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        .trip-card:hover { transform: translateY(-4px); }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #A5D6A7; border-radius: 3px; }
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
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
<body class="bg-gray-100">

<!-- Custom Navbar -->
<nav class="fixed top-0 left-0 right-0 h-16 bg-white shadow-sm z-50">
    <div class="max-w-7xl mx-auto px-4 h-full flex items-center justify-between">
        <div class="flex items-center gap-8">
            <a href="index.php" class="text-2xl font-['Pacifico'] text-primary">Syngo</a>
            <div class="relative">
                <input type="text" id="search-bar" placeholder="Search destinations or trips..." class="w-[400px] h-10 pl-10 pr-4 text-sm rounded-full border border-gray-200 focus:outline-none focus:border-primary" onkeyup="searchTrips()">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <a href="create_trip.php" class="!rounded-button px-4 h-10 bg-primary text-white flex items-center gap-2 whitespace-nowrap">Create Trip</a>
            <div class="w-10 h-10 flex items-center justify-center relative cursor-pointer">
                <i class="ri-notification-3-line text-Februarygray-600"></i>
                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
            </div>
            <div class="flex items-center gap-3 cursor-pointer">
                <img src="<?= $user['profile_picture'] ?: 'https://public.readdy.ai/ai/img_res/cb6fd3e7b68878d46ddacbc3d3415e6d.jpg' ?>" class="w-10 h-10 rounded-full object-cover">
                <span class="text-sm font-medium"><?= htmlspecialchars($user['first_name'] . " " . $user['last_name']) ?></span>
                <div class="ml-4">
                    <a href="logout.php" class="text-sm text-red-600">Logout</a>
                </div>
            </div>
        </div>
    </div>
</nav>


<!-- Dashboard Container -->
<div class="max-w-7xl mx-auto px-4 mt-20 mb-8 grid grid-cols-12 gap-6">
    <!-- Sidebar Section -->
    <aside class="col-span-12 sm:col-span-3">
        <div class="bg-white rounded-lg p-4 shadow-sm mb-6">
            <div class="flex items-center gap-3 mb-4">
                <img src="<?= $user['profile_picture'] ?: 'https://public.readdy.ai/ai/img_res/ef96e3fecf36463f62ed1a36b8ff8674.jpg' ?>" class="w-14 h-14 rounded-full object-cover">
                <div>
                    <h3 class="font-semibold"><?= htmlspecialchars($user['first_name'] . " " . $user['last_name']) ?></h3>
                    <p class="text-sm text-gray-500">Travel Enthusiast</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <div class="font-semibold"><?= $trips_result->num_rows ?></div>
                    <div class="text-sm text-gray-500">Trips</div>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <div class="font-semibold"><?= $communities_result->num_rows ?></div>
                    <div class="text-sm text-gray-500">Communities</div>
                </div>
            </div>
            <a href="profile.php">
  <button class="w-full !rounded-button h-10 border border-primary text-primary hover:bg-primary hover:text-white transition-colors whitespace-nowrap">
    View Profile
  </button>
</a>

        </div>

        <!-- My Communities Section (Aligned under Profile Section) -->
        <div class="bg-white rounded-lg p-4 shadow-sm">
            <h3 class="font-semibold mb-4">My Communities</h3>
            <div class="space-y-4">
                <?php while ($community = $communities_result->fetch_assoc()) : ?>
                    <div class="flex items-center gap-3">
                        <img src="<?= $community['image'] ?>" class="w-10 h-10 rounded-full object-cover">
                        <div>
                            <div class="font-medium"><?= htmlspecialchars($community['name']) ?></div>
                            <div class="text-sm text-gray-500"><?= $community['member_count'] ?> members</div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <a href="communities.php">
  <button class="w-full !rounded-button h-10 bg-gray-50 text-gray-700 mt-4 hover:bg-gray-100 whitespace-nowrap">
    View All Communities
  </button>
</a>

        </div>
        <!-- Trip Activity Section -->
        <div class="bg-white rounded-lg p-4 shadow-sm mt-6">
            <h3 class="font-semibold mb-4">Trip Activity</h3>
            <div class="space-y-4">
                <?php while ($activity = $activity_result->fetch_assoc()) : ?>
                    <div class="flex items-start gap-3">
                        <img src="<?= $activity['profile_picture'] ?>" class="w-8 h-8 rounded-full">
                        <div>
                            <p class="text-sm"><span class="font-medium"><?= htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']) ?></span> <?= htmlspecialchars($activity['activity_details']) ?></p>
                            <p class="text-xs text-gray-500"><?= date('M d, Y h:i A', strtotime($activity['created_at'])) ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </aside>

<!-- Main Content Section -->
<div class="col-span-12 sm:col-span-9">
    <div class="bg-white rounded-lg p-6 shadow-sm">
        <h2 class="text-2xl font-bold mb-4">My Trips</h2>
        <input type="text" id="search" class="w-full p-2 border rounded mb-4" placeholder="Search Trips..." onkeyup="searchTrips()">

        <div id="trip-list">
            <?php while ($trip = $trips_result->fetch_assoc()) : ?>
                <?php
                // Fetch participants for the trip
                $participants_query = "SELECT users.profile_picture FROM trip_members 
                                       JOIN users ON trip_members.user_id = users.user_id 
                                       WHERE trip_members.trip_id = ?";
                $participants_stmt = $conn->prepare($participants_query);
                $participants_stmt->bind_param("i", $trip['trip_id']);
                $participants_stmt->execute();
                $participants_result = $participants_stmt->get_result();
                $participants = [];
                while ($participant = $participants_result->fetch_assoc()) {
                    $participants[] = $participant['profile_picture'];
                }
                $spots_left = $trip['group_size'] - count($participants);
                ?>

                <div class="trip-card bg-white rounded-lg border border-gray-100 overflow-hidden mb-4">
                    <!-- Display trip image if available, else show a default image -->
                    <img src="<?= htmlspecialchars($trip['trip_image']) ?: 'default-image.jpg' ?>" class="w-full h-48 object-cover">
                    <div class="p-4">
                        <h3 class="font-semibold text-lg"><?= htmlspecialchars($trip['title']) ?></h3>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($trip['destination']) ?></p>
                        <div class="flex items-center gap-4 mb-4">
                            <span class="text-sm text-gray-500"><?= date('M d', strtotime($trip['start_date'])) ?> - <?= date('M d', strtotime($trip['end_date'])) ?></span>
                            <span class="text-sm text-gray-500"><?= $spots_left ?>/<?= $trip['group_size'] ?> spots left</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-4"><?= htmlspecialchars($trip['description']) ?></p>
                        <div class="flex items-center justify-between">
                            <div class="flex -space-x-2">
                                <?php foreach ($participants as $participant_image) : ?>
                                    <img src="<?= $participant_image ?>" class="w-8 h-8 rounded-full border-2 border-white">
                                <?php endforeach; ?>
                            </div>
                            <!-- Updated Join Trip button with dynamic trip_id -->
                            <a href="join_trip.php?trip_id=<?= $trip['trip_id'] ?>">
                                <button class="!rounded-button px-4 h-10 bg-primary text-white whitespace-nowrap">
                                    Join Trip
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>


<script>
    function searchTrips() {
        var input = document.getElementById("search").value.toLowerCase();
        var tripCards = document.querySelectorAll(".trip-card");

        tripCards.forEach(function(card) {
            var title = card.querySelector("h3").innerText.toLowerCase();
            var destination = card.querySelector("p.text-sm.text-gray-500").innerText.toLowerCase();
            if (title.includes(input) || destination.includes(input)) {
                card.style.display = "";
            } else {
                card.style.display = "none";
            }
        });
    }
</script>

</body>
</html>
