<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syngo - A Trip with Stranger</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
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
<body class="bg-white">
    <nav class="fixed top-0 w-full bg-white/95 backdrop-blur-sm z-50 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="#" class="text-2xl font-['Pacifico'] text-primary">Syngo</a>
                    <div class="hidden md:flex space-x-8 ml-10">
                        <a href="#features" class="text-gray-700 hover:text-primary">Features</a>
                        <a href="#communities" class="text-gray-700 hover:text-primary">Communities</a>
                        <a href="#planning" class="text-gray-700 hover:text-primary">Planning</a>
                        <a href="#budget" class="text-gray-700 hover:text-primary">Budget</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="px-6 py-2 text-primary border border-primary hover:bg-primary hover:text-white transition-colors !rounded-button whitespace-nowrap">Sign In</button>
                    <button class="px-6 py-2 bg-primary text-white hover:bg-primary/90 transition-colors !rounded-button whitespace-nowrap">Get Started</button>
                </div>
            </div>
        </div>
    </nav>

    <section class="pt-16 relative min-h-screen flex items-center" style="background-image: url('https://public.readdy.ai/ai/img_res/237f92833a7087695fcb9138e5537c08.jpg'); background-size: cover; background-position: center;">
        <div class="absolute inset-0 bg-gradient-to-r from-white via-white/90 to-transparent"></div>
        <div class="max-w-7xl mx-auto px-4 relative">
            <div class="max-w-2xl">
                <h1 class="text-5xl font-bold text-gray-900 mb-6">Travel With New Friends</h1>
                <p class="text-xl text-gray-600 mb-8">Connect with like-minded travelers, plan amazing trips together, and create unforgettable memories. Join Syngo today and discover a new way to explore the world.</p>
                <button class="px-8 py-3 bg-primary text-white text-lg hover:bg-primary/90 transition-colors !rounded-button whitespace-nowrap">Start Your Journey</button>
            </div>
        </div>
    </section>

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
                    <img src="https://public.readdy.ai/ai/img_res/e320b4fbb4317625249c5eacdc684db9.jpg" alt="Food Travelers" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Food Travelers</h3>
                        <p class="text-gray-600 mb-4">Experience the world through local cuisines and food tours.</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">756 members</span>
                            <button class="px-4 py-2 bg-primary text-white hover:bg-primary/90 transition-colors !rounded-button whitespace-nowrap">Join Group</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="planning" class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid md:grid-cols-2 gap-16 items-center">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-6">Plan Your Perfect Trip</h2>
                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="ri-route-line text-primary"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">Interactive Itinerary Builder</h3>
                                <p class="text-gray-600">Create detailed day-by-day plans with our easy-to-use tools.</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="ri-hotel-line text-primary"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">Accommodation Finder</h3>
                                <p class="text-gray-600">Find and book group-friendly accommodations worldwide.</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="ri-calendar-line text-primary"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">Group Calendar</h3>
                                <p class="text-gray-600">Coordinate schedules and plan activities together.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <img src="https://public.readdy.ai/ai/img_res/927587f17c2b897f84c1b48f90f1db6c.jpg" alt="Planning Tools" class="rounded-lg shadow-lg">
                </div>
            </div>
        </div>
    </section>

    <section id="budget" class="py-24">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900">Smart Budget Management</h2>
                <p class="mt-4 text-gray-600">Keep track of group expenses and split costs fairly</p>
            </div>
            <div class="grid md:grid-cols-2 gap-16">
                <div class="bg-white p-8 rounded-lg shadow-sm">
                    <h3 class="text-xl font-semibold text-gray-900 mb-6">Expense Tracking</h3>
                    <div id="expenseChart" class="w-full h-80"></div>
                </div>
                <div class="space-y-8">
                    <div class="bg-white p-8 rounded-lg shadow-sm">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Budget Templates</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Transportation</span>
                                <div class="w-48 h-2 bg-gray-200 rounded-full">
                                    <div class="w-3/4 h-full bg-primary rounded-full"></div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Accommodation</span>
                                <div class="w-48 h-2 bg-gray-200 rounded-full">
                                    <div class="w-1/2 h-full bg-primary rounded-full"></div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Activities</span>
                                <div class="w-48 h-2 bg-gray-200 rounded-full">
                                    <div class="w-1/4 h-full bg-primary rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-8 rounded-lg shadow-sm">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Split Expenses</h3>
                        <p class="text-gray-600 mb-4">Easily split bills and track who owes what</p>
                        <button class="px-6 py-3 bg-primary text-white hover:bg-primary/90 transition-colors !rounded-button whitespace-nowrap">Try Budget Planner</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <a href="#" class="text-2xl font-['Pacifico'] text-white mb-4 block">Syngo</a>
                    <p class="text-gray-400">Making group travel easier and more enjoyable for everyone.</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Features</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">Communities</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Trip Planning</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Budget Tools</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Company</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Careers</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Stay Connected</h4>
                    <div class="flex space-x-4 mb-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-primary transition-colors">
                            <i class="ri-facebook-fill"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-primary transition-colors">
                            <i class="ri-twitter-fill"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-primary transition-colors">
                            <i class="ri-instagram-fill"></i>
                        </a>
                    </div>
                    <form class="space-y-4">
                        <input type="email" placeholder="Enter your email" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-primary">
                        <button type="submit" class="w-full px-4 py-2 bg-primary text-white hover:bg-primary/90 transition-colors !rounded-button">Subscribe</button>
                    </form>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-400">
                <p>&copy; 2025 Syngo. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
    <script>
        const chart = echarts.init(document.getElementById('expenseChart'));
        const option = {
            animation: false,
            tooltip: {
                trigger: 'item',
                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                borderColor: '#ddd',
                textStyle: {
                    color: '#1f2937'
                }
            },
            legend: {
                bottom: '5%',
                left: 'center',
                textStyle: {
                    color: '#1f2937'
                }
            },
            series: [{
                name: 'Expense Distribution',
                type: 'pie',
                radius: ['40%', '70%'],
                avoidLabelOverlap: false,
                itemStyle: {
                    borderRadius: 8,
                    borderColor: '#fff',
                    borderWidth: 2
                },
                label: {
                    show: false,
                    position: 'center'
                },
                emphasis: {
                    label: {
                        show: true,
                        fontSize: 20,
                        fontWeight: 'bold'
                    }
                },
                labelLine: {
                    show: false
                },
                data: [
                    { value: 1048, name: 'Transportation', itemStyle: { color: 'rgba(87, 181, 231, 1)' } },
                    { value: 735, name: 'Accommodation', itemStyle: { color: 'rgba(141, 211, 199, 1)' } },
                    { value: 580, name: 'Food', itemStyle: { color: 'rgba(251, 191, 114, 1)' } },
                    { value: 484, name: 'Activities', itemStyle: { color: 'rgba(252, 141, 98, 1)' } }
                ]
            }]
        };
        chart.setOption(option);

        window.addEventListener('resize', () => {
            chart.resize();
        });
    </script>
</body>
</html>
