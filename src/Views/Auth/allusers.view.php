<?php
$title = 'User Management';

// Content to inject into the layout
ob_start();
?>

<h1 class="text-2xl font-bold text-gray-800 mb-4">User Management</h1>

<table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-lg">
  <thead>
    <tr class="bg-blue-800 text-white">
      <th class="py-2 px-4 text-left">User ID</th>
      <th class="py-2 px-4 text-left">Email</th>
      <th class="py-2 px-4 text-left">Employee Name</th>
      <th class="py-2 px-4 text-left">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($allUsers)): ?>
      <?php foreach ($allUsers as $user): ?>
        <tr class="border-t">
          <td class="py-2 px-4"><?= htmlspecialchars($user['id']) ?></td>
          <td class="py-2 px-4"><?= htmlspecialchars($user['email']) ?></td>
          <td class="py-2 px-4">
            <?= !empty($user['first_name']) && !empty($user['last_name']) 
                ? htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) 
                : 'Not linked' ?>
          </td>
          <td class="py-2 px-4">
            <a href="/edit-user/<?= htmlspecialchars($user['id']) ?>" 
               class="text-blue-600 hover:underline">Edit</a>
            |
            <a href="/delete-user?id=<?= htmlspecialchars($user['id']) ?>" 
               class="text-red-600 hover:underline" 
               onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr>
        <td colspan="4" class="text-center py-4">No users found.</td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
