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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $destination = $_POST['destination'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $group_size = $_POST['group_size'];
    $description = $_POST['description'];
    $trip_image = '';

    // Validate input
    if (empty($title) || empty($destination) || empty($start_date) || empty($end_date) || empty($group_size)) {
        $error_message = "All fields are required.";
    } else {
        // Handle file upload
        if (isset($_FILES['trip_image']) && $_FILES['trip_image']['error'] == 0) {
            $image_tmp = $_FILES['trip_image']['tmp_name'];
            $image_name = $_FILES['trip_image']['name'];
            $image_size = $_FILES['trip_image']['size'];
            $image_extension = pathinfo($image_name, PATHINFO_EXTENSION);

            // Validate image extension
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array(strtolower($image_extension), $allowed_extensions)) {
                $error_message = "Invalid image format. Allowed formats: jpg, jpeg, png, gif.";
            } elseif ($image_size > 5000000) { // 5MB max size
                $error_message = "Image size exceeds the maximum allowed size of 5MB.";
            } else {
                // Generate a unique file name
                $image_new_name = uniqid('trip_', true) . '.' . $image_extension;
                $image_upload_dir = 'uploads/trips/';

                // Ensure the directory exists
                if (!file_exists($image_upload_dir)) {
                    mkdir($image_upload_dir, 0777, true);
                }

                // Move uploaded file to the desired directory
                if (move_uploaded_file($image_tmp, $image_upload_dir . $image_new_name)) {
                    $trip_image = $image_upload_dir . $image_new_name;
                } else {
                    $error_message = "There was an error uploading the image. Please try again.";
                }
            }
        }

        // Insert trip into the database if no errors
        if (!isset($error_message)) {
            // Start a transaction to ensure atomicity
            $conn->begin_transaction();

            try {
                // Insert the trip
                $trip_query = "INSERT INTO trips (title, destination, start_date, end_date, group_size, description, trip_image, created_by) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $trip_stmt = $conn->prepare($trip_query);
                $trip_stmt->bind_param("ssssissi", $title, $destination, $start_date, $end_date, $group_size, $description, $trip_image, $user_id);
                $trip_stmt->execute();

                if ($trip_stmt->affected_rows > 0) {
                    $trip_id = $conn->insert_id; // Get the newly created trip ID

                    // Create a trip group with the same name as the trip
                    $group_query = "INSERT INTO trip_groups (trip_id, name) VALUES (?, ?)";
                    $group_stmt = $conn->prepare($group_query);
                    $group_stmt->bind_param("is", $trip_id, $title);
                    $group_stmt->execute();

                    if ($group_stmt->affected_rows > 0) {
                        $group_id = $conn->insert_id; // Get the newly created group ID

                        // Add the creator to the trip_members table
                        $member_query = "INSERT INTO trip_members (trip_id, user_id) VALUES (?, ?)";
                        $member_stmt = $conn->prepare($member_query);
                        $member_stmt->bind_param("ii", $trip_id, $user_id);
                        $member_stmt->execute();

                        // Add the creator to the trip_group_members table
                        $group_member_query = "INSERT INTO trip_group_members (group_id, user_id) VALUES (?, ?)";
                        $group_member_stmt = $conn->prepare($group_member_query);
                        $group_member_stmt->bind_param("ii", $group_id, $user_id);
                        $group_member_stmt->execute();

                        // Commit the transaction
                        $conn->commit();
                        $success_message = "Trip and group created successfully!";
                        // Optionally redirect to dashboard
                        // header("Location: dashboard.php");
                        // exit();
                    } else {
                        throw new Exception("Failed to create trip group.");
                    }
                } else {
                    throw new Exception("Failed to create trip.");
                }
            } catch (Exception $e) {
                // Rollback on error
                $conn->rollback();
                $error_message = "Error: " . $e->getMessage() . " Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Trip - Syngo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
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

    <!-- Create Trip Form -->
    <div class="max-w-3xl mx-auto px-4 pt-20 pb-8">
        <div class="bg-white p-4 sm:p-6 rounded-lg shadow-md">
            <h2 class="text-xl sm:text-2xl font-semibold mb-4 sm:mb-6">Create a New Trip</h2>

            <?php if (isset($error_message)) : ?>
                <div class="bg-red-100 text-red-700 p-3 sm:p-4 mb-4 rounded-md text-sm">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            <?php if (isset($success_message)) : ?>
                <div class="bg-green-100 text-green-700 p-3 sm:p-4 mb-4 rounded-md text-sm">
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="create_trip.php" enctype="multipart/form-data" class="space-y-4 sm:space-y-6">
                <div>
                    <label for="trip_image" class="block text-sm font-medium text-gray-700">Trip Display Picture</label>
                    <input type="file" id="trip_image" name="trip_image" class="w-full mt-2 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:bg-primary file:text-white hover:file:bg-primary/90" accept="image/*">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Trip Title</label>
                        <input type="text" id="title" name="title" class="w-full p-2 sm:p-3 mt-2 border rounded-md focus:ring-2 focus:ring-primary focus:border-primary text-sm" required>
                    </div>
                    <div>
                        <label for="destination" class="block text-sm font-medium text-gray-700">Destination</label>
                        <input type="text" id="destination" name="destination" class="w-full p-2 sm:p-3 mt-2 border rounded-md focus:ring-2 focus:ring-primary focus:border-primary text-sm" required>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" id="start_date" name="start_date" class="w-full p-2 sm:p-3 mt-2 border rounded-md focus:ring-2 focus:ring-primary focus:border-primary text-sm" required>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" id="end_date" name="end_date" class="w-full p-2 sm:p-3 mt-2 border rounded-md focus:ring-2 focus:ring-primary focus:border-primary text-sm" required>
                    </div>
                    <div>
                        <label for="group_size" class="block text-sm font-medium text-gray-700">Group Size</label>
                        <input type="number" id="group_size" name="group_size" class="w-full p-2 sm:p-3 mt-2 border rounded-md focus:ring-2 focus:ring-primary focus:border-primary text-sm" required min="1">
                    </div>
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Trip Description</label>
                    <textarea id="description" name="description" rows="4" class="w-full p-2 sm:p-3 mt-2 border rounded-md focus:ring-2 focus:ring-primary focus:border-primary text-sm" required></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="w-full sm:w-auto !rounded-button px-4 sm:px-6 py-2 bg-primary text-white text-sm sm:text-base hover:bg-primary/90">Create Trip</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>