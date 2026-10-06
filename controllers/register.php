<?php
require_once __DIR__ . '/bootstrap.php';

$pageTitle = 'Регистрация';
$error = '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $username = trim(postString('username'));
    $password = postString('password');

    if ($username === '' || $password === '') {
        $error = 'Заполните все поля.';
    } elseif (mb_strlen($username, 'UTF-8') < 3 || mb_strlen($username, 'UTF-8') > 50) {
        $error = 'Логин должен содержать от 3 до 50 символов.';
    } elseif (mb_strlen($password, 'UTF-8') < 6) {
        $error = 'Пароль должен содержать не менее 6 символов.';
    } elseif ((new User($pdo))->findByUsername($username)) {
        $error = 'Такой пользователь уже существует.';
    } else {
        try {
            (new User($pdo))->create($username, $password);
        } catch (PDOException $e) {
            if ($e->getCode() !== '23000') {
                throw $e;
            }
            $error = 'Такой пользователь уже существует.';
        }
        if ($error === '') {
            header('Location: login.php?registered=1');
            exit;
        }
    }
}
require dirname(__DIR__) . '/views/register.php';
