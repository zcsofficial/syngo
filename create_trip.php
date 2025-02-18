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
            $trip_query = "INSERT INTO trips (title, destination, start_date, end_date, group_size, description, trip_image, created_by) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $trip_stmt = $conn->prepare($trip_query);
            $trip_stmt->bind_param("ssssissi", $title, $destination, $start_date, $end_date, $group_size, $description, $trip_image, $user_id);
            $trip_stmt->execute();

            if ($trip_stmt->affected_rows > 0) {
                $success_message = "Trip created successfully!";
                // Optionally, you can redirect the user to the trip page after creating the trip
                // header("Location: trip.php?id=" . $conn->insert_id);
                // exit();
            } else {
                $error_message = "There was an issue creating your trip. Please try again.";
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
                <i class="ri-notification-3-line text-gray-600"></i>
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

<!-- Create Trip Form -->
<div class="max-w-7xl mx-auto px-4 mt-20 mb-8">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-semibold mb-6">Create a New Trip</h2>

        <?php if (isset($error_message)) { ?>
            <div class="bg-red-100 text-red-700 p-4 mb-4 rounded-md">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php } ?>
        <?php if (isset($success_message)) { ?>
            <div class="bg-green-100 text-green-700 p-4 mb-4 rounded-md">
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php } ?>

        <form method="POST" action="create_trip.php" enctype="multipart/form-data">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="col-span-1 sm:col-span-2">
                    <label for="trip_image" class="block text-sm font-medium text-gray-700">Trip Display Picture</label>
                    <input type="file" id="trip_image" name="trip_image" class="input-field w-full p-3 mt-2 border rounded-md" accept="image/*">
                </div>
                <div class="col-span-1">
                    <label for="title" class="block text-sm font-medium text-gray-700">Trip Title</label>
                    <input type="text" id="title" name="title" class="input-field w-full p-3 mt-2 border rounded-md" required>
                </div>
                <div class="col-span-1">
                    <label for="destination" class="block text-sm font-medium text-gray-700">Destination</label>
                    <input type="text" id="destination" name="destination" class="input-field w-full p-3 mt-2 border rounded-md" required>
                </div>
                <div class="col-span-1 sm:col-span-2">
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="input-field w-full p-3 mt-2 border rounded-md" required>
                </div>
                <div class="col-span-1 sm:col-span-2">
                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="input-field w-full p-3 mt-2 border rounded-md" required>
                </div>
                <div class="col-span-1">
                    <label for="group_size" class="block text-sm font-medium text-gray-700">Group Size</label>
                    <input type="number" id="group_size" name="group_size" class="input-field w-full p-3 mt-2 border rounded-md" required min="1">
                </div>
                <div class="col-span-1 sm:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">Trip Description</label>
                    <textarea id="description" name="description" rows="4" class="input-field w-full p-3 mt-2 border rounded-md" required></textarea>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="w-full sm:w-auto bg-primary text-white py-3 px-6 rounded-md hover:bg-green-600">Create Trip</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
