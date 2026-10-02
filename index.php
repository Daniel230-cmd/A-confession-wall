<?php require 'db.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Anonymous Confession Wall</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <h1>🕵️ Anonymous Confession Wall</h1>

    <form action="submit.php" method="POST">
        <textarea name="message"
                  placeholder="Type your confession..."
                  required></textarea>

        <button type="submit">
            Post Anonymously
        </button>
    </form>

    <h2>Recent Confessions</h2>

    <?php
   $result = $pdo->query(
    "SELECT * FROM confessions ORDER BY created_at DESC"
    );

    while($row = $result->fetch()) {
        echo "
        <div class='card'>
            <p>" . htmlspecialchars($row['message']) . "</p>
            <small>" . $row['created_at'] . "</small>
        </div>
        ";
    }
    ?>

</div>

</body>
</html>