<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4F46E5',
                        secondary: '#6B7280'
                    }
                }
            }
        };

        function loadContent(page) {
            let title = document.getElementById("page-title");
            let content = document.getElementById("content");

            if (page === "dashboard") {
                title.innerText = "Dashboard";
                content.innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white p-6 rounded-lg shadow-md">
                            <h3 class="text-xl font-semibold">Revenue</h3>
                            <p class="text-2xl font-bold">-</p>
                            <span class="text-green-500">↑ 32k increase</span>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-md">
                            <h3 class="text-xl font-semibold">New Customers</h3>
                            <p class="text-2xl font-bold">-</p>
                            <span class="text-red-500">↓ 3% decrease</span>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-md">
                            <h3 class="text-xl font-semibold">New Orders</h3>
                            <p class="text-2xl font-bold">3.54k</p>
                            <span class="text-green-500">↑ 7% increase</span>
                        </div>
                    </div>
                `;
            } else if (page === "products") {
                title.innerText = "Products";
                content.innerHTML = `
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold">Product List</h3>
                        <ul class="mt-4 space-y-2">
                            <li class="border p-2 rounded-md">Product A</li>
                            <li class="border p-2 rounded-md">Product B</li>
                            <li class="border p-2 rounded-md">Product C</li>
                        </ul>
                    </div>
                `;
            } else if (page === "orders") {
                title.innerText = "Orders";
                content.innerHTML = `
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold">Recent Orders</h3>
                        <p class="mt-2">No recent orders available.</p>
                    </div>
                `;
            } else if (page === "customers") {
                title.innerText = "Customers";
                content.innerHTML = `
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold">Customer Details</h3>
                        <p class="mt-2">List of customers will be displayed here.</p>
                    </div>
                `;
            }
        }
    </script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white h-full p-5 shadow-md flex flex-col">
            <img src="images.png" alt="Anoor Cloud Logo" class="w-24 h-12 pl-8">
            <nav class="mt-5">
                <ul class="space-y-2">
                    <li class="p-3 hover:bg-gray-200 rounded-lg cursor-pointer flex items-center">
                        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                            <!-- Dashboard Icon (Home Icon) -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 9L12 2L21 9"></path>
                                <path d="M9 22V12H15V22"></path>
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
  <!-- Links -->
  <li class="p-2 rounded-lg cursor-pointer flex items-center
  {{ request()->routeIs('links') ? 'bg-gray-100 text-orange-500' : 'hover:bg-gray-200 text-gray-800' }}">
  <a href="{{ route('links') }}" class="flex items-center space-x-2">
      <svg class="w-[27px] h-[27px]
          {{ request()->routeIs('links') ? 'text-orange-500' : 'text-gray-800' }}"
          aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M13.213 9.787a3.391 3.391 0 0 0-4.795 0l-3.425 3.426a3.39 3.39 0 0 0 4.795 4.794l.321-.304m-.321-4.49a3.39 3.39 0 0 0 4.795 0l3.424-3.426a3.39 3.39 0 0 0-4.794-4.795l-1.028.961"/>
      </svg>
      <span>Links</span>
  </a>
</li>

                    <!-- Products -->
                <li class="p-3 hover:bg-gray-200 rounded-lg cursor-pointer flex items-center">
                    <a href="{{ route('products') }}" class="flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 3h18v18H3V3z"></path>
                            <path d="M3 9h18"></path>
                        </svg>
                        <span>Products</span>
                    </a>
                </li>



                <!-- Source -->
                <li class="p-3 hover:bg-gray-200 rounded-lg cursor-pointer flex items-center">
                    <a href="{{ route('source') }}" class="flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 18L22 12 16 6"></path>
                            <path d="M8 6L2 12l6 6"></path>
                            <path d="M10 12H2"></path>
                            <path d="M14 12h8"></path>
                        </svg>
                        <span>Source</span>
                    </a>
                </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 id="page-title" class="text-3xl font-bold">Dashboard</h2>
                <!-- <input type="text" placeholder="Search" class="px-4 py-2 border rounded-lg w-64">-->
            </div>

            <!-- Filters -->
            <div class="max-w-7xl bg-white p-6 rounded-lg shadow-md">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Dropdown -->
                    <div class="flex flex-col gap-2">
                        <label class="text-gray-700 font-medium">choose Type</label>
                        <select class="w-full p-2 border rounded-md">
                            <option>-</option>
                            <option>PIVC</option>
                            <option>RiNn Raksha</option>
                        </select>
                    </div>

                  <!-- Start Date -->
                <div class="flex flex-col gap-2">
                    <label class="text-gray-700 font-medium">Start date</label>
                    <input type="date" class="w-full p-2 border rounded-md">
                </div>

                <!-- End Date -->
                <div class="flex flex-col gap-2">
                    <label class="text-gray-700 font-medium">End date</label>
                    <input type="date" class="w-full p-2 border rounded-md">
                </div>

                </div>
            </div>

            <!-- Dynamic Content -->
            <div id="content" class="mt-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold text-gray-500">Total Links</h3>
                        <p class="text-2xl font-bold">{{ $linksCount }}</p>
                        <span class="text-green-500">↑ 32k increase</span>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold text-gray-500">Completed</h3>
                        <p class="text-2xl font-bold">0</p>
                        <span class="text-red-500">↓ 3% decrease</span>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold text-gray-500">Not Completed</h3>
                        <p class="text-2xl font-bold">0</p>
                        <span class="text-green-500">↑ 7% increase</span>
                    </div>
                </div>
            </div>

            <!--    2nd row-->
            <div id="content" class="mt-6">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold text-gray-500">Products</h3>
                        <p class="text-2xl font-bold">{{ $productCount }}</p>

                        <span class="text-green-500">↑ 32k increase</span>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold text-gray-500">Source</h3>
                        <p class="text-2xl font-bold">{{ $sourceCount }}</p>
                        <span class="text-red-500">↓ 3% decrease</span>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold text-gray-500">Pdf not created</h3>
                        <p class="text-2xl font-bold">0</p>
                        <span class="text-green-500">↑ 7% increase</span>
                    </div>

                </div>
            </div>

            <div id="content" class="mt-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold text-gray-500">SFTP Pushed</h3>
                        <p class="text-2xl font-bold">0</p>
                        <span class="text-green-500">↑ 32k increase</span>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold text-gray-500">SFTP Not Pushed</h3>
                        <p class="text-2xl font-bold">0</p>
                        <span class="text-red-500">↓ 3% decrease</span>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold text-gray-500">Deactivated Link</h3>
                        <p class="text-2xl font-bold">0</p>
                        <span class="text-green-500">↑ 7% increase</span>
                    </div>
                </div>
            </div>

        </main>
    </div>


</body>
</html>
