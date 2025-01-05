<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Enterprise HRMS - <?= htmlspecialchars($title ?? 'Dashboard') ?></title>

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Font Awesome 6 (for icons) -->
  <link 
    rel="stylesheet" 
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
  />
</head>
<body class="select-none bg-gray-100 min-h-screen">
<header class="bg-blue-800 text-white shadow-md">
  <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
    
    <!-- Branding -->
    <div class="flex items-center space-x-4">
      <a href="/dashboard" class="text-2xl font-bold hover:text-blue-300">HRMS</a>
    </div>

    <!-- Navigation Links (hidden on mobile) -->
    <nav class="hidden md:flex space-x-6">
      <a href="/dashboard" class="hover:text-blue-300">Dashboard</a>
      <a href="/profile" class="hover:text-blue-300">Profile</a>
      <a href="/settings" class="hover:text-blue-300">Settings</a>
    </nav>

    <!-- Profile Dropdown -->
    <div class="relative">
      <button 
        class="flex items-center space-x-2 focus:outline-none hover:text-blue-300" 
        onclick="toggleDropdown()"
      >
        <span class="hidden sm:inline">
          Hi, <?= htmlspecialchars($loggedInUser['first_name'] ?? 'User') ?>
        </span>
        <i class="fa-solid fa-user-circle text-2xl"></i>
      </button>

      <!-- Dropdown Menu -->
      <div 
        id="profileDropdown" 
        class="absolute right-0 mt-2 bg-white text-gray-800 rounded shadow-lg hidden w-48"
      >
        <a href="/profile" class="block px-4 py-2 hover:bg-gray-100">Profile</a>
        <a href="/settings" class="block px-4 py-2 hover:bg-gray-100">Settings</a>
        <a href="/logout" class="block px-4 py-2 hover:bg-gray-100">Logout</a>
      </div>
    </div>
  </div>
</header>


<script>
  function toggleDropdown() {
    const dropdown = document.getElementById('profileDropdown');
    dropdown.classList.toggle('hidden');
  }

  document.addEventListener('click', (event) => {
    const dropdown = document.getElementById('profileDropdown');
    const button = event.target.closest('button');

    if (!button || !dropdown.contains(event.target)) {
      dropdown.classList.add('hidden');
    }
  });
</script>
