<?php
$title = 'Dashboard';

// Content to inject into the layout
ob_start();
?>

<h1 class="text-2xl font-bold text-gray-800 mb-4">Welcome to the Dashboard</h1>

<p class="text-gray-700">
  <strong>Email:</strong> <?= htmlspecialchars($userDetails['email'] ?? '') ?>
</p>

<?php if (!empty($userDetails['first_name']) && !empty($userDetails['last_name'])): ?>
  <p class="text-gray-700">
    <strong>Employee Name:</strong> 
    <?= htmlspecialchars($userDetails['first_name']) ?> 
    <?= htmlspecialchars($userDetails['middle_name'] ?? '') ?> 
    <?= htmlspecialchars($userDetails['last_name']) ?>
  </p>
<?php else: ?>
  <p class="text-gray-700">
    <strong>Employee Name:</strong> Not linked
  </p>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
