<?php

$pdo = new PDO(
    'mysql:host=localhost;dbname=confession_wall;charset=utf8mb4',
    'root',
    ''
);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

define('ADMIN_HASH', 'PUT_YOUR_PASSWORD_HASH_HERE');