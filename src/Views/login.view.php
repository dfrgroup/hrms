<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Enterprise HRMS - Login</title>

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Font Awesome 6 (for icons) -->
  <link 
    rel="stylesheet" 
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
  />
</head>
<body class="select-none bg-gradient-to-br from-blue-100 to-indigo-50 min-h-screen">

  <!-- Optional: server-side error passed into JS -->
  <?php 
    // $errorMessage = "Invalid credentials. Please try again.";
  ?>

  <!-- Outer container -->
  <div class="container mx-auto px-4 sm:px-6 md:px-8 py-6 sm:py-10 md:py-16 flex flex-col items-center justify-center min-h-screen">
    
    <!-- Main Card -->
    <div class="w-full max-w-6xl bg-white rounded-lg shadow-2xl overflow-hidden flex flex-col lg:flex-row">
      
      <!-- Left: Announcements Section (hidden below lg) -->
      <div class="hidden lg:flex lg:flex-col lg:justify-center lg:w-7/12 bg-gradient-to-br from-blue-700 to-indigo-700 text-white p-8 lg:p-10">
        <h2 class="text-2xl lg:text-3xl font-bold mb-6">Company News &amp; Announcements</h2>
        
        <ul class="space-y-5 text-gray-100 text-sm lg:text-base">
          <li class="border-l-4 border-white pl-3">
            <strong class="block text-base lg:text-lg">Jan 2025 Payroll Cycle</strong>
            Submit all timecards before Jan 25 for on-time processing.
          </li>
          <li class="border-l-4 border-white pl-3">
            <strong class="block text-base lg:text-lg">Upcoming Training</strong>
            Mandatory cybersecurity training on Feb 10.
          </li>
          <li class="border-l-4 border-white pl-3">
            <strong class="block text-base lg:text-lg">Office Renovation</strong>
            Construction on 3rd floor starts next month. Expect noise.
          </li>
        </ul>

        <div class="mt-8">
          <h3 class="text-xl font-semibold mb-2">Upcoming Holidays</h3>
          <p class="text-sm lg:text-base">Presidents' Day: Feb 17</p>
        </div>
      </div>

      <!-- Right: Login Section -->
      <div class="w-full lg:w-5/12 flex flex-col justify-center p-6 sm:p-8 lg:p-10">
        
        <!-- Logo/Title -->
        <div class="text-center mb-8">
          <h1 class="text-3xl sm:text-4xl font-extrabold text-blue-800">Enterprise HRMS</h1>
          <p class="text-gray-600 text-base sm:text-lg mt-1">Sign in to access your account</p>
        </div>
        <!-- Error Alert -->
    <div 
      id="errorAlert" 
      class="hidden w-full mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
      role="alert"
    >
      <strong class="font-bold">Error:</strong>
      <span class="block sm:inline" id="errorMessageText">Something went wrong.</span>
      <span 
        class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer"
        onclick="closeErrorAlert()"
      >
        <i class="fa-solid fa-xmark"></i>
      </span>
    </div>


        <!-- Login Form -->
        <form 
          class="space-y-6"
          action="/login" 
          method="POST" 
          novalidate
        >
          <!-- Email -->
          <div>
            <label for="email" class="block text-gray-700 mb-2">Email Address</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                <i class="fa-solid fa-envelope"></i>
              </span>
              <input 
                id="email" 
                name="email" 
                type="email" 
                required 
                class="pl-10 pr-3 py-2 w-full border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="you@example.com"
              />
            </div>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-gray-700 mb-2">Password</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                <i class="fa-solid fa-lock"></i>
              </span>
              <input 
                id="password" 
                name="password" 
                type="password" 
                required 
                class="pl-10 pr-10 py-2 w-full border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="••••••••"
              />
              <!-- Toggle Password Visibility -->
              <span 
                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 cursor-pointer hover:text-blue-600"
                onclick="togglePassword()"
              >
                <i class="fa-solid fa-eye" id="toggleEye"></i>
              </span>
            </div>
          </div>

          <!-- Submit -->
          <button 
            type="submit" 
            class="w-full py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700 transition-colors text-lg"
          >
            <i class="fa-solid fa-right-to-bracket mr-2"></i> Sign In
          </button>

          <!-- Bottom Links -->
          <div class="flex justify-between items-center text-sm">
            <a 
              href="#" 
              class="text-blue-600 hover:underline"
              onclick="openModal()"
            >Trouble logging in?</a>
            <a href="#" class="text-blue-600 hover:underline">Create Account</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Overlay -->
  <div 
    id="modalOverlay" 
    class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex justify-center items-center"
    onclick="closeModalByOverlay(event)"
  >
    <!-- Modal Content -->
    <div 
      id="modalContent"
      class="relative bg-white w-11/12 max-w-md p-6 sm:p-8 rounded-lg shadow-xl"
      onclick="event.stopPropagation()"
    >
      <!-- Close Button -->
      <button 
        class="absolute top-4 right-4 text-gray-600 hover:text-gray-800"
        onclick="closeModal()"
      >
        <i class="fa-solid fa-xmark text-2xl"></i>
      </button>

      <h2 class="text-2xl font-bold mb-3 text-blue-700">Trouble Logging In?</h2>
      <p class="text-gray-700 text-sm sm:text-base mb-4">
        If you're having issues accessing your account, you can try the following:
      </p>
      <ul class="list-disc list-inside text-gray-600 space-y-2 pl-2">
        <li>Reset your password <a href="#" class="text-blue-600 hover:underline">here</a>.</li>
        <li>Make sure Caps Lock is off.</li>
        <li>Contact the IT help desk if your account is locked.</li>
      </ul>
      <p class="text-sm text-gray-500 mt-4">
        For further assistance, reach out to 
        <a href="#" class="text-blue-600 hover:underline">HR Support</a>.
      </p>
    </div>
  </div>

  <!-- Inline JS -->
  <script>
    // If there's a server-side error
    const errorMessage = <?php echo isset($errorMessage) ? json_encode($errorMessage) : 'null'; ?>;
    if (errorMessage) {
      document.getElementById('errorMessageText').textContent = errorMessage;
      document.getElementById('errorAlert').classList.remove('hidden');
    }

    function closeErrorAlert() {
      document.getElementById('errorAlert').classList.add('hidden');
    }

    function togglePassword() {
      const pwdInput = document.getElementById('password');
      const toggleIcon = document.getElementById('toggleEye');
      const isPassword = pwdInput.type === 'password';
      
      pwdInput.type = isPassword ? 'text' : 'password';
      toggleIcon.classList.toggle('fa-eye');
      toggleIcon.classList.toggle('fa-eye-slash');
    }

    function openModal() {
      document.getElementById('modalOverlay').classList.remove('hidden');
      document.body.classList.add('overflow-hidden'); // Stop scrolling on background
    }

    function closeModal() {
      document.getElementById('modalOverlay').classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    }

    function closeModalByOverlay(event) {
      if (event.target === event.currentTarget) {
        closeModal();
      }
    }
  </script>
</body>
</html>
