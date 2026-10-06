<?php
require_once __DIR__ . '/bootstrap.php';
requireLogin();

$pageTitle = 'Новая тема';
$error = '';
$title = '';
$body = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $title = trim(postString('title'));
    $body = trim(postString('body'));
    $error = validateTopicTitle($title) ?? validateTopicBody($body) ?? '';
    if ($error === '') {
        $id = (new Topic($pdo))->create((int)$_SESSION['user_id'], $title, $body);
        header('Location: topic.php?id=' . $id);
        exit;
    }
}
require dirname(__DIR__) . '/views/create_topic.php';
