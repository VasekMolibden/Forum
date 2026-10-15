<?php
require_once __DIR__ . '/bootstrap.php';
requireAdmin();

$topicModel = new Topic($pdo);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $topicId = filter_var(postString('topic_id'), FILTER_VALIDATE_INT);
    if ($topicId === false || $topicId < 1) {
        renderError(400, 'Некорректная тема.');
    }
    if (!$topicModel->delete($topicId)) {
        setFlash('danger', 'Тема не найдена или уже удалена.');
    } else {
        setFlash('success', 'Тема и все ответы удалены.');
    }
    header('Location: admin_topics.php');
    exit;
}

$totalTopics = $topicModel->count();
$totalPages = max(1, (int)ceil($totalTopics / PAGE_SIZE));
$page = min(requestPage(), $totalPages);
$topics = $topicModel->paginate(PAGE_SIZE, ($page - 1) * PAGE_SIZE);
$pageTitle = 'Управление темами';
require dirname(__DIR__) . '/views/admin_topics.php';
