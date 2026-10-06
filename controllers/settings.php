<?php
require_once __DIR__ . '/bootstrap.php';
requireLogin();

$userId = (int)$_SESSION['user_id'];
$userModel = new User($pdo);
$profile = $userModel->find($userId);
if (!$profile) {
    renderError(404, 'Пользователь не найден.');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $currentPassword = postString('current_password');
    $newPassword = postString('new_password');
    $confirmPassword = postString('confirm_password');
    if (!$userModel->verifyPassword($userId, $currentPassword)) {
        $error = 'Текущий пароль указан неверно.';
    } elseif (mb_strlen($newPassword, 'UTF-8') < 6) {
        $error = 'Новый пароль должен содержать не менее 6 символов.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Новые пароли не совпадают.';
    } else {
        $userModel->updatePassword($userId, $newPassword);
        setFlash('success', 'Пароль изменён.');
        header('Location: profile.php');
        exit;
    }
}

$pageTitle = 'Настройки профиля';
require dirname(__DIR__) . '/views/settings.php';
