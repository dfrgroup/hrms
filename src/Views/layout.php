<?php
require __DIR__ . '/partials/Header.php';
?>

<div class="flex">
  <?php require __DIR__ . '/partials/Sidebar.php'; ?>

  <main class="flex-1 p-6 ml-64">
    <?= $content ?? '' ?>
  </main>
</div>

</body>
</html>
