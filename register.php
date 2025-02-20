<?php
session_start();
require_once 'config.php'; // Include database connection

// Initialize error and success messages
$error = '';
$success = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['password'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if the email already exists
    $email_check_query = "SELECT * FROM users WHERE email = ?";
    $email_check_stmt = $conn->prepare($email_check_query);
    $email_check_stmt->bind_param('s', $email);
    $email_check_stmt->execute();
    $email_check_result = $email_check_stmt->get_result();

    if ($email_check_result->num_rows > 0) {
        $error = 'Email is already registered.';
    } else {
        // Insert the new user into the database
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert_query = "INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param('ssss', $first_name, $last_name, $email, $hashed_password);
        
        if ($insert_stmt->execute()) {
            $success = 'Registration successful! You can now log in.';
        } else {
            $error = 'There was an issue registering your account. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Syngo</title>
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
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Create a new account</h2>
                    <p class="text-gray-600 mb-6 text-sm sm:text-base">Fill in the details to sign up</p>

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

                    <form action="register.php" method="POST" class="space-y-4 sm:space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2" for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="block w-full py-2 px-3 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary text-sm" placeholder="Enter your first name" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2" for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="block w-full py-2 px-3 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary text-sm" placeholder="Enter your last name" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2" for="email">Email</label>
                            <input type="email" id="email" name="email" class="block w-full py-2 px-3 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary text-sm" placeholder="Enter your email" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2" for="password">Password</label>
                            <input type="password" id="password" name="password" class="block w-full py-2 px-3 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary text-sm" placeholder="Enter your password" required>
                        </div>

                        <button type="submit" class="w-full bg-primary text-white py-2 px-4 rounded hover:bg-primary/90 focus:ring-4 focus:ring-primary/20 text-sm sm:text-base">Sign up</button>
                    </form>

                    <p class="mt-6 sm:mt-8 text-center text-xs sm:text-sm text-gray-600">
                        Already have an account? <a href="login.php" class="text-primary hover:text-primary/80">Sign in</a>
                    </p>
                </div>
            </div>
        </main>
    </div>

    <script>
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>