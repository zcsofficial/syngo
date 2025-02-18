<?php
session_start();
require 'config.php'; // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if the user is an admin and if valid community_id and user_to_remove_id are provided
if (isset($_GET['community_id'], $_GET['user_to_remove_id'])) {
    $community_id = $_GET['community_id'];
    $user_to_remove_id = $_GET['user_to_remove_id'];

    // Check if the logged-in user is the admin of the community
    $check_admin_query = "
        SELECT role 
        FROM community_members 
        WHERE community_id = ? AND user_id = ? 
    ";
    $stmt = $conn->prepare($check_admin_query);
    $stmt->bind_param("ii", $community_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin_data = $result->fetch_assoc();

    if ($admin_data && $admin_data['role'] === 'admin') {
        // Remove the user from the community
        $remove_query = "DELETE FROM community_members WHERE community_id = ? AND user_id = ?";
        $stmt = $conn->prepare($remove_query);
        $stmt->bind_param("ii", $community_id, $user_to_remove_id);
        $stmt->execute();

        // Redirect back to the community chat
        header("Location: community_chat.php?community_id=" . $community_id);
        exit();
    } else {
        // If the user is not an admin, show an error message
        header("Location: community_chat.php?community_id=" . $community_id . "&error=You are not an admin.");
        exit();
    }
} else {
    // Redirect back with an error if parameters are missing
    header("Location: community_chat.php?error=Invalid request.");
    exit();
}
?>
