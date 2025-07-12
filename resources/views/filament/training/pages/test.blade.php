<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6 font-sans">

  <!-- Container -->
  <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg">

    <!-- Header -->
    <div class="mb-4 flex justify-between items-center">
      <h1 class="text-xl font-semibold">Dashboard</h1>
    </div>

    <!-- Greeting and Date -->
    <div class="mb-6">
      <h2 class="text-lg font-semibold mb-1">Welcome to Stasiun Belajar E-Training System, Teacher</h2>
      <p class="text-sm text-gray-500">Thursday, 16 Jan 2025</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 gap-4 mb-6">
      <div class="bg-red-100 p-4 rounded-lg flex items-center space-x-4">
        <!-- Book Icon -->
        <div class="flex-shrink-0">
          <!-- Heroicon: Book -->
          <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M12 4H3v16h9m0-16v16"></path>
          </svg>
        </div>
        <div>
          <div class="text-xl font-semibold">0</div>
          <div class="text-sm text-gray-600">Total Finished Courses</div>
        </div>
      </div>
      <div class="bg-yellow-100 p-4 rounded-lg flex items-center space-x-4">
        <!-- Clipboard Icon -->
        <div class="flex-shrink-0">
          <!-- Heroicon: Clipboard List -->
          <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m-9-8h.01M4 6h16M4 6a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"></path>
          </svg>
        </div>
        <div>
          <div class="text-xl font-semibold">0</div>
          <div class="text-sm text-gray-600">Total Passed Quizzes</div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-2 gap-4">
      <!-- Left Card -->
      <div class="bg-purple-100 p-4 rounded-lg flex flex-col justify-between h-48 relative">
        <div>
          <h3 class="font-semibold mb-2">Tailwind CSS</h3>
          <p class="text-sm text-gray-700 mb-4">Chapter 1. Installation</p>
        </div>
        <!-- Continue Button -->
        <button class="bg-pink-400 text-white px-4 py-2 rounded-lg self-start hover:bg-pink-500 transition">Continue</button>
      </div>
      <!-- Right Card -->
      <div class="bg-blue-100 p-4 rounded-lg flex items-center justify-center h-48">
        <!-- Heroicon: Video Camera -->
        <svg class="w-12 h-12 text-blue-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
          <path d="M15.5 8.25V5a1 1 0 00-1-1h-9a1 1 0 00-1 1v10a1 1 0 001 1h9a1 1 0 001-1v-3.25l4 2v-4l-4 2z" />
        </svg>
        <!-- Link Button -->
        <a href="#" class="ml-4 bg-blue-300 text-white px-4 py-2 rounded-lg hover:bg-blue-400 transition">Link</a>
      </div>
    </div>

  </div>

</body>
</html>