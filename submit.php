<?php

require 'db.php';

$message = trim($_POST['message'] ?? '');

if ($message === '') {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare(
    'INSERT INTO confessions (message) VALUES (:message)'
);

$stmt->execute([
    ':message' => $message
]);

header('Location: index.php');
exit;