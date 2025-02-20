<?php
session_start();
require 'config.php'; // Database connection

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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

// Fetch statistics
$total_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$total_trips = $conn->query("SELECT COUNT(*) as total FROM trips")->fetch_assoc()['total'];
$total_communities = $conn->query("SELECT COUNT(*) as total FROM communities")->fetch_assoc()['total'];
$total_messages = $conn->query("SELECT COUNT(*) as total FROM chat_messages")->fetch_assoc()['total'];

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Trip CRUD
    if (isset($_POST['trip_action'])) {
        $trip_id = (int)$_POST['trip_id'];
        if ($_POST['trip_action'] === 'update') {
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $stmt = $conn->prepare("UPDATE trips SET title = ?, description = ? WHERE trip_id = ?");
            $stmt->bind_param("ssi", $title, $description, $trip_id);
            $stmt->execute();
        } elseif ($_POST['trip_action'] === 'delete') {
            $stmt = $conn->prepare("DELETE FROM trips WHERE trip_id = ?");
            $stmt->bind_param("i", $trip_id);
            $stmt->execute();
        }
    }

    // Handle Community CRUD
    if (isset($_POST['community_action'])) {
        $community_id = (int)$_POST['community_id'];
        if ($_POST['community_action'] === 'update') {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $stmt = $conn->prepare("UPDATE communities SET name = ?, description = ? WHERE community_id = ?");
            $stmt->bind_param("ssi", $name, $description, $community_id);
            $stmt->execute();
        } elseif ($_POST['community_action'] === 'delete') {
            $stmt = $conn->prepare("DELETE FROM communities WHERE community_id = ?");
            $stmt->bind_param("i", $community_id);
            $stmt->execute();
        } elseif ($_POST['community_action'] === 'remove_member') {
            $member_id = (int)$_POST['member_id'];
            $stmt = $conn->prepare("DELETE FROM community_members WHERE community_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $community_id, $member_id);
            $stmt->execute();
        }
    }

    // Handle Group CRUD
    if (isset($_POST['group_action'])) {
        $group_id = (int)$_POST['group_id'];
        if ($_POST['group_action'] === 'update') {
            $name = trim($_POST['name']);
            $stmt = $conn->prepare("UPDATE trip_groups SET name = ? WHERE group_id = ?");
            $stmt->bind_param("si", $name, $group_id);
            $stmt->execute();
        } elseif ($_POST['group_action'] === 'delete') {
            $stmt = $conn->prepare("DELETE FROM trip_groups WHERE group_id = ?");
            $stmt->bind_param("i", $group_id);
            $stmt->execute();
        } elseif ($_POST['group_action'] === 'remove_member') {
            $member_id = (int)$_POST['member_id'];
            $stmt = $conn->prepare("DELETE FROM trip_group_members WHERE group_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $group_id, $member_id);
            $stmt->execute();
        }
    }

    // Handle Chat Management
    if (isset($_POST['chat_action']) && $_POST['chat_action'] === 'delete_message') {
        $message_id = (int)$_POST['message_id'];
        $stmt = $conn->prepare("DELETE FROM chat_messages WHERE message_id = ?");
        $stmt->bind_param("i", $message_id);
        $stmt->execute();
    }

    // Handle User Role Update
    if (isset($_POST['user_action']) && $_POST['user_action'] === 'update_role') {
        $user_id_to_update = (int)$_POST['user_id'];
        $new_role = $_POST['role'];
        if (in_array($new_role, ['user', 'admin']) && $user_id_to_update != $user_id) { // Prevent self-role change
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
            $stmt->bind_param("si", $new_role, $user_id_to_update);
            $stmt->execute();
        }
    }

    header("Location: admin.php");
    exit();
}

// Fetch all trips, communities, groups, and users
$trips = $conn->query("SELECT * FROM trips ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$communities = $conn->query("SELECT * FROM communities ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$groups = $conn->query("SELECT tg.*, t.title AS trip_title FROM trip_groups tg JOIN trips t ON tg.trip_id = t.trip_id ORDER BY tg.created_at DESC")->fetch_all(MYSQLI_ASSOC);
$users = $conn->query("SELECT user_id, first_name, last_name, email, role FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Syngo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
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
                    <input type="text" id="search-bar" placeholder="Search..." class="w-40 sm:w-64 md:w-[400px] h-10 pl-10 pr-4 text-sm rounded-full border border-gray-200 focus:outline-none focus:border-primary">
                    <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            <div class="flex items-center gap-2 sm:gap-4">
                <a href="dashboard.php" class="!rounded-button px-3 sm:px-4 h-9 sm:h-10 bg-primary text-white text-sm flex items-center gap-2 whitespace-nowrap">Dashboard</a>
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

    <!-- Admin Dashboard -->
    <div class="max-w-7xl mx-auto px-4 pt-20 pb-8">
        <h1 class="text-2xl sm:text-3xl font-semibold text-gray-800 mb-6">Admin Dashboard</h1>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                <h3 class="text-sm font-medium text-gray-600">Total Users</h3>
                <p class="text-2xl font-semibold text-gray-800"><?= $total_users ?></p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                <h3 class="text-sm font-medium text-gray-600">Total Trips</h3>
                <p class="text-2xl font-semibold text-gray-800"><?= $total_trips ?></p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                <h3 class="text-sm font-medium text-gray-600">Total Communities</h3>
                <p class="text-2xl font-semibold text-gray-800"><?= $total_communities ?></p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                <h3 class="text-sm font-medium text-gray-600">Total Messages</h3>
                <p class="text-2xl font-semibold text-gray-800"><?= $total_messages ?></p>
            </div>
        </div>

        <!-- Usage Graph -->
        <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">App Usage Statistics</h2>
            <canvas id="usageChart" height="100"></canvas>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-gray-200 mb-6">
            <button class="tab-button px-4 py-2 text-sm font-medium text-gray-600 hover:text-primary border-b-2 border-transparent hover:border-primary" onclick="showTab('overview')">Overview</button>
            <button class="tab-button px-4 py-2 text-sm font-medium text-gray-600 hover:text-primary border-b-2 border-transparent hover:border-primary" onclick="showTab('trips')">Trips</button>
            <button class="tab-button px-4 py-2 text-sm font-medium text-gray-600 hover:text-primary border-b-2 border-transparent hover:border-primary" onclick="showTab('communities')">Communities</button>
            <button class="tab-button px-4 py-2 text-sm font-medium text-gray-600 hover:text-primary border-b-2 border-transparent hover:border-primary" onclick="showTab('groups')">Groups</button>
            <button class="tab-button px-4 py-2 text-sm font-medium text-gray-600 hover:text-primary border-b-2 border-transparent hover:border-primary" onclick="showTab('users')">Users</button>
        </div>

        <!-- Tab Content -->
        <div id="overview" class="tab-content active">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Dashboard Overview</h2>
            <p class="text-sm text-gray-600">Manage trips, communities, groups, and users from this dashboard.</p>
        </div>

        <div id="trips" class="tab-content">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Manage Trips</h2>
            <div class="space-y-4 max-h-[600px] overflow-y-auto custom-scrollbar">
                <?php foreach ($trips as $trip) : ?>
                    <div class="bg-white p-4 rounded-lg shadow-sm">
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="trip_id" value="<?= $trip['trip_id'] ?>">
                            <div class="flex flex-col sm:flex-row gap-4">
                                <input type="text" name="title" value="<?= htmlspecialchars($trip['title']) ?>" class="w-full p-2 border rounded-md text-sm">
                                <textarea name="description" class="w-full p-2 border rounded-md text-sm" rows="2"><?= htmlspecialchars($trip['description']) ?></textarea>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" name="trip_action" value="update" class="!rounded-button px-4 py-2 bg-primary text-white text-sm hover:bg-primary/90">Update</button>
                                <button type="submit" name="trip_action" value="delete" class="!rounded-button px-4 py-2 bg-red-600 text-white text-sm hover:bg-red-700">Delete</button>
                            </div>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="communities" class="tab-content">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Manage Communities</h2>
            <div class="space-y-4 max-h-[600px] overflow-y-auto custom-scrollbar">
                <?php foreach ($communities as $community) : ?>
                    <div class="bg-white p-4 rounded-lg shadow-sm">
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="community_id" value="<?= $community['community_id'] ?>">
                            <div class="flex flex-col sm:flex-row gap-4">
                                <input type="text" name="name" value="<?= htmlspecialchars($community['name']) ?>" class="w-full p-2 border rounded-md text-sm">
                                <textarea name="description" class="w-full p-2 border rounded-md text-sm" rows="2"><?= htmlspecialchars($community['description']) ?></textarea>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" name="community_action" value="update" class="!rounded-button px-4 py-2 bg-primary text-white text-sm hover:bg-primary/90">Update</button>
                                <button type="submit" name="community_action" value="delete" class="!rounded-button px-4 py-2 bg-red-600 text-white text-sm hover:bg-red-700">Delete</button>
                            </div>
                        </form>
                        <div class="mt-4">
                            <h3 class="text-sm font-semibold text-gray-700 mb-2">Members</h3>
                            <?php
                            $members_query = $conn->prepare("SELECT u.user_id, u.first_name, u.last_name FROM community_members cm JOIN users u ON cm.user_id = u.user_id WHERE cm.community_id = ?");
                            $members_query->bind_param("i", $community['community_id']);
                            $members_query->execute();
                            $members_result = $members_query->get_result();
                            while ($member = $members_result->fetch_assoc()) :
                            ?>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded-md mb-2">
                                    <span class="text-xs"><?= htmlspecialchars($member['first_name'] . " " . $member['last_name']) ?></span>
                                    <form method="POST">
                                        <input type="hidden" name="community_id" value="<?= $community['community_id'] ?>">
                                        <input type="hidden" name="member_id" value="<?= $member['user_id'] ?>">
                                        <button type="submit" name="community_action" value="remove_member" class="text-xs text-red-600 hover:underline">Remove</button>
                                    </form>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-sm font-semibold text-gray-700 mb-2">Chats</h3>
                            <?php
                            $chats_query = $conn->prepare("SELECT cm.message_id, cm.message, u.first_name, u.last_name FROM chat_messages cm JOIN users u ON cm.user_id = u.user_id WHERE cm.community_id = ? LIMIT 5");
                            $chats_query->bind_param("i", $community['community_id']);
                            $chats_query->execute();
                            $chats_result = $chats_query->get_result();
                            while ($chat = $chats_result->fetch_assoc()) :
                            ?>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded-md mb-2">
                                    <span class="text-xs"><?= htmlspecialchars($chat['first_name'] . " " . $chat['last_name']) . ": " . $chat['message'] ?></span>
                                    <form method="POST">
                                        <input type="hidden" name="message_id" value="<?= $chat['message_id'] ?>">
                                        <button type="submit" name="chat_action" value="delete_message" class="text-xs text-red-600 hover:underline">Delete</button>
                                    </form>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="groups" class="tab-content">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Manage Groups</h2>
            <div class="space-y-4 max-h-[600px] overflow-y-auto custom-scrollbar">
                <?php foreach ($groups as $group) : ?>
                    <div class="bg-white p-4 rounded-lg shadow-sm">
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="group_id" value="<?= $group['group_id'] ?>">
                            <div class="flex flex-col sm:flex-row gap-4">
                                <input type="text" name="name" value="<?= htmlspecialchars($group['name']) ?>" class="w-full p-2 border rounded-md text-sm">
                                <span class="text-xs text-gray-600">Trip: <?= htmlspecialchars($group['trip_title']) ?></span>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" name="group_action" value="update" class="!rounded-button px-4 py-2 bg-primary text-white text-sm hover:bg-primary/90">Update</button>
                                <button type="submit" name="group_action" value="delete" class="!rounded-button px-4 py-2 bg-red-600 text-white text-sm hover:bg-red-700">Delete</button>
                            </div>
                        </form>
                        <div class="mt-4">
                            <h3 class="text-sm font-semibold text-gray-700 mb-2">Members</h3>
                            <?php
                            $members_query = $conn->prepare("SELECT u.user_id, u.first_name, u.last_name FROM trip_group_members tgm JOIN users u ON tgm.user_id = u.user_id WHERE tgm.group_id = ?");
                            $members_query->bind_param("i", $group['group_id']);
                            $members_query->execute();
                            $members_result = $members_query->get_result();
                            while ($member = $members_result->fetch_assoc()) :
                            ?>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded-md mb-2">
                                    <span class="text-xs"><?= htmlspecialchars($member['first_name'] . " " . $member['last_name']) ?></span>
                                    <form method="POST">
                                        <input type="hidden" name="group_id" value="<?= $group['group_id'] ?>">
                                        <input type="hidden" name="member_id" value="<?= $member['user_id'] ?>">
                                        <button type="submit" name="group_action" value="remove_member" class="text-xs text-red-600 hover:underline">Remove</button>
                                    </form>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="users" class="tab-content">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Manage Users</h2>
            <div class="space-y-4 max-h-[600px] overflow-y-auto custom-scrollbar">
                <?php foreach ($users as $u) : ?>
                    <div class="bg-white p-4 rounded-lg shadow-sm">
                        <form method="POST" class="flex flex-col sm:flex-row items-center gap-4">
                            <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                            <span class="text-sm font-medium"><?= htmlspecialchars($u['first_name'] . " " . $u['last_name']) ?></span>
                            <span class="text-sm text-gray-600"><?= htmlspecialchars($u['email']) ?></span>
                            <select name="role" class="p-2 border rounded-md text-sm">
                                <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                            <button type="submit" name="user_action" value="update_role" class="!rounded-button px-4 py-2 bg-primary text-white text-sm hover:bg-primary/90 <?= $u['user_id'] == $user_id ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= $u['user_id'] == $user_id ? 'disabled' : '' ?>>Update Role</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('text-primary', 'border-primary');
                button.classList.add('text-gray-600', 'border-transparent');
            });
            document.getElementById(tabId).classList.add('active');
            document.querySelector(`button[onclick="showTab('${tabId}')"]`).classList.add('text-primary', 'border-primary');
            document.querySelector(`button[onclick="showTab('${tabId}')"]`).classList.remove('text-gray-600', 'border-transparent');
        }

        document.addEventListener("DOMContentLoaded", () => {
            showTab('overview');
            const ctx = document.getElementById('usageChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Users', 'Trips', 'Communities', 'Messages'],
                    datasets: [{
                        label: 'App Usage',
                        data: [<?= $total_users ?>, <?= $total_trips ?>, <?= $total_communities ?>, <?= $total_messages ?>],
                        backgroundColor: ['#22C55E', '#3B82F6', '#10B981', '#F59E0B'],
                        borderColor: ['#16A34A', '#2563EB', '#059669', '#D97706'],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: { beginAtZero: true }
                    },
                    plugins: {
                        legend: { display: false },
                        title: { display: true, text: 'App Usage Metrics' }
                    }
                }
            });
        });
    </script>
</body>
</html>