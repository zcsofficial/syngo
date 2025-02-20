<?php
session_start();
require 'config.php'; // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data for navbar
$user_query = $conn->prepare("SELECT first_name, last_name, profile_picture FROM users WHERE user_id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

// Handle form submission to create a new community
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $community_name = trim($_POST['name']);
    $community_description = trim($_POST['description']);
    $image = $_FILES['image'];

    // Validate inputs
    if (empty($community_name) || empty($community_description)) {
        $error_message = "Both name and description are required.";
    } else {
        // Handle image upload
        $community_image = null;
        if ($image['error'] === 0) {
            $target_dir = "uploads/communities/"; // Define the directory to store images
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true); // Create directory if it doesn't exist
            }
            $image_name = uniqid('community_', true) . "." . strtolower(pathinfo($image["name"], PATHINFO_EXTENSION));
            $target_file = $target_dir . $image_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if the file is a valid image
            $valid_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($imageFileType, $valid_extensions)) {
                if (move_uploaded_file($image["tmp_name"], $target_file)) {
                    $community_image = $target_file;
                } else {
                    $error_message = "Sorry, there was an error uploading your image.";
                }
            } else {
                $error_message = "Only JPG, JPEG, PNG, and GIF files are allowed.";
            }
        }

        if (!isset($error_message)) {
            // Start a transaction to ensure atomicity
            $conn->begin_transaction();

            try {
                // Insert the new community into the database
                $stmt = $conn->prepare("INSERT INTO communities (name, description, image) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $community_name, $community_description, $community_image);
                $stmt->execute();

                // Get the newly created community ID
                $community_id = $conn->insert_id;

                // Insert the user as an admin into community_members
                $role = 'admin';
                $user_community_stmt = $conn->prepare("INSERT INTO community_members (community_id, user_id, role) VALUES (?, ?, ?)");
                $user_community_stmt->bind_param("iis", $community_id, $user_id, $role);
                $user_community_stmt->execute();

                // Commit the transaction
                $conn->commit();

                // Redirect to the communities page
                header("Location: communities.php");
                exit();
            } catch (Exception $e) {
                // Rollback on error
                $conn->rollback();
                $error_message = "An error occurred while creating the community: " . $e->getMessage();
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
    <title>Create Community - Syngo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
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
                    <img src="<?= $user['profile_picture'] ?: 'https://public.readdy.ai/ai/img_res/cb6fd3e7b68878d46ddacbc3d3415e6d.jpg' ?>" class="w-8 sm:w-10 h-8 sm:h-10 rounded-full object-cover">
                    <span class="text-xs sm:text-sm font-medium hidden sm:block"><?= htmlspecialchars($user['first_name'] . " " . $user['last_name']) ?></span>
                    <a href="logout.php" class="text-xs sm:text-sm text-red-600 ml-2">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Create Community Section -->
    <div class="max-w-3xl mx-auto px-4 pt-20 pb-8">
        <div class="bg-white p-4 sm:p-6 rounded-lg shadow-md">
            <h2 class="text-xl sm:text-2xl font-semibold mb-4 sm:mb-6">Create a New Community</h2>

            <!-- Display any error messages -->
            <?php if (isset($error_message)) : ?>
                <div class="bg-red-100 text-red-700 p-3 sm:p-4 mb-4 rounded-md text-sm">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <!-- Community Creation Form -->
            <form method="POST" action="create_community.php" enctype="multipart/form-data" class="space-y-4 sm:space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Community Name</label>
                    <input type="text" id="name" name="name" class="mt-1 p-2 sm:p-3 w-full border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary text-sm" required>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Community Description</label>
                    <textarea id="description" name="description" rows="4" class="mt-1 p-2 sm:p-3 w-full border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary text-sm" required></textarea>
                </div>

                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700">Community Image</label>
                    <input type="file" id="image" name="image" class="mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:bg-primary file:text-white hover:file:bg-primary/90" accept="image/*">
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="w-full sm:w-auto !rounded-button px-4 sm:px-6 py-2 bg-primary text-white text-sm sm:text-base hover:bg-primary/90">Create Community</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>