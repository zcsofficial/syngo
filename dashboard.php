<?php
// Include database connection and session management
include('config.php');
session_start();

// Assuming user is logged in and user details are stored in session
$user_id = $_SESSION['user_id'];

// Fetch user profile from the database
$user_query = "SELECT first_name, last_name, profile_picture, role FROM users WHERE user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch user trips
$trips_query = "SELECT t.trip_id, t.title, t.destination, t.start_date, t.end_date, t.group_size, t.description, 
                       COUNT(tm.user_id) AS members_count
                FROM trips t
                LEFT JOIN trip_members tm ON t.trip_id = tm.trip_id
                WHERE t.created_by = ?
                GROUP BY t.trip_id";
$stmt = $conn->prepare($trips_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$trips_result = $stmt->get_result();

// Fetch user communities
$communities_query = "SELECT c.community_id, c.name, c.description, COUNT(uc.user_id) AS members_count 
                      FROM communities c
                      LEFT JOIN user_communities uc ON c.community_id = uc.community_id
                      WHERE c.created_by = ?
                      GROUP BY c.community_id";
$stmt = $conn->prepare($communities_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$communities_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syngo - Dashboard</title>
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
                        primary: '#2E7D32',
                        secondary: '#A5D6A7'
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
<body class="bg-gray-50 font-['Inter']">
    <nav class="fixed top-0 left-0 right-0 h-16 bg-white shadow-sm z-50">
        <div class="max-w-7xl mx-auto px-4 h-full flex items-center justify-between">
            <div class="flex items-center gap-8">
                <a href="#" class="text-2xl font-['Pacifico'] text-primary">logo</a>
                <div class="relative">
                    <input type="text" placeholder="Search destinations or trips..." class="w-[400px] h-10 pl-10 pr-4 text-sm rounded-full border border-gray-200 focus:outline-none focus:border-primary">
                    <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="create_trip.php" class="!rounded-button px-4 h-10 bg-primary text-white flex items-center gap-2 whitespace-nowrap">
                    <i class="ri-add-line w-4 h-4 flex items-center justify-center"></i>
                    Create Trip
                </a>
                <div class="w-10 h-10 flex items-center justify-center relative cursor-pointer">
                    <i class="ri-notification-3-line text-gray-600"></i>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </div>
                <div class="flex items-center gap-3 cursor-pointer">
                    <img src="<?= $user['profile_picture'] ?>" class="w-10 h-10 rounded-full object-cover">
                    <span class="text-sm font-medium"><?= $user['first_name'] . ' ' . $user['last_name'] ?></span>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 mt-20 mb-8 grid grid-cols-12 gap-6">
        <aside class="col-span-3">
            <div class="bg-white rounded-lg p-4 shadow-sm mb-6">
                <div class="flex items-center gap-3 mb-4">
                    <img src="<?= $user['profile_picture'] ?>" class="w-14 h-14 rounded-full object-cover">
                    <div>
                        <h3 class="font-semibold"><?= $user['first_name'] . ' ' . $user['last_name'] ?></h3>
                        <p class="text-sm text-gray-500"><?= $user['role'] ?></p>
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
                <a href="profile.php" class="w-full !rounded-button h-10 border border-primary text-primary hover:bg-primary hover:text-white transition-colors whitespace-nowrap">View Profile</a>
            </div>

            <div class="bg-white rounded-lg p-4 shadow-sm">
                <h3 class="font-semibold mb-4">My Communities</h3>
                <div class="space-y-4">
                    <?php while ($community = $communities_result->fetch_assoc()) : ?>
                        <div class="flex items-center gap-3">
                            <img src="<?= $community['image'] ?>" class="w-10 h-10 rounded-full object-cover">
                            <div>
                                <div class="font-medium"><?= $community['name'] ?></div>
                                <div class="text-sm text-gray-500"><?= $community['members_count'] ?> members</div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                <a href="all_communities.php" class="w-full !rounded-button h-10 bg-gray-50 text-gray-700 mt-4 hover:bg-gray-100 whitespace-nowrap">View All Communities</a>
            </div>
        </aside>

        <main class="col-span-6">
            <div class="bg-white rounded-lg p-4 shadow-sm mb-6">
                <div class="flex items-center gap-4 mb-6">
                    <a href="discover_trips.php" class="!rounded-full px-4 h-8 bg-primary text-white whitespace-nowrap">Discover Trips</a>
                    <a href="my_trips.php" class="!rounded-full px-4 h-8 bg-gray-50 text-gray-700 whitespace-nowrap">My Trips</a>
                </div>

                <div class="space-y-6">
                    <?php while ($trip = $trips_result->fetch_assoc()) : ?>
                        <div class="trip-card bg-white rounded-lg border border-gray-100 overflow-hidden transition-transform duration-200">
                            <img src="<?= $trip['image'] ?>" class="w-full h-48 object-cover">
                            <div class="p-4">
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <h3 class="font-semibold text-lg"><?= $trip['title'] ?></h3>
                                        <p class="text-sm text-gray-500"><?= $trip['destination'] ?></p>
                                    </div>
                                    <div class="flex items-center gap-1 text-sm text-yellow-500">
                                        <i class="ri-star-fill w-4 h-4 flex items-center justify-center"></i>
                                        <span><?= $trip['rating'] ?></span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 mb-4">
                                    <div class="flex items-center gap-1 text-sm text-gray-500">
                                        <i class="ri-calendar-line w-4 h-4 flex items-center justify-center"></i>
                                        <span><?= $trip['start_date'] ?> - <?= $trip['end_date'] ?></span>
                                    </div>
                                    <div class="flex items-center gap-1 text-sm text-gray-500">
                                        <i class="ri-group-line w-4 h-4 flex items-center justify-center"></i>
                                        <span><?= $trip['members_count'] ?> members</span>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 mb-4"><?= $trip['description'] ?></p>
                                <div class="flex items-center justify-between">
                                    <a href="trip_details.php?id=<?= $trip['trip_id'] ?>" class="text-sm text-primary hover:underline">View Details</a>
                                    <button class="px-4 h-8 text-sm bg-primary text-white rounded-full">Join Trip</button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </main>

        <aside class="col-span-3">
            <!-- Additional content like ads, trip suggestions, etc. can be added here. -->
        </aside>
    </div>
</body>
</html>
