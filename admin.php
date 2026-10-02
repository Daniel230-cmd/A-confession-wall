<?php
session_start();
require 'db.php';

$_SESSION['csrf'] ??= bin2hex(random_bytes(16));
$e = fn(string $s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        if (password_verify($_POST['password'] ?? '', ADMIN_HASH)) {
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            $_SESSION['csrf']  = bin2hex(random_bytes(16));
            header('Location: admin.php');
            exit;
        }
        sleep(1); // slow down guessing
        $error = 'Wrong password.';
    }

    if (!empty($_SESSION['admin']) && hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        if ($action === 'delete') {
            $pdo->prepare('DELETE FROM confessions WHERE id = :id')
                ->execute([':id' => (int)($_POST['id'] ?? 0)]);
            header('Location: admin.php?deleted=1');
            exit;
        }
        if ($action === 'logout') {
            $_SESSION = [];
            session_destroy();
            header('Location: admin.php');
            exit;
        }
    }
}

$isAdmin = !empty($_SESSION['admin']);
$rows = $isAdmin
    ? $pdo->query('SELECT id, message, created_at FROM confessions ORDER BY id DESC LIMIT 300')->fetchAll()
    : [];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Admin · The Wall</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="admin">

<?php if (!$isAdmin): ?>
  <header>
    <h1>Admin login</h1>
    <form method="post">
      <input type="hidden" name="action" value="login">
      <label for="pw" class="sr">Password</label>
      <input id="pw" type="password" name="password" placeholder="Password" required autofocus>
      <div class="row"><span></span><button type="submit">Log in</button></div>
    </form>
    <?php if ($error): ?><p class="flash warn" role="alert"><?= $e($error) ?></p><?php endif; ?>
  </header>

<?php else: ?>
  <header class="admin-bar">
    <h1>Moderation (<?= count($rows) ?>)</h1>
    <form method="post">
      <input type="hidden" name="action" value="logout">
      <input type="hidden" name="csrf" value="<?= $e($_SESSION['csrf']) ?>">
      <button type="submit" class="ghost">Log out</button>
    </form>
  </header>

  <?php if (isset($_GET['deleted'])): ?>
    <p class="flash good admin-flash" role="status">Confession deleted.</p>
  <?php endif; ?>

  <main class="admin-list">
    <?php if (!$rows): ?><p class="empty">Nothing to moderate.</p><?php endif; ?>
    <?php foreach ($rows as $r): ?>
      <article class="admin-row">
        <div>
          <p><?= nl2br($e($r['message'])) ?></p>
          <time>#<?= (int)$r['id'] ?> · <?= $e($r['created_at']) ?></time>
        </div>
        <form method="post" onsubmit="return confirm('Delete confession #<?= (int)$r['id'] ?>?')">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
          <input type="hidden" name="csrf" value="<?= $e($_SESSION['csrf']) ?>">
          <button type="submit" class="danger">Delete</button>
        </form>
      </article>
    <?php endforeach; ?>
  </main>
<?php endif; ?>

</body>
</html>