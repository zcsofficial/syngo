<?php
session_start();
require 'config.php'; // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

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
        if ($image['error'] === 0) {
            $target_dir = "uploads/communities/"; // Define the directory to store images
            $target_file = $target_dir . basename($image["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if the file is a valid image (you can add more checks as needed)
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
        } else {
            $community_image = null; // If no image is uploaded
        }

        if (!isset($error_message)) {
            // Insert the new community into the database
            $stmt = $conn->prepare("INSERT INTO communities (name, description, created_by, image) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssis", $community_name, $community_description, $user_id, $community_image);

            if ($stmt->execute()) {
                // Insert the user into the new community
                $community_id = $stmt->insert_id;
                $user_community_stmt = $conn->prepare("INSERT INTO user_communities (user_id, community_id) VALUES (?, ?)");
                $user_community_stmt->bind_param("ii", $user_id, $community_id);
                $user_community_stmt->execute();

                // Redirect to the newly created community page
                header("Location: communities.php");
                exit();
            } else {
                $error_message = "An error occurred while creating the community.";
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
    <title>Communities - Syngo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
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

<!-- Create Community Section -->
<div class="max-w-7xl mx-auto px-4 mt-20 mb-8">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-semibold mb-6">Create a New Community</h2>

        <!-- Display any error messages -->
        <?php if (isset($error_message)) { ?>
            <div class="bg-red-100 text-red-700 p-4 mb-4 rounded-md">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php } ?>

        <!-- Community Creation Form -->
        <form method="POST" action="create_community.php" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Community Name</label>
                <input type="text" id="name" name="name" class="mt-1 p-2 w-full border border-gray-300 rounded-md" required>
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700">Community Description</label>
                <textarea id="description" name="description" class="mt-1 p-2 w-full border border-gray-300 rounded-md" required></textarea>
            </div>

            <div class="mb-4">
                <label for="image" class="block text-sm font-medium text-gray-700">Community Image</label>
                <input type="file" id="image" name="image" class="mt-1 p-2 w-full border border-gray-300 rounded-md">
            </div>

            <button type="submit" class="w-full py-2 bg-primary text-white rounded-md hover:bg-green-600">
                Create Community
            </button>
        </form>
    </div>
</div>

</body>
</html>
