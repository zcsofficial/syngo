<?php
session_start();
require 'config.php'; // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if the community ID and message are set
if (isset($_POST['community_id']) && isset($_POST['message'])) {
    $community_id = $_POST['community_id'];
    $message = $_POST['message'];

    // Validate message
    if (!empty($message)) {
        // Insert message into the chat_messages table
        $insert_message_query = "INSERT INTO chat_messages (community_id, user_id, message) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_message_query);
        $stmt->bind_param("iis", $community_id, $user_id, $message);
        $stmt->execute();

        // Redirect back to the community chat
        header("Location: community_chat.php?community_id=" . $community_id);
        exit();
    } else {
        // Redirect back with an error if the message is empty
        header("Location: community_chat.php?community_id=" . $community_id . "&error=Message cannot be empty.");
        exit();
    }
} else {
    // Redirect back if community ID or message is not set
    header("Location: community_chat.php?error=Invalid request.");
    exit();
}
?>
