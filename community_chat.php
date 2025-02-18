<?php
session_start();
require 'config.php'; // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$community_id = $_GET['community_id']; // Assuming community ID is passed as a URL parameter

// Fetch community details
$community_query = $conn->prepare("SELECT * FROM communities WHERE community_id = ?");
$community_query->bind_param("i", $community_id);
$community_query->execute();
$community_result = $community_query->get_result();
$community = $community_result->fetch_assoc();

// Check if user is part of the community
$check_member_query = $conn->prepare("SELECT * FROM community_members WHERE community_id = ? AND user_id = ?");
$check_member_query->bind_param("ii", $community_id, $user_id);
$check_member_query->execute();
$check_member_result = $check_member_query->get_result();

if ($check_member_result->num_rows == 0) {
    // User is not a member of this community
    header("Location: communities.php");
    exit();
}

// Fetch chat messages
$messages_query = "SELECT chat_messages.*, users.first_name, users.last_name, users.profile_picture 
                   FROM chat_messages
                   JOIN users ON chat_messages.user_id = users.user_id
                   WHERE chat_messages.community_id = ? ORDER BY chat_messages.created_at ASC";
$messages_stmt = $conn->prepare($messages_query);
$messages_stmt->bind_param("i", $community_id);
$messages_stmt->execute();
$messages_result = $messages_stmt->get_result();

// Fetch community members
$members_query = "SELECT users.user_id, users.first_name, users.last_name, users.profile_picture, community_members.role 
                  FROM community_members 
                  JOIN users ON community_members.user_id = users.user_id 
                  WHERE community_members.community_id = ?";
$members_stmt = $conn->prepare($members_query);
$members_stmt->bind_param("i", $community_id);
$members_stmt->execute();
$members_result = $members_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syngo - Community Chat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
    <style>
        /* Custom styles for chat */
        .chat-box {
            max-height: 400px;
            overflow-y: auto;
        }
        .chat-message {
            margin-bottom: 16px;
        }
        .chat-message img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
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

<!-- Custom Navbar -->
<nav class="fixed top-0 left-0 right-0 h-16 bg-white shadow-sm z-50">
    <div class="max-w-7xl mx-auto px-4 h-full flex items-center justify-between">
        <div class="flex items-center gap-8">
            <a href="index.php" class="text-2xl font-['Pacifico'] text-primary">Syngo</a>
        </div>
        <div class="flex items-center gap-4">
            <a href="communities.php" class="!rounded-button px-4 h-10 bg-primary text-white flex items-center gap-2">Back to Community</a>
        </div>
    </div>
</nav>

<!-- Chat and Community Management Section -->
<div class="max-w-7xl mx-auto px-4 mt-20 mb-8 grid grid-cols-12 gap-6">
    <!-- Chat Section -->
    <div class="col-span-12 sm:col-span-9 bg-white rounded-lg p-6 shadow-sm">
        <h2 class="text-2xl font-semibold mb-4">Community Chat - <?= htmlspecialchars($community['name']) ?></h2>
        
        <div class="chat-box bg-gray-50 p-4 rounded-lg mb-4">
            <?php while ($message = $messages_result->fetch_assoc()) : ?>
                <div class="chat-message flex gap-4">
                    <img src="<?= $message['profile_picture'] ?: 'default-avatar.jpg' ?>" class="w-9 h-9 object-cover">
                    <div>
                        <p><span class="font-semibold"><?= htmlspecialchars($message['first_name']) ?> <?= htmlspecialchars($message['last_name']) ?></span>: <?= htmlspecialchars($message['message']) ?></p>
                        <p class="text-sm text-gray-500"><?= date('M d, Y h:i A', strtotime($message['created_at'])) ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Chat Input Form -->
        <form action="send_message.php" method="POST" class="flex gap-4">
            <input type="hidden" name="community_id" value="<?= $community_id ?>">
            <input type="text" name="message" class="w-full p-2 border rounded-lg" placeholder="Type your message here..." required>
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg">Send</button>
        </form>
    </div>

    <!-- Members Management Section -->
    <div class="col-span-12 sm:col-span-3 bg-white rounded-lg p-6 shadow-sm">
        <h3 class="text-xl font-semibold mb-4">Community Members</h3>
        <div class="space-y-4">
            <?php while ($member = $members_result->fetch_assoc()) : ?>
                <div class="flex items-center gap-3">
                    <img src="<?= $member['profile_picture'] ?>" class="w-10 h-10 rounded-full object-cover">
                    <div class="flex-grow">
                        <div class="font-medium"><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></div>
                        <div class="text-sm text-gray-500"><?= $member['role'] ?></div>
                    </div>
                    <?php if ($member['role'] != 'admin') : ?>
                        <a href="ban_member.php?community_id=<?= $community_id ?>&user_id=<?= $member['user_id'] ?>" class="text-red-600 text-sm">Ban</a>
                        <a href="remove_member.php?community_id=<?= $community_id ?>&user_id=<?= $member['user_id'] ?>" class="text-red-600 text-sm">Remove</a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

</body>
</html>
