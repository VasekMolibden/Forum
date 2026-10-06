<?php
require_once __DIR__ . '/bootstrap.php';

$id = requestId();
$topicModel = new Topic($pdo);
$postModel = new Post($pdo);
$topic = $id > 0 ? $topicModel->find($id) : null;
if (!$topic) {
    renderError(404, 'Тема не найдена.');
}

$error = '';
$content = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    verifyCsrfToken();
    $content = trim(postString('content'));
    $error = validatePostContent($content) ?? '';
    if ($error === '') {
        $postModel->create($id, (int)$_SESSION['user_id'], $content);
        header('Location: topic.php?id=' . $id);
        exit;
    }
}

$totalPosts = $postModel->countForTopic($id);
$totalPages = max(1, (int)ceil($totalPosts / PAGE_SIZE));
$page = min(requestPage(), $totalPages);
$posts = $postModel->paginateForTopic($id, PAGE_SIZE, ($page - 1) * PAGE_SIZE);
$pageTitle = $topic['title'];
require dirname(__DIR__) . '/views/topic.php';
