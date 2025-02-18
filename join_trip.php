<?php
// Start the session and include necessary files
session_start();

// Include the database connection
include('config.php'); // Make sure this path is correct and includes the $conn connection

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get the trip ID from the URL
if (isset($_GET['trip_id'])) {
    $trip_id = (int)$_GET['trip_id'];

    // Check if the user is already a member of the trip
    $checkQuery = "SELECT * FROM trip_members WHERE trip_id = ? AND user_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $trip_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // If the user is already a member, redirect to the trip details page
    if ($result->num_rows > 0) {
        header('Location: trip_details.php?trip_id=' . $trip_id);
        exit;
    }

    // Fetch the trip details
    $tripQuery = "SELECT * FROM trips WHERE trip_id = ?";
    $stmt = $conn->prepare($tripQuery);
    $stmt->bind_param("i", $trip_id);
    $stmt->execute();
    $tripResult = $stmt->get_result();

    // If the trip exists
    if ($tripResult->num_rows > 0) {
        $trip = $tripResult->fetch_assoc();
        $group_size = $trip['group_size'];

        // Get the current number of members in the trip
        $membersQuery = "SELECT COUNT(*) AS total_members FROM trip_members WHERE trip_id = ?";
        $stmt = $conn->prepare($membersQuery);
        $stmt->bind_param("i", $trip_id);
        $stmt->execute();
        $membersResult = $stmt->get_result();
        $members = $membersResult->fetch_assoc();

        // If there is space for more members, allow the user to join
        if ($members['total_members'] < $group_size) {
            // Insert the user into the trip_members table
            $insertQuery = "INSERT INTO trip_members (trip_id, user_id) VALUES (?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("ii", $trip_id, $user_id);
            if ($stmt->execute()) {
                // Insert into activity log
                $activityQuery = "INSERT INTO activity_log (user_id, activity_type, activity_details) VALUES (?, 'Joined Trip', ?)";
                $activityStmt = $conn->prepare($activityQuery);
                $activityStmt->bind_param("is", $user_id, $trip['title']);
                $activityStmt->execute();

                // Redirect to trip details page after joining
                header('Location: trip_details.php?trip_id=' . $trip_id);
                exit;
            } else {
                $error = "Unable to join the trip at the moment. Please try again.";
            }
        } else {
            $error = "This trip is already full.";
        }
    } else {
        header('Location: index.php');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Trip</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="p-8">
                <h2 class="text-2xl font-semibold text-gray-800">Join Trip</h2>
                <?php if (isset($error)) { ?>
                    <div class="bg-red-200 text-red-800 p-4 rounded mb-4">
                        <?= $error ?>
                    </div>
                <?php } ?>

                <div class="mt-6">
                    <h3 class="text-xl font-semibold"><?= $trip['title'] ?></h3>
                    <p class="text-gray-600"><?= $trip['description'] ?></p>
                    <div class="mt-4">
                        <p><strong>Destination:</strong> <?= $trip['destination'] ?></p>
                        <p><strong>Start Date:</strong> <?= $trip['start_date'] ?></p>
                        <p><strong>End Date:</strong> <?= $trip['end_date'] ?></p>
                        <p><strong>Group Size:</strong> <?= $group_size ?></p>
                        <p><strong>Current Members:</strong> <?= $members['total_members'] ?></p>
                    </div>

                    <div class="mt-6">
                        <?php if ($members['total_members'] < $group_size) { ?>
                            <form method="POST" action="join_trip.php?trip_id=<?= $trip_id ?>">
                                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-md">Join This Trip</button>
                            </form>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
