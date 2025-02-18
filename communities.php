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

// Fetch communities the user is a part of
$communities_query = "
    SELECT c.community_id, c.name, c.description
    FROM communities c
    JOIN community_members cm ON c.community_id = cm.community_id
    WHERE cm.user_id = ?
";
$communities_stmt = $conn->prepare($communities_query);
$communities_stmt->bind_param("i", $user_id);
$communities_stmt->execute();
$communities_result = $communities_stmt->get_result();

// Fetch all communities to allow joining
$all_communities_query = "SELECT community_id, name, description FROM communities";
$all_communities_result = $conn->query($all_communities_query);

// Handle join community action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['join_community'])) {
    $community_id = $_POST['community_id'];

    // Check if the user is already a member
    $check_membership_query = "SELECT * FROM community_members WHERE community_id = ? AND user_id = ?";
    $check_membership_stmt = $conn->prepare($check_membership_query);
    $check_membership_stmt->bind_param("ii", $community_id, $user_id);
    $check_membership_stmt->execute();
    $check_membership_result = $check_membership_stmt->get_result();

    if ($check_membership_result->num_rows == 0) {
        // Add user to community
        $join_community_query = "INSERT INTO community_members (community_id, user_id, role) VALUES (?, ?, 'member')";
        $join_community_stmt = $conn->prepare($join_community_query);
        $join_community_stmt->bind_param("ii", $community_id, $user_id);
        $join_community_stmt->execute();

        // Redirect back to the communities page
        header("Location: communities.php");
        exit();
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
            <a href="create_community.php" class="!rounded-button px-4 h-10 bg-primary text-white flex items-center gap-2 whitespace-nowrap">Create Community</a>
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

<!-- Communities Section -->
<div class="max-w-7xl mx-auto px-4 mt-20 mb-8">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-semibold mb-6">Your Communities</h2>

        <?php if ($communities_result->num_rows > 0) { ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while ($community = $communities_result->fetch_assoc()) { ?>
                    <div class="bg-gray-50 p-4 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold"><?= htmlspecialchars($community['name']) ?></h3>
                        <p class="text-gray-500 text-sm"><?= htmlspecialchars($community['description']) ?></p>
                        <a href="community_chat.php?community_id=<?= $community['community_id'] ?>" class="mt-4 inline-block text-center w-full py-2 px-4 bg-primary text-white rounded-md hover:bg-green-600">
                            Go to Chat
                        </a>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <p class="text-gray-500">You are not part of any community yet. Join some to participate in the chats!</p>
        <?php } ?>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md mt-8">
        <h2 class="text-2xl font-semibold mb-6">Join New Communities</h2>

        <?php if ($all_communities_result->num_rows > 0) { ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while ($community = $all_communities_result->fetch_assoc()) { ?>
                    <form method="POST" action="communities.php">
                        <input type="hidden" name="community_id" value="<?= $community['community_id'] ?>">
                        <div class="bg-gray-50 p-4 rounded-lg shadow-md">
                            <h3 class="text-xl font-semibold"><?= htmlspecialchars($community['name']) ?></h3>
                            <p class="text-gray-500 text-sm"><?= htmlspecialchars($community['description']) ?></p>
                            <button type="submit" name="join_community" class="mt-4 inline-block text-center w-full py-2 px-4 bg-primary text-white rounded-md hover:bg-green-600">
                                Join Community
                            </button>
                        </div>
                    </form>
                <?php } ?>
            </div>
        <?php } else { ?>
            <p class="text-gray-500">No communities available to join at the moment.</p>
        <?php } ?>
    </div>
</div>

</body>
</html>
