<?php
session_start();
require 'config.php'; // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data for navbar and messages
$user_query = $conn->prepare("SELECT first_name, last_name, profile_picture FROM users WHERE user_id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

// Get the trip ID from the URL
if (!isset($_GET['trip_id'])) {
    header('Location: dashboard.php');
    exit();
}

$trip_id = (int)$_GET['trip_id'];

// Fetch trip and group details
$group_query = $conn->prepare("SELECT tg.group_id, t.title FROM trip_groups tg JOIN trips t ON tg.trip_id = t.trip_id WHERE tg.trip_id = ?");
$group_query->bind_param("i", $trip_id);
$group_query->execute();
$group_result = $group_query->get_result();

if ($group_result->num_rows === 0) {
    header('Location: dashboard.php');
    exit();
}

$group = $group_result->fetch_assoc();
$group_id = $group['group_id'];
$trip_title = $group['title'];

// Check if the user is a member of the trip group
$member_query = $conn->prepare("SELECT * FROM trip_group_members WHERE group_id = ? AND user_id = ?");
$member_query->bind_param("ii", $group_id, $user_id);
$member_query->execute();
$member_result = $member_query->get_result();

if ($member_result->num_rows === 0) {
    header('Location: trip_details.php?trip_id=' . $trip_id);
    exit();
}

// Fetch group members for the modal
$members_query = $conn->prepare("
    SELECT u.user_id, u.first_name, u.last_name, u.profile_picture 
    FROM trip_group_members tgm 
    JOIN users u ON tgm.user_id = u.user_id 
    WHERE tgm.group_id = ?
");
$members_query->bind_param("i", $group_id);
$members_query->execute();
$members_result = $members_query->get_result();

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $insert_query = $conn->prepare("INSERT INTO chat_messages (group_id, user_id, message) VALUES (?, ?, ?)");
        $insert_query->bind_param("iis", $group_id, $user_id, $message);
        if ($insert_query->execute()) {
            header("Location: chat_group.php?trip_id=" . $trip_id);
            exit();
        } else {
            $error_message = "Failed to send message. Please try again.";
        }
    }
}

// Fetch chat messages for this trip group
$messages_query = $conn->prepare("
    SELECT cm.message_id, cm.message, cm.created_at, u.user_id, u.first_name, u.last_name, u.profile_picture 
    FROM chat_messages cm 
    JOIN users u ON cm.user_id = u.user_id 
    WHERE cm.group_id = ? 
    ORDER BY cm.created_at ASC
");
$messages_query->bind_param("i", $group_id);
$messages_query->execute();
$messages_result = $messages_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - <?= htmlspecialchars($trip_title) ?> - Syngo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        .chat-box { display: flex; flex-direction: column; height: 70vh; }
        .chat-messages { flex: 1; overflow-y: auto; }
        .chat-input { position: sticky; bottom: 0; background: white; padding: 1rem; border-top: 1px solid #E5E7EB; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #22C55E; border-radius: 3px; }
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

    <!-- Chat Container -->
    <div class="max-w-4xl mx-auto px-4 pt-20 pb-8">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="p-4 sm:p-6 flex flex-col h-full">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl sm:text-2xl font-semibold text-gray-800">Group Chat - <?= htmlspecialchars($trip_title) ?></h2>
                    <button onclick="document.getElementById('membersModal').classList.remove('hidden')" class="!rounded-button px-3 sm:px-4 py-2 bg-primary text-white text-sm hover:bg-primary/90">Group Members</button>
                </div>

                <!-- Error Message -->
                <?php if (isset($error_message)) : ?>
                    <div class="bg-red-100 text-red-700 p-3 sm:p-4 rounded-md mb-4 text-sm">
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>

                <!-- Chat Box -->
                <div class="chat-box flex-1">
                    <div class="chat-messages custom-scrollbar p-4 bg-gray-50 rounded-md">
                        <?php while ($message = $messages_result->fetch_assoc()) : ?>
                            <div class="flex items-start gap-3 mb-4 <?= $message['user_id'] === $user_id ? 'justify-end' : '' ?>">
                                <?php if ($message['user_id'] !== $user_id) : ?>
                                    <img src="<?= $message['profile_picture'] ?: 'https://public.readdy.ai/ai/img_res/cb6fd3e7b68878d46ddacbc3d3415e6d.jpg' ?>" class="w-8 h-8 rounded-full object-cover">
                                <?php endif; ?>
                                <div class="max-w-[70%]">
                                    <p class="text-xs sm:text-sm font-medium <?= $message['user_id'] === $user_id ? 'text-right' : '' ?>">
                                        <?= htmlspecialchars($message['first_name'] . " " . $message['last_name']) ?>
                                    </p>
                                    <div class="bg-<?= $message['user_id'] === $user_id ? 'primary' : 'secondary' ?> text-<?= $message['user_id'] === $user_id ? 'white' : 'gray-800' ?> p-3 rounded-md text-xs sm:text-sm">
                                        <?= htmlspecialchars($message['message']) ?>
                                    </div>
                                    <p class="text-xs text-gray-500 <?= $message['user_id'] === $user_id ? 'text-right' : '' ?>">
                                        <?= date('M d, Y h:i A', strtotime($message['created_at'])) ?>
                                    </p>
                                </div>
                                <?php if ($message['user_id'] === $user_id) : ?>
                                    <img src="<?= $message['profile_picture'] ?: 'https://public.readdy.ai/ai/img_res/cb6fd3e7b68878d46ddacbc3d3415e6d.jpg' ?>" class="w-8 h-8 rounded-full object-cover">
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <div class="chat-input flex items-center gap-2">
                        <textarea name="message" rows="1" class="w-full p-2 sm:p-3 border rounded-md focus:ring-2 focus:ring-primary focus:border-primary text-sm resize-none" placeholder="Type your message..." required form="chatForm"></textarea>
                        <button type="submit" form="chatForm" class="!rounded-button px-4 sm:px-6 py-2 bg-primary text-white text-sm sm:text-base hover:bg-primary/90 whitespace-nowrap">Send</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Members Modal -->
    <div id="membersModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden z-50">
        <div class="bg-white p-4 sm:p-6 rounded-lg max-w-md w-full max-h-[80vh] overflow-y-auto custom-scrollbar">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg sm:text-xl font-semibold text-gray-800">Group Members</h3>
                <button onclick="document.getElementById('membersModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            <ul class="space-y-3">
                <?php while ($member = $members_result->fetch_assoc()) : ?>
                    <li class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-md">
                        <a href="member.php?user_id=<?= $member['user_id'] ?>" class="flex items-center gap-3">
                            <img src="<?= $member['profile_picture'] ?: 'https://public.readdy.ai/ai/img_res/cb6fd3e7b68878d46ddacbc3d3415e6d.jpg' ?>" class="w-8 h-8 rounded-full object-cover">
                            <span class="text-xs sm:text-sm"><?= htmlspecialchars($member['first_name'] . " " . $member['last_name']) ?></span>
                        </a>
                        <button class="text-xs text-primary hover:text-primary/80" onclick="alert('Private chat feature coming soon!')">Chat</button>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>

    <!-- Hidden Form for Chat Submission -->
    <form id="chatForm" method="POST" action="chat_group.php?trip_id=<?= $trip_id ?>" class="hidden"></form>

    <script>
        // Auto-scroll to the bottom of the chat messages
        document.addEventListener("DOMContentLoaded", function() {
            const chatMessages = document.querySelector('.chat-messages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        });
    </script>
</body>
</html>