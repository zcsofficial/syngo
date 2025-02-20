<?php
session_start();
require 'config.php'; // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data including role
$user_query = $conn->prepare("SELECT first_name, last_name, email, profile_picture, role FROM users WHERE user_id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

// Pagination settings
$trips_per_page = 3; // Number of trips per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page from URL, default to 1
$offset = ($page - 1) * $trips_per_page; // Calculate offset

// Fetch total number of trips for pagination
$total_trips_query = "SELECT COUNT(*) as total FROM trips";
$total_trips_stmt = $conn->prepare($total_trips_query);
$total_trips_stmt->execute();
$total_trips_result = $total_trips_stmt->get_result();
$total_trips = $total_trips_result->fetch_assoc()['total'];
$total_pages = ceil($total_trips / $trips_per_page); // Calculate total pages

// Fetch trips with pagination
$trips_query = "SELECT trip_id, title, destination, start_date, end_date, group_size, description, trip_image 
                FROM trips 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
$trips_stmt = $conn->prepare($trips_query);
$trips_stmt->bind_param("ii", $trips_per_page, $offset);
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
                   WHERE activity_log.user_id = ? 
                   ORDER BY activity_log.created_at DESC 
                   LIMIT 5";
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
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
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
<body class="bg-gray-100 font-sans min-h-screen">
    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 h-16 bg-white shadow-sm z-50">
        <div class="max-w-7xl mx-auto px-4 h-full flex items-center justify-between">
            <div class="flex items-center gap-4 sm:gap-8">
                <a href="index.php" class="text-xl sm:text-2xl font-['Pacifico'] text-primary">Syngo</a>
                <div class="relative hidden sm:block">
                    <input type="text" id="search-bar" placeholder="Search destinations or trips..." class="w-40 sm:w-64 md:w-[400px] h-10 pl-10 pr-4 text-sm rounded-full border border-gray-200 focus:outline-none focus:border-primary" onkeyup="searchTrips()">
                    <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            <div class="flex items-center gap-2 sm:gap-4">
                <a href="create_trip.php" class="!rounded-button px-3 sm:px-4 h-9 sm:h-10 bg-primary text-white text-sm flex items-center gap-2 whitespace-nowrap">Create Trip</a>
                <?php if ($user['role'] === 'admin') : ?>
                    <a href="admin.php" class="!rounded-button px-3 sm:px-4 h-9 sm:h-10 bg-blue-600 text-white text-sm flex items-center gap-2 whitespace-nowrap">Admin Panel</a>
                <?php endif; ?>
                <div class="w-8 sm:w-10 h-8 sm:h-10 flex items-center justify-center relative cursor-pointer">
                    <i class="ri-notification-3-line text-gray-600 text-lg sm:text-xl"></i>
                    <span class="absolute top-0 sm:top-1 right-0 sm:right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </div>
                <div class="flex items-center gap-2 sm:gap-3 cursor-pointer">
                    <img src="<?= $user['profile_picture'] ?: 'https://public.readdy.ai/ai/img_res/cb6fd3e7b68878d46ddacbc3d3415e6d.jpg' ?>" class="w-8 sm:w-10 h-8 sm:h-10 rounded-full object-cover">
                    <span class="text-xs sm:text-sm font-medium hidden sm:block"><?= htmlspecialchars($user['first_name'] . " " . $user['last_name']) ?></span>
                    <a href="logout.php" class="text-xs sm:text-sm text-red-600 ml-2">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Dashboard Container -->
    <div class="max-w-7xl mx-auto px-4 pt-20 pb-8 flex flex-col sm:grid sm:grid-cols-12 gap-6">
        <!-- Sidebar Section -->
        <aside class="col-span-12 sm:col-span-3 space-y-6">
            <!-- Profile Card -->
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <img src="<?= $user['profile_picture'] ?: 'https://public.readdy.ai/ai/img_res/ef96e3fecf36463f62ed1a36b8ff8674.jpg' ?>" class="w-12 sm:w-14 h-12 sm:h-14 rounded-full object-cover">
                    <div>
                        <h3 class="font-semibold text-sm sm:text-base"><?= htmlspecialchars($user['first_name'] . " " . $user['last_name']) ?></h3>
                        <p class="text-xs sm:text-sm text-gray-500">Travel Enthusiast</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="text-center p-2 sm:p-3 bg-gray-50 rounded-lg">
                        <div class="font-semibold text-sm sm:text-base"><?= $total_trips ?></div>
                        <div class="text-xs text-gray-500">Trips</div>
                    </div>
                    <div class="text-center p-2 sm:p-3 bg-gray-50 rounded-lg">
                        <div class="font-semibold text-sm sm:text-base"><?= $communities_result->num_rows ?></div>
                        <div class="text-xs text-gray-500">Communities</div>
                    </div>
                </div>
                <a href="profile.php">
                    <button class="w-full !rounded-button h-9 sm:h-10 border border-primary text-primary hover:bg-primary hover:text-white transition-colors text-sm whitespace-nowrap">
                        View Profile
                    </button>
                </a>
            </div>

            <!-- Communities Section -->
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <h3 class="font-semibold text-sm sm:text-base mb-4">My Communities</h3>
                <div class="space-y-3 sm:space-y-4 max-h-64 overflow-y-auto custom-scrollbar">
                    <?php while ($community = $communities_result->fetch_assoc()) : ?>
                        <div class="flex items-center gap-3">
                            <img src="<?= $community['image'] ?>" class="w-8 sm:w-10 h-8 sm:h-10 rounded-full object-cover">
                            <div>
                                <div class="font-medium text-sm"><?= htmlspecialchars($community['name']) ?></div>
                                <div class="text-xs text-gray-500"><?= $community['member_count'] ?> members</div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                <a href="communities.php">
                    <button class="w-full !rounded-button h-9 sm:h-10 bg-gray-50 text-gray-700 mt-4 hover:bg-gray-100 text-sm whitespace-nowrap">
                        View All Communities
                    </button>
                </a>
            </div>

            <!-- Trip Activity Section -->
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <h3 class="font-semibold text-sm sm:text-base mb-4">Trip Activity</h3>
                <div class="space-y-3 sm:space-y-4 max-h-64 overflow-y-auto custom-scrollbar">
                    <?php while ($activity = $activity_result->fetch_assoc()) : ?>
                        <div class="flex items-start gap-2 sm:gap-3">
                            <img src="<?= $activity['profile_picture'] ?>" class="w-6 sm:w-8 h-6 sm:h-8 rounded-full">
                            <div>
                                <p class="text-xs sm:text-sm"><span class="font-medium"><?= htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']) ?></span> <?= htmlspecialchars($activity['activity_details']) ?></p>
                                <p class="text-xs text-gray-500"><?= date('M d, Y h:i A', strtotime($activity['created_at'])) ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </aside>

        <!-- Main Content Section -->
        <main class="col-span-12 sm:col-span-9">
            <div class="bg-white rounded-lg p-4 sm:p-6 shadow-sm">
                <h2 class="text-xl sm:text-2xl font-bold mb-4">My Trips</h2>
                <input type="text" id="search" class="w-full p-2 border rounded mb-4 text-sm" placeholder="Search Trips..." onkeyup="searchTrips()">

                <div id="trip-list" class="space-y-4">
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

                        // Check if the current user is a member of this trip
                        $is_member_query = "SELECT * FROM trip_members WHERE trip_id = ? AND user_id = ?";
                        $is_member_stmt = $conn->prepare($is_member_query);
                        $is_member_stmt->bind_param("ii", $trip['trip_id'], $user_id);
                        $is_member_stmt->execute();
                        $is_member_result = $is_member_stmt->get_result();
                        $is_member = $is_member_result->num_rows > 0;
                        ?>

                        <div class="trip-card bg-white rounded-lg border border-gray-100 overflow-hidden transition-transform">
                            <img src="<?= htmlspecialchars($trip['trip_image']) ?: 'default-image.jpg' ?>" class="w-full h-40 sm:h-48 object-cover">
                            <div class="p-4">
                                <h3 class="font-semibold text-base sm:text-lg"><?= htmlspecialchars($trip['title']) ?></h3>
                                <p class="text-xs sm:text-sm text-gray-500"><?= htmlspecialchars($trip['destination']) ?></p>
                                <div class="flex flex-col sm:flex-row sm:items-center sm:gap-4 mb-4 mt-2">
                                    <span class="text-xs sm:text-sm text-gray-500"><?= date('M d', strtotime($trip['start_date'])) ?> - <?= date('M d', strtotime($trip['end_date'])) ?></span>
                                    <span class="text-xs sm:text-sm text-gray-500"><?= $spots_left ?>/<?= $trip['group_size'] ?> spots left</span>
                                </div>
                                <p class="text-xs sm:text-sm text-gray-600 mb-4 line-clamp-2"><?= htmlspecialchars($trip['description']) ?></p>
                                <div class="flex items-center justify-between">
                                    <div class="flex -space-x-1 sm:-space-x-2">
                                        <?php foreach ($participants as $participant_image) : ?>
                                            <img src="<?= $participant_image ?>" class="w-6 sm:w-8 h-6 sm:h-8 rounded-full border-2 border-white">
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if ($is_member) : ?>
                                        <a href="chat_group.php?trip_id=<?= $trip['trip_id'] ?>">
                                            <button class="!rounded-button px-3 sm:px-4 h-9 sm:h-10 bg-primary text-white text-sm whitespace-nowrap">
                                                Chat
                                            </button>
                                        </a>
                                    <?php else : ?>
                                        <a href="join_trip.php?trip_id=<?= $trip['trip_id'] ?>">
                                            <button class="!rounded-button px-3 sm:px-4 h-9 sm:h-10 bg-primary text-white text-sm whitespace-nowrap">
                                                Join Trip
                                            </button>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination Controls -->
                <?php if ($total_pages > 1) : ?>
                    <div class="mt-6 flex flex-wrap justify-center gap-2">
                        <!-- Previous Button -->
                        <a href="?page=<?= max(1, $page - 1) ?>" class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-100 <?= $page <= 1 ? 'pointer-events-none opacity-50' : '' ?>">
                            Previous
                        </a>

                        <!-- Page Numbers -->
                        <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                            <a href="?page=<?= $i ?>" class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-100 <?= $i === $page ? 'bg-primary text-white border-primary' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <!-- Next Button -->
                        <a href="?page=<?= min($total_pages, $page + 1) ?>" class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-100 <?= $page >= $total_pages ? 'pointer-events-none opacity-50' : '' ?>">
                            Next
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        function searchTrips() {
            var input = document.getElementById("search").value.toLowerCase();
            var tripCards = document.querySelectorAll(".trip-card");

            tripCards.forEach(function(card) {
                var title = card.querySelector("h3").innerText.toLowerCase();
                var destination = card.querySelector("p.text-sm.text-gray-500, p.text-xs.text-gray-500").innerText.toLowerCase();
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