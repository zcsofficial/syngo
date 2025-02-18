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
    <title>Syngo Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <style>
        /* Custom colors for Syngo */
        .bg-primary {
            background-color: #22C55E; /* Syngo's primary color */
        }
        .text-primary {
            color: #22C55E;
        }
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
    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 h-16 bg-white shadow-sm z-50">
        <div class="max-w-7xl mx-auto px-4 h-full flex items-center justify-between">
            <div class="flex items-center gap-8">
                <a href="index.php" class="text-2xl font-['Pacifico'] text-primary">Syngo</a>
            </div>
            <div class="flex items-center gap-4">
                <!-- Dashboard link -->
                <a href="dashboard.php" class="text-green">Dashboard</a>
                <!-- Profile section -->
                <div class="flex items-center gap-3 cursor-pointer">
                    <img src="<?= $user['profile_picture'] ?: 'https://public.readdy.ai/ai/img_res/cb6fd3e7b68878d46ddacbc3d3415e6d.jpg' ?>" class="w-10 h-10 rounded-full object-cover">
                    <span class="text-sm font-medium"><?= $user['first_name'] . ' ' . $user['last_name'] ?></span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Section -->
    <div class="max-w-4xl mx-auto px-4 py-24 pt-20">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="p-8">
                <h2 class="text-2xl font-semibold text-gray-800"><?= htmlspecialchars($trip['title']) ?></h2>
                <p class="text-gray-600"><?= htmlspecialchars($trip['description']) ?></p>

                <div class="mt-4">
                    <p><strong>Destination:</strong> <?= htmlspecialchars($trip['destination']) ?></p>
                    <p><strong>Start Date:</strong> <?= date('M d, Y', strtotime($trip['start_date'])) ?></p>
                    <p><strong>End Date:</strong> <?= date('M d, Y', strtotime($trip['end_date'])) ?></p>
                    <p><strong>Group Size:</strong> <?= $group_size ?></p>
                    <p><strong>Current Members:</strong> <?= $members['total_members'] ?></p>
                </div>

                <!-- Participants -->
                <div class="mt-6">
                    <h3 class="text-xl font-semibold mb-4">Participants</h3>
                    <div class="flex space-x-4">
                        <?php while ($participant = $participantsResult->fetch_assoc()) : ?>
                            <div class="flex items-center space-x-2">
                                <img src="<?= $participant['profile_picture'] ?: 'default-profile.jpg' ?>" class="w-10 h-10 rounded-full" alt="<?= htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name']) ?>">
                                <p class="text-sm"><?= htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name']) ?></p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Join Trip Button -->
                <div class="mt-6">
                    <?php
                    // Check if the user is already a member of the trip
                    $checkQuery = "SELECT * FROM trip_members WHERE trip_id = ? AND user_id = ?";
                    $stmt = $conn->prepare($checkQuery);
                    $stmt->bind_param("ii", $trip_id, $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        echo "<p class='text-green-600'>You are already a member of this trip.</p>";
                    } else {
                        // If the trip is not full, show the join button
                        if ($members['total_members'] < $group_size) {
                            echo "<a href='join_trip.php?trip_id=" . $trip_id . "'>
                                    <button class='bg-primary text-white px-6 py-2 rounded-md'>Join This Trip</button>
                                  </a>";
                        } else {
                            echo "<p class='text-red-600'>This trip is already full.</p>";
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
