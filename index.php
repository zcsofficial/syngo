<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syngo - A Trip with Stranger</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>:where([class^="ri-"])::before { content: "\f3c2"; }</style>
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
<body class="bg-white font-sans">

    <!-- Navigation -->
    <nav class="fixed top-0 w-full bg-white/95 backdrop-blur-sm z-50 border-b border-gray-100 shadow-md">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-4">
                    <a href="index" class="text-3xl font-['Pacifico'] text-primary">Syngo</a>
                    <div class="hidden md:flex space-x-8 ml-10">
                        <a href="#features" class="text-gray-700 hover:text-primary">Features</a>
                        <a href="#communities" class="text-gray-700 hover:text-primary">Communities</a>
                        <a href="#planning" class="text-gray-700 hover:text-primary">Planning</a>
                        <a href="#budget" class="text-gray-700 hover:text-primary">Budget</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Updated Sign In Button with Link -->
                    <a href="login.php" class="px-6 py-2 text-primary border border-primary hover:bg-primary hover:text-white transition-colors !rounded-button whitespace-nowrap">Sign In</a>
                    <!-- Updated Get Started Button with Link -->
                    <a href="register.php" class="px-6 py-2 bg-primary text-white hover:bg-primary/90 transition-colors !rounded-button whitespace-nowrap">Get Started</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-16 relative min-h-screen flex items-center justify-center text-center bg-cover bg-center" style="background-image: url('https://public.readdy.ai/ai/img_res/237f92833a7087695fcb9138e5537c08.jpg');">
        <div class="absolute inset-0 bg-gradient-to-r from-white via-white/90 to-transparent"></div>
        <div class="max-w-4xl mx-auto px-4 relative">
            <h1 class="text-5xl font-bold text-gray-900 mb-6">Travel With New Friends</h1>
            <p class="text-xl text-gray-600 mb-8">Connect with like-minded travelers, plan amazing trips together, and create unforgettable memories. Join Syngo today and discover a new way to explore the world.</p>
            <a href="register.php" class="px-8 py-3 bg-primary text-white text-lg hover:bg-primary/90 transition-colors !rounded-button whitespace-nowrap">Start Your Journey</a>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900">Why Choose Syngo</h2>
                <p class="mt-4 text-gray-600">Everything you need to make your group travel experience amazing</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-lg shadow-sm">
                    <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-6">
                        <i class="ri-group-line text-primary text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Connect with Travelers</h3>
                    <p class="text-gray-600">Join communities of travelers who share your interests and travel style. Make friends and plan trips together.</p>
                </div>
                <div class="bg-white p-8 rounded-lg shadow-sm">
                    <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-6">
                        <i class="ri-map-2-line text-primary text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Smart Trip Planning</h3>
                    <p class="text-gray-600">Powerful tools to plan your itinerary, find accommodations, and coordinate with your travel group.</p>
                </div>
                <div class="bg-white p-8 rounded-lg shadow-sm">
                    <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-6">
                        <i class="ri-wallet-3-line text-primary text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Budget Management</h3>
                    <p class="text-gray-600">Track expenses, split costs, and manage group finances easily with our budget planning tools.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Communities Section -->
    <section id="communities" class="py-24">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900">Popular Travel Communities</h2>
                <p class="mt-4 text-gray-600">Find your perfect travel companions</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <img src="https://public.readdy.ai/ai/img_res/1eb0f9cb41eddd07470f157fca0fd7b0.jpg" alt="Adventure Seekers" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Adventure Seekers</h3>
                        <p class="text-gray-600 mb-4">For those who love hiking, camping, and outdoor adventures.</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">1,234 members</span>
                            <button class="px-4 py-2 bg-primary text-white hover:bg-primary/90 transition-colors !rounded-button whitespace-nowrap">Join Group</button>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <img src="https://public.readdy.ai/ai/img_res/172cbc65e420eca0a0d3f5feda97a579.jpg" alt="Culture Explorers" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Culture Explorers</h3>
                        <p class="text-gray-600 mb-4">Discover local traditions, arts, and authentic experiences.</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">987 members</span>
                            <button class="px-4 py-2 bg-primary text-white hover:bg-primary/90 transition-colors !rounded-button whitespace-nowrap">Join Group</button>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <img src="https://img.freepik.com/free-photo/wooden-board-empty-table-front-blue-sea-sky-background-perspective-wood-floor-sea-sky-can-be-used-display-montage-your-products-beach-summer-concepts_1253-804.jpg?t=st=1739869701~exp=1739873301~hmac=667071c90010c7f66a066debf0712a19b1eb711f8376e78a05e60b7d6b2865f1&w=1060" alt="Beach Lovers" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Beach Lovers</h3>
                        <p class="text-gray-600 mb-4">For those who dream of sun, sand, and sea.</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">345 members</span>
                            <button class="px-4 py-2 bg-primary text-white hover:bg-primary/90 transition-colors !rounded-button whitespace-nowrap">Join Group</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-wrap justify-between">
                <div>
                    <a href="#" class="text-3xl font-['Pacifico'] text-primary">Syngo</a>
                </div>
                <div class="space-x-8">
                    <a href="#features" class="text-white hover:text-primary">Features</a>
                    <a href="#communities" class="text-white hover:text-primary">Communities</a>
                    <a href="#planning" class="text-white hover:text-primary">Planning</a>
                    <a href="#budget" class="text-white hover:text-primary">Budget</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
