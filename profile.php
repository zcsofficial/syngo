<?php
session_start();
require_once 'config.php'; // Include database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch recent trips (optional)
$queryTrips = "SELECT * FROM trips WHERE created_by = ? ORDER BY created_at DESC LIMIT 3";
$stmtTrips = $conn->prepare($queryTrips);
$stmtTrips->bind_param('i', $user_id);
$stmtTrips->execute();
$trips = $stmtTrips->get_result();

// Handle form submission for profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = $_POST['bio'];
    $location = $_POST['location'];
    $profile_picture = $user['profile_picture']; // Keep existing picture if no update

    // Check if a new profile picture was uploaded
    if ($_FILES['profile_picture']['error'] === 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES['profile_picture']['name']);
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $profile_picture = $target_file;
        }
    }

    // Update profile in the database
    $updateQuery = "UPDATE users SET bio = ?, location = ?, profile_picture = ? WHERE user_id = ?";
    $stmtUpdate = $conn->prepare($updateQuery);
    $stmtUpdate->bind_param('sssi', $bio, $location, $profile_picture, $user_id);
    if ($stmtUpdate->execute()) {
        // Redirect to the updated profile page
        header('Location: profile.php');
        exit();
    } else {
        $error = "Failed to update profile.";
    }
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
<body class="bg-white min-h-screen">
    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 h-16 bg-white shadow-sm z-50">
    <div class="max-w-7xl mx-auto px-4 h-full flex items-center justify-between">
        <div class="flex items-center gap-8">
            <a href="index.php" class="text-2xl font-['Pacifico'] text-primary">Syngo</a>
        </div>
        <div class="flex items-center gap-4">
            <!-- Dashboard link -->
            <a href="dashboard.php" class="  text-green">Dashboard</a>
            <!-- Create Trip link -->
            
            <div class="flex items-center gap-3 cursor-pointer">
                <img src="<?= $user['profile_picture'] ?: 'https://public.readdy.ai/ai/img_res/cb6fd3e7b68878d46ddacbc3d3415e6d.jpg' ?>" class="w-10 h-10 rounded-full object-cover">
                <span class="text-sm font-medium"><?= $user['first_name'] . ' ' . $user['last_name'] ?></span>
            </div>
        </div>
    </div>
</nav>
    

    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="relative h-48 bg-primary/10">
                <div class="absolute -bottom-16 left-1/2 -translate-x-1/2">
                    <div class="relative">
                        <img src="<?= $user['profile_picture'] ?>" 
                             class="w-32 h-32 rounded-full border-4 border-white object-cover"/>
                        <div class="absolute bottom-0 right-0 bg-primary text-white p-1 rounded-full">
                            <i class="ri-checkbox-circle-fill ri-lg"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-20 px-8 pb-8">
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-900"><?= $user['first_name'] ?> <?= $user['last_name'] ?></h1>
                    <div class="flex items-center justify-center mt-2 text-gray-600">
                        <i class="ri-map-pin-line ri-lg"></i>
                        <span class="ml-2"><?= $user['location'] ?? 'Location not set' ?></span>
                    </div>
                    <div class="flex items-center justify-center mt-2">
                        <div class="flex text-yellow-400">
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-fill"></i>
                            <i class="ri-star-half-fill"></i>
                        </div>
                        <span class="ml-2 text-gray-600">(4.8)</span>
                    </div>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h2 class="text-lg font-semibold mb-4">About Me</h2>
                            <textarea name="bio" class="w-full p-4 border rounded-lg" rows="4"><?= $user['bio'] ?? '' ?></textarea>
                        </div>

                        <div>
                            <h2 class="text-lg font-semibold mb-4">Location</h2>
                            <input type="text" name="location" value="<?= $user['location'] ?? '' ?>" class="w-full p-4 border rounded-lg"/>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h2 class="text-lg font-semibold mb-4">Profile Picture</h2>
                        <input type="file" name="profile_picture" class="w-full p-4 border rounded-lg"/>
                    </div>

                    <div class="mt-8 flex justify-center space-x-4">
                        <button type="submit" class="bg-primary text-white px-6 py-2 rounded-button flex items-center">
                            <i class="ri-save-line mr-2"></i>
                            Save Changes
                        </button>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="mt-4 text-red-500"><?= $error ?></div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <script>
        const saveButton = document.getElementById('saveButton');
        const heartIcon = document.getElementById('heartIcon');
        let saved = false;

        saveButton.addEventListener('click', () => {
            saved = !saved;
            if(saved) {
                heartIcon.className = 'ri-heart-fill mr-2 text-primary';
                saveButton.classList.add('border-primary', 'text-primary');
            } else {
                heartIcon.className = 'ri-heart-line mr-2';
                saveButton.classList.remove('border-primary', 'text-primary');
            }
        });
    </script>
</body>
</html>
