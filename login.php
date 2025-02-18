<?php
session_start();
require_once 'config.php'; // Include database connection
// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
// Initialize error and success messages
$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'], $_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query to fetch user by email
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Verify the password
    if ($user && password_verify($password, $user['password'])) {
        // User authenticated, start session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        // Redirect to the dashboard or homepage
        header("Location: dashboard.php");
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}

// Handle password reset request
if (isset($_POST['reset_email'])) {
    $reset_email = $_POST['reset_email'];

    // Query to check if the email exists
    $reset_query = "SELECT * FROM users WHERE email = ?";
    $reset_stmt = $conn->prepare($reset_query);
    $reset_stmt->bind_param('s', $reset_email);
    $reset_stmt->execute();
    $reset_result = $reset_stmt->get_result();

    if ($reset_result->num_rows > 0) {
        // Password reset link generation (for demo purposes, just a message)
        $success = 'A password reset link has been sent to your email.';
    } else {
        $error = 'Email not found.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Syngo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
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
<body class="bg-gray-50 min-h-screen">
    <div class="flex min-h-screen">
        <div class="flex-1 flex flex-col justify-between">
            <header class="p-6 flex justify-between items-center">
                <a href="/" class="text-2xl font-['Pacifico'] text-primary">Syngo</a>
                <nav class="flex items-center gap-8">
                    <a href="/" class="text-gray-600 hover:text-primary">Home</a>
                    <a href="/about" class="text-gray-600 hover:text-primary">About</a>
                    <a href="/contact" class="text-gray-600 hover:text-primary">Contact</a>
                </nav>
            </header>

            <main class="flex-1 flex items-center justify-center p-8">
                <div class="w-full max-w-md">
                    <div class="bg-white p-8 rounded-lg shadow-sm">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Welcome back</h2>
                        <p class="text-gray-600 mb-8">Please enter your details to sign in</p>

                        <?php if ($error): ?>
                            <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
                                <?= $error ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="bg-green-100 text-green-700 p-4 rounded mb-4">
                                <?= $success ?>
                            </div>
                        <?php endif; ?>

                        <form action="login.php" method="POST" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="email">Email</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="ri-mail-line text-gray-400 w-5 h-5 flex items-center justify-center"></i>
                                    </div>
                                    <input type="email" id="email" name="email" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary text-sm" placeholder="Enter your email" required>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="password">Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="ri-lock-line text-gray-400 w-5 h-5 flex items-center justify-center"></i>
                                    </div>
                                    <input type="password" id="password" name="password" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary text-sm" placeholder="Enter your password" required>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <label class="flex items-center">
                                    <input type="checkbox" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-600">Remember me</span>
                                </label>
                                <button type="button" class="text-sm text-primary hover:text-primary/80" onclick="showForgotPassword()">Forgot password?</button>
                            </div>

                            <button type="submit" class="w-full bg-primary text-white py-2 px-4 rounded hover:bg-primary/90 focus:ring-4 focus:ring-primary/20 whitespace-nowrap">Sign in</button>
                        </form>

                        <p class="mt-8 text-center text-sm text-gray-600">
                            Don't have an account? <a href="register.php" class="text-primary hover:text-primary/80">Sign up</a>
                        </p>
                    </div>
                </div>
            </main>

            <!-- Forgot Password Modal -->
            <div id="forgotPasswordModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden">
                <div class="bg-white p-6 rounded-lg max-w-sm w-full">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Forgot your password?</h3>
                    <p class="text-sm text-gray-600 mb-6">Enter your email to receive a password reset link.</p>
                    <form action="login.php" method="POST">
                        <div>
                            <label for="reset_email" class="text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" id="reset_email" name="reset_email" class="block w-full py-2 px-3 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter your email" required>
                        </div>
                        <button type="submit" class="w-full bg-primary text-white py-2 px-4 rounded mt-4">Reset Password</button>
                    </form>
                    <button type="button" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600" onclick="hideForgotPassword()">X</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showForgotPassword() {
            document.getElementById('forgotPasswordModal').classList.remove('hidden');
        }

        function hideForgotPassword() {
            document.getElementById('forgotPasswordModal').classList.add('hidden');
        }
    </script>
</body>
</html>
