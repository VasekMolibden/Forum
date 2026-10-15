<?php
require_once __DIR__ . '/bootstrap.php';

$pageTitle = 'Вход';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $username = trim(postString('username'));
    $password = postString('password');
    $user = (new User($pdo))->findByUsername($username);

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        unset($_SESSION['csrf_token']);
        header('Location: index.php');
        exit;
    }
    $error = 'Неверный логин или пароль.';
}
require dirname(__DIR__) . '/views/login.php';
