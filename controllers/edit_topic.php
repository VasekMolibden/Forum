<?php
require_once __DIR__ . '/bootstrap.php';
requireLogin();

$id = requestId();
$topicModel = new Topic($pdo);
$topic = $id > 0 ? $topicModel->find($id) : null;
if (!$topic) {
    renderError(404, 'Тема не найдена.');
}
if (!canManage((int)$topic['user_id'])) {
    renderError(403, 'Изменить тему может только её автор или модератор.');
}

$error = '';
$title = $topic['title'];
$body = $topic['body'];
$categoryModel = new Category($pdo);
$categories = $categoryModel->all();
$selectedCategory = $topic['category_id'] === null ? '' : (string)$topic['category_id'];
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
        $topicModel->update($id, $title, $body, $categoryId);
        setFlash('success', 'Тема изменена.');
        header('Location: topic.php?id=' . $id);
        exit;
    }
}
$pageTitle = 'Изменить тему';
require dirname(__DIR__) . '/views/edit_topic.php';
