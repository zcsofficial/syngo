<?php
// Start the session and include necessary files
session_start();

// Include the database connection
include('config.php');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user data for navbar
$user_query = $conn->prepare("SELECT first_name, last_name, email, profile_picture FROM users WHERE user_id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

// Get the trip ID from the URL
if (isset($_GET['trip_id'])) {
    $trip_id = (int)$_GET['trip_id'];

    // Fetch the trip details
    $tripQuery = "SELECT * FROM trips WHERE trip_id = ?";
    $stmt = $conn->prepare($tripQuery);
    $stmt->bind_param("i", $trip_id);
    $stmt->execute();
    $tripResult = $stmt->get_result();

    // If the trip exists
    if ($tripResult->num_rows > 0) {
        $trip = $tripResult->fetch_assoc();
        $group_size = $trip['group_size'];

        // Get the current number of members in the trip
        $membersQuery = "SELECT COUNT(*) AS total_members FROM trip_members WHERE trip_id = ?";
        $stmt = $conn->prepare($membersQuery);
        $stmt->bind_param("i", $trip_id);
        $stmt->execute();
        $membersResult = $stmt->get_result();
        $members = $membersResult->fetch_assoc();

        // Fetch the participants for this trip
        $participantsQuery = "SELECT users.user_id, users.first_name, users.last_name, users.profile_picture 
                              FROM trip_members 
                              JOIN users ON trip_members.user_id = users.user_id 
                              WHERE trip_members.trip_id = ?";
        $stmt = $conn->prepare($participantsQuery);
        $stmt->bind_param("i", $trip_id);
        $stmt->execute();
        $participantsResult = $stmt->get_result();

        // Check if the user is a member of the trip
        $checkQuery = "SELECT * FROM trip_members WHERE trip_id = ? AND user_id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("ii", $trip_id, $user_id);
        $stmt->execute();
        $is_member_result = $stmt->get_result();
        $is_member = $is_member_result->num_rows > 0;
    } else {
        header('Location: index.php');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syngo - Trip Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
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
    <!-- Navbar (Matched with dashboard.php) -->
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
                    <img src="<?= $user['profile_picture'] ?: 'https://public.readdy.ai/ai/img_res/cb6fd3e7b68878d46ddacbc3d3415e6d.jpg' ?>" class="w-8 sm:w-10 h-8 sm:h-10 rounded-full object-cover">
                    <span class="text-xs sm:text-sm font-medium hidden sm:block"><?= htmlspecialchars($user['first_name'] . " " . $user['last_name']) ?></span>
                    <a href="logout.php" class="text-xs sm:text-sm text-red-600 ml-2">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Section -->
    <div class="max-w-4xl mx-auto px-4 py-16 sm:py-24 pt-20">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="p-4 sm:p-8">
                <h2 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-2"><?= htmlspecialchars($trip['title']) ?></h2>
                <p class="text-xs sm:text-sm text-gray-600 mb-4"><?= htmlspecialchars($trip['description']) ?></p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <p class="text-xs sm:text-sm"><strong>Destination:</strong> <?= htmlspecialchars($trip['destination']) ?></p>
                        <p class="text-xs sm:text-sm"><strong>Start Date:</strong> <?= date('M d, Y', strtotime($trip['start_date'])) ?></p>
                        <p class="text-xs sm:text-sm"><strong>End Date:</strong> <?= date('M d, Y', strtotime($trip['end_date'])) ?></p>
                    </div>
                    <div>
                        <p class="text-xs sm:text-sm"><strong>Group Size:</strong> <?= $group_size ?></p>
                        <p class="text-xs sm:text-sm"><strong>Current Members:</strong> <?= $members['total_members'] ?></p>
                    </div>
                </div>

                <!-- Participants -->
                <div class="mt-6">
                    <h3 class="text-lg sm:text-xl font-semibold mb-4">Participants</h3>
                    <ul class="space-y-3">
                        <?php while ($participant = $participantsResult->fetch_assoc()) : ?>
                            <li>
                                <a href="member.php?user_id=<?= $participant['user_id'] ?>" class="flex items-center space-x-3 hover:bg-gray-50 p-2 rounded">
                                    <img src="<?= $participant['profile_picture'] ?: 'default-profile.jpg' ?>" class="w-8 sm:w-10 h-8 sm:h-10 rounded-full object-cover" alt="<?= htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name']) ?>">
                                    <p class="text-xs sm:text-sm"><?= htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name']) ?></p>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex flex-col sm:flex-row gap-4">
                    <?php if ($is_member) : ?>
                        <a href="chat_group.php?trip_id=<?= $trip_id ?>">
                            <button class="!rounded-button px-4 sm:px-6 py-2 bg-primary text-white text-sm sm:text-base hover:bg-primary/90 whitespace-nowrap">
                                Chat
                            </button>
                        </a>
                    <?php else : ?>
                        <?php if ($members['total_members'] < $group_size) : ?>
                            <a href="join_trip.php?trip_id=<?= $trip_id ?>">
                                <button class="!rounded-button px-4 sm:px-6 py-2 bg-primary text-white text-sm sm:text-base hover:bg-primary/90 whitespace-nowrap">
                                    Join This Trip
                                </button>
                            </a>
                        <?php else : ?>
                            <p class="text-xs sm:text-sm text-red-600">This trip is already full.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>