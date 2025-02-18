<?php
session_start();
require_once "config.php"; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (!empty($first_name) && !empty($last_name) && !empty($email) && !empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $first_name, $last_name, $email, $hashed_password);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Registration successful!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error: Email already exists!"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "All fields are required!"]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Syngo</title>
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

    <!-- Navbar -->
    <nav class="bg-green-500 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-white text-lg font-bold">Syngo</a>
            <a href="login.php" class="text-white">Login</a>
        </div>
    </nav>

    <!-- Registration Container -->
    <div class="flex justify-center items-center min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
            <h2 class="text-center text-2xl font-bold text-green-500 mb-4">Register</h2>
            <form id="registerForm">
                <div class="mb-4">
                    <label class="block text-gray-700">First Name</label>
                    <input type="text" name="first_name" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Last Name</label>
                    <input type="text" name="last_name" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Email</label>
                    <input type="email" name="email" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Password</label>
                    <input type="password" name="password" class="w-full p-2 border rounded" required>
                </div>
                <button type="submit" class="w-full bg-green-500 text-white p-2 rounded">Register</button>
            </form>
            <p class="text-center text-gray-600 mt-4">Already have an account? <a href="login.php" class="text-green-500">Login</a></p>
            <p id="message" class="text-center mt-2"></p>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-200 p-4 text-center text-gray-700">
        &copy; 2025 Syngo. All rights reserved.
    </footer>

    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            fetch('register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                let msg = document.getElementById('message');
                msg.textContent = data.message;
                msg.className = data.success ? "text-green-500" : "text-red-500";
            })
            .catch(error => console.error('Error:', error));
        });
    </script>

</body>
</html>
