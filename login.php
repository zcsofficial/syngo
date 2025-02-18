<?php
session_start();
require_once "config.php"; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT user_id, first_name, last_name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $first_name, $last_name, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION["user_id"] = $user_id;
                $_SESSION["first_name"] = $first_name;
                $_SESSION["last_name"] = $last_name;
                echo json_encode(["success" => true, "message" => "Login successful!", "redirect" => "dashboard.php"]);
            } else {
                echo json_encode(["success" => false, "message" => "Incorrect password!"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "No account found with this email!"]);
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
    <title>Login - Syngo</title>
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
            <a href="register.php" class="text-white">Register</a>
        </div>
    </nav>

    <!-- Login Container -->
    <div class="flex justify-center items-center min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
            <h2 class="text-center text-2xl font-bold text-green-500 mb-4">Login</h2>
            <form id="loginForm">
                <div class="mb-4">
                    <label class="block text-gray-700">Email</label>
                    <input type="email" name="email" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Password</label>
                    <input type="password" name="password" class="w-full p-2 border rounded" required>
                </div>
                <button type="submit" class="w-full bg-green-500 text-white p-2 rounded">Login</button>
            </form>
            <p class="text-center text-gray-600 mt-4">Don't have an account? <a href="register.php" class="text-green-500">Register</a></p>
            <p id="message" class="text-center mt-2"></p>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-200 p-4 text-center text-gray-700">
        &copy; 2025 Syngo. All rights reserved.
    </footer>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            fetch('login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                let msg = document.getElementById('message');
                msg.textContent = data.message;
                msg.className = data.success ? "text-green-500" : "text-red-500";
                if (data.success) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    </script>

</body>
</html>
