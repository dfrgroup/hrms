<?php
$title = 'Edit User';

// Content to inject into the layout
ob_start();
?>

<h1 class="text-2xl font-bold text-gray-800 mb-4">View User</h1>


<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
