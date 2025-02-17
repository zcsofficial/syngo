<?php
session_start();
include('config.php');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle Login
    if (isset($_POST['login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
        $result = mysqli_query($conn, $query);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password'])) {
            // Setting session variables
            $_SESSION['user_id'] = $user['user_id'];  // Updated field for user ID
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];  // Concatenate first and last name
            header('Location: dashboard.php'); // Redirect to dashboard after login
            exit;
        } else {
            $login_error = "Invalid email or password.";
        }
    }

    // Handle Registration
    if (isset($_POST['register'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        // Split name into first and last name
        $name_parts = explode(' ', $name, 2);
        $first_name = $name_parts[0];
        $last_name = isset($name_parts[1]) ? $name_parts[1] : '';

        // Check if email already exists
        $check_email_query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
        $check_email_result = mysqli_query($conn, $check_email_query);
        if (mysqli_num_rows($check_email_result) > 0) {
            $register_error = "Email already exists.";
        } else {
            $query = "INSERT INTO users (first_name, last_name, email, password, role) 
                      VALUES ('$first_name', '$last_name', '$email', '$password', 'user')";
            if (mysqli_query($conn, $query)) {
                $_SESSION['user_id'] = mysqli_insert_id($conn);
                $_SESSION['user_name'] = $first_name . ' ' . $last_name;
                header('Location: dashboard.php'); // Redirect to dashboard after registration
                exit;
            } else {
                $register_error = "Error registering user.";
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
    <title>Syngo - Login / Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="max-w-md mx-auto mt-20 bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-center text-gray-900 mb-6">Syngo - Sign In / Sign Up</h1>

        <!-- Login Form -->
        <?php if (!isset($_GET['action']) || $_GET['action'] == 'login'): ?>
        <form action="auth.php" method="POST">
            <div class="mb-4">
                <label for="email" class="block text-gray-700">Email</label>
                <input type="email" name="email" id="email" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-primary" required>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700">Password</label>
                <input type="password" name="password" id="password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-primary" required>
            </div>
            <?php if (isset($login_error)): ?>
                <p class="text-red-500 text-sm"><?php echo $login_error; ?></p>
            <?php endif; ?>
            <div class="flex items-center justify-between">
                <button type="submit" name="login" class="px-6 py-2 bg-primary text-white rounded-md">Login</button>
                <a href="auth.php?action=register" class="text-primary">Sign Up</a>
            </div>
            <div class="text-center mt-4">
                <a href="#" class="text-sm text-gray-500">Forgot Password?</a>
            </div>
        </form>

        <!-- Register Form -->
        <?php elseif ($_GET['action'] == 'register'): ?>
        <form action="auth.php" method="POST">
            <div class="mb-4">
                <label for="name" class="block text-gray-700">Name</label>
                <input type="text" name="name" id="name" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-primary" required>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700">Email</label>
                <input type="email" name="email" id="email" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-primary" required>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700">Password</label>
                <input type="password" name="password" id="password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-primary" required>
            </div>
            <div class="mb-4">
                <label for="confirm_password" class="block text-gray-700">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-primary" required>
            </div>
            <?php if (isset($register_error)): ?>
                <p class="text-red-500 text-sm"><?php echo $register_error; ?></p>
            <?php endif; ?>
            <button type="submit" name="register" class="w-full px-6 py-2 bg-primary text-white rounded-md">Register</button>
            <div class="text-center mt-4">
                <a href="auth.php?action=login" class="text-primary">Already have an account? Login</a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
