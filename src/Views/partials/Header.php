<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Enterprise HRMS - <?= htmlspecialchars($title ?? 'Dashboard') ?></title>

  <!-- Tailwind CSS (CDN) -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Font Awesome 6 (for icons) -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
  />
</head>
<body class="select-none bg-gray-100 min-h-screen">

  <!-- Top Header -->
  <header class="bg-blue-900 text-white shadow-md">
    <div class="flex items-center justify-between px-4 py-3 sm:px-6 lg:px-8">

      <!-- Left: Branding (and optional sidebar toggle if needed) -->
      <div class="flex items-center space-x-4">
        <!-- Branding -->
        <a 
          href="/dashboard" 
          class="text-2xl font-bold tracking-wide hover:text-blue-200 transition-colors"
        >
          HRMS
        </a>
      </div>

      <!-- Center: Search Bar -->
      <div class="hidden md:flex flex-1 mx-6 max-w-2xl">
        <div class="relative w-full">
          <input
            type="text"
            placeholder="Search..."
            class="w-full pl-10 pr-4 py-2 rounded-full focus:outline-none text-gray-800"
          />
          <span class="absolute left-3 top-2 text-gray-400">
            <i class="fa-solid fa-magnifying-glass"></i>
          </span>
        </div>
      </div>

      <!-- Right: Icons & Profile -->
      <div class="flex items-center space-x-4">
        <!-- Notification Bell -->
        <div class="relative">
          <button
            class="relative focus:outline-none hover:text-blue-200 transition-colors"
            onclick="toggleDropdown('notificationDropdown')"
          >
            <i class="fa-solid fa-bell text-xl"></i>
            <!-- Notification Badge -->
            <span 
              class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full text-xs w-4 h-4 flex items-center justify-center"
            >
              3
            </span>
          </button>
          <!-- Notification Dropdown -->
          <div
            id="notificationDropdown"
            data-dropdown
            class="hidden absolute right-0 mt-2 w-80 bg-white text-gray-800 rounded-md shadow-lg"
          >
            <div class="p-4 border-b border-gray-200 font-semibold">
              Notifications
            </div>
            <ul class="max-h-60 overflow-auto">
              <!-- Example notification items; replace with your data -->
              <li class="px-4 py-2 hover:bg-gray-100 flex items-start space-x-2">
                <i class="fa-solid fa-envelope text-blue-600 mt-1"></i>
                <div>
                  <p class="font-medium">New Message</p>
                  <p class="text-sm text-gray-500">You have received a new message.</p>
                </div>
              </li>
              <li class="px-4 py-2 hover:bg-gray-100 flex items-start space-x-2">
                <i class="fa-solid fa-calendar-check text-green-600 mt-1"></i>
                <div>
                  <p class="font-medium">Task Completed</p>
                  <p class="text-sm text-gray-500">Your leave request has been approved.</p>
                </div>
              </li>
              <!-- Add more notifications here -->
            </ul>
            <div class="p-2 text-center border-t border-gray-200">
              <a
                href="/notifications"
                class="block w-full text-blue-600 hover:underline"
              >
                View All
              </a>
            </div>
          </div>
        </div>

        <!-- Chat Icon -->
        <div class="relative">
          <button
            class="relative focus:outline-none hover:text-blue-200 transition-colors"
            onclick="toggleDropdown('chatDropdown')"
          >
            <i class="fa-solid fa-comments text-xl"></i>
            <!-- Chat Badge -->
            <span 
              class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full text-xs w-4 h-4 flex items-center justify-center"
            >
              5
            </span>
          </button>
          <!-- Chat Dropdown -->
          <div
            id="chatDropdown"
            data-dropdown
            class="hidden absolute right-0 mt-2 w-80 bg-white text-gray-800 rounded-md shadow-lg"
          >
            <div class="p-4 border-b border-gray-200 font-semibold">
              Chats
            </div>
            <ul class="max-h-60 overflow-auto">
              <!-- Example chat items; replace or dynamically load your data -->
              <li class="px-4 py-2 hover:bg-gray-100 flex items-start space-x-2">
                <i class="fa-solid fa-user text-blue-600 mt-1"></i>
                <div>
                  <p class="font-medium">John Smith</p>
                  <p class="text-sm text-gray-500">Hey, can we discuss the new policy updates?</p>
                </div>
              </li>
              <li class="px-4 py-2 hover:bg-gray-100 flex items-start space-x-2">
                <i class="fa-solid fa-user text-purple-600 mt-1"></i>
                <div>
                  <p class="font-medium">Jane Doe</p>
                  <p class="text-sm text-gray-500">Meeting scheduled for tomorrow at 10 AM</p>
                </div>
              </li>
              <!-- Add more chat previews here -->
            </ul>
            <div class="p-2 text-center border-t border-gray-200">
              <a
                href="/chats"
                class="block w-full text-blue-600 hover:underline"
              >
                View All
              </a>
            </div>
          </div>
        </div>

        <!-- Profile Menu -->
        <div class="relative">
          <button
            class="flex items-center space-x-2 focus:outline-none hover:text-blue-200 transition-colors"
            onclick="toggleDropdown('profileDropdown')"
          >
            <span class="hidden sm:inline">
              Hi, <?= htmlspecialchars($loggedInUser['first_name'] ?? 'User') ?>
            </span>
            <i class="fa-solid fa-circle-user text-2xl"></i>
          </button>

          <!-- Profile Dropdown -->
          <div
            id="profileDropdown"
            data-dropdown
            class="hidden absolute right-0 mt-2 w-72 bg-white text-gray-800 rounded-md shadow-lg overflow-hidden"
          >
            <!-- User & Employee Details -->
            <div class="p-4 border-b border-gray-200">
              <div class="flex items-center space-x-2 mb-2">
                <i class="fa-solid fa-user text-blue-600"></i>
                <span class="font-semibold">
                  <?= htmlspecialchars($loggedInUser['first_name'] ?? 'John') ?>
                  <?= htmlspecialchars($loggedInUser['last_name'] ?? 'Doe') ?>
                </span>
              </div>
              <div class="flex items-center space-x-2">
                <i class="fa-solid fa-id-badge text-green-600"></i>
                <!-- Example Employee ID / Department -->
                <span class="text-sm text-gray-600">Employee ID: 123456</span>
              </div>
            </div>

            <!-- Actions -->
            <a
              href="/profile"
              class="block px-4 py-2 hover:bg-gray-100 flex items-center space-x-2"
            >
              <i class="fa-solid fa-user"></i>
              <span>My Profile</span>
            </a>
            <a
              href="/tasks"
              class="block px-4 py-2 hover:bg-gray-100 flex items-center space-x-2"
            >
              <i class="fa-solid fa-list-check"></i>
              <span>My Tasks</span>
            </a>
            <a
              href="/settings"
              class="block px-4 py-2 hover:bg-gray-100 flex items-center space-x-2"
            >
              <i class="fa-solid fa-gear"></i>
              <span>Settings</span>
            </a>
            <a
              href="/logout"
              class="block px-4 py-2 hover:bg-gray-100 flex items-center space-x-2"
            >
              <i class="fa-solid fa-right-from-bracket"></i>
              <span>Logout</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </header>


  <!-- Scripts -->
  <script>
    // Close all open dropdowns except for the one just toggled
    function toggleDropdown(dropdownId) {
      // Get all dropdowns
      const dropdowns = document.querySelectorAll('[data-dropdown]');

      dropdowns.forEach((dropdown) => {
        if (dropdown.id !== dropdownId) {
          dropdown.classList.add('hidden');
        }
      });

      // Toggle the requested dropdown
      const targetDropdown = document.getElementById(dropdownId);
      targetDropdown.classList.toggle('hidden');
    }

    // Click outside to close any open dropdown
    document.addEventListener('click', (event) => {
      const isDropdownButton = event.target.closest('button[onclick^="toggleDropdown"]');
      const dropdowns = document.querySelectorAll('[data-dropdown]');

      // If clicked outside a dropdown button or dropdown content, close all
      if (!isDropdownButton) {
        dropdowns.forEach((dropdown) => {
          if (!dropdown.contains(event.target)) {
            dropdown.classList.add('hidden');
          }
        });
      }
    });
  </script>
