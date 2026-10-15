<?php
require_once __DIR__ . '/bootstrap.php';
requireLogin();

$pageTitle = 'Новая тема';
$categoryModel = new Category($pdo);
$categories = $categoryModel->all();
$error = '';
$title = '';
$body = '';
$selectedCategory = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $title = trim(postString('title'));
    $body = trim(postString('body'));
    $selectedCategory = postString('category_id');
    $categoryId = null;
    if ($selectedCategory !== '') {
        $parsedCategoryId = filter_var($selectedCategory, FILTER_VALIDATE_INT);
        if ($parsedCategoryId === false || $parsedCategoryId < 1 || !$categoryModel->exists((int)$parsedCategoryId)) {
            $error = 'Выбранная категория не существует.';
        } else {
            $categoryId = (int)$parsedCategoryId;
        }
    }
    if ($error === '') {
        $error = validateTopicTitle($title) ?? validateTopicBody($body) ?? '';
    }
    if ($error === '') {
        $id = (new Topic($pdo))->create((int)$_SESSION['user_id'], $title, $body, $categoryId);
        header('Location: topic.php?id=' . $id);
        exit;
    }
}
require dirname(__DIR__) . '/views/create_topic.php';
