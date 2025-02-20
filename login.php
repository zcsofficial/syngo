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
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}

// Handle password reset request
if (isset($_POST['reset_email'])) {
    $reset_email = $_POST['reset_email'];
    $reset_query = "SELECT * FROM users WHERE email = ?";
    $reset_stmt = $conn->prepare($reset_query);
    $reset_stmt->bind_param('s', $reset_email);
    $reset_stmt->execute();
    $reset_result = $reset_stmt->get_result();

    if ($reset_result->num_rows > 0) {
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
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
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
<body class="bg-gray-50 min-h-screen font-sans">
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <header class="p-4 sm:p-6 flex justify-between items-center bg-white shadow-sm fixed top-0 w-full z-10">
            <a href="/" class="text-xl sm:text-2xl font-['Pacifico'] text-primary">Syngo</a>
            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="sm:hidden p-2 text-gray-700 focus:outline-none">
                <i class="ri-menu-line text-xl"></i>
            </button>
            <!-- Desktop Navigation -->
            <nav id="nav-links" class="hidden sm:flex items-center gap-6 md:gap-8">
                <a href="/" class="text-gray-600 hover:text-primary text-sm md:text-base">Home</a>
                <a href="/about" class="text-gray-600 hover:text-primary text-sm md:text-base">About</a>
                <a href="/contact" class="text-gray-600 hover:text-primary text-sm md:text-base">Contact</a>
            </nav>
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden sm:hidden absolute top-16 left-0 w-full bg-white shadow-md border-t border-gray-100">
                <div class="flex flex-col p-4 space-y-4">
                    <a href="/" class="text-gray-600 hover:text-primary">Home</a>
                    <a href="/about" class="text-gray-600 hover:text-primary">About</a>
                    <a href="/contact" class="text-gray-600 hover:text-primary">Contact</a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 flex items-center justify-center p-4 sm:p-8 mt-16 sm:mt-0">
            <div class="w-full max-w-sm sm:max-w-md">
                <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Welcome back</h2>
                    <p class="text-gray-600 mb-6 text-sm sm:text-base">Please enter your details to sign in</p>

                    <?php if ($error): ?>
                        <div class="bg-red-100 text-red-700 p-3 sm:p-4 rounded mb-4 text-sm">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="bg-green-100 text-green-700 p-3 sm:p-4 rounded mb-4 text-sm">
                            <?= $success ?>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="POST" class="space-y-4 sm:space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2" for="email">Email</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="ri-mail-line text-gray-400 w-4 h-4 sm:w-5 sm:h-5"></i>
                                </div>
                                <input type="email" id="email" name="email" class="block w-full pl-9 sm:pl-10 pr-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary text-sm" placeholder="Enter your email" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2" for="password">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="ri-lock-line text-gray-400 w-4 h-4 sm:w-5 sm:h-5"></i>
                                </div>
                                <input type="password" id="password" name="password" class="block w-full pl-9 sm:pl-10 pr-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary text-sm" placeholder="Enter your password" required>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <label class="flex items-center">
                                <input type="checkbox" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                <span class="ml-2 text-xs sm:text-sm text-gray-600">Remember me</span>
                            </label>
                            <button type="button" class="text-xs sm:text-sm text-primary hover:text-primary/80" onclick="showForgotPassword()">Forgot password?</button>
                        </div>

                        <button type="submit" class="w-full bg-primary text-white py-2 px-4 rounded hover:bg-primary/90 focus:ring-4 focus:ring-primary/20 text-sm sm:text-base">Sign in</button>
                    </form>

                    <p class="mt-6 sm:mt-8 text-center text-xs sm:text-sm text-gray-600">
                        Don't have an account? <a href="register.php" class="text-primary hover:text-primary/80">Sign up</a>
                    </p>
                </div>
            </div>
        </main>

        <!-- Forgot Password Modal -->
        <div id="forgotPasswordModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden z-20 p-4">
            <div class="bg-white p-4 sm:p-6 rounded-lg max-w-xs sm:max-w-sm w-full">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-3 sm:mb-4">Forgot your password?</h3>
                <p class="text-xs sm:text-sm text-gray-600 mb-4 sm:mb-6">Enter your email to receive a password reset link.</p>
                <form action="login.php" method="POST" class="space-y-4">
                    <div>
                        <label for="reset_email" class="text-sm font-medium text-gray-700 mb-1 sm:mb-2">Email</label>
                        <input type="email" id="reset_email" name="reset_email" class="block w-full py-2 px-3 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary text-sm" placeholder="Enter your email" required>
                    </div>
                    <button type="submit" class="w-full bg-primary text-white py-2 px-4 rounded mt-2 sm:mt-4 text-sm">Reset Password</button>
                </form>
                <button type="button" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600" onclick="hideForgotPassword()">
                    <i class="ri-close-line text-lg"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        function showForgotPassword() {
            document.getElementById('forgotPasswordModal').classList.remove('hidden');
        }

        function hideForgotPassword() {
            document.getElementById('forgotPasswordModal').classList.add('hidden');
        }
    </script>
</body>
</html>