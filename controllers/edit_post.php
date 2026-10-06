<?php
require_once __DIR__ . '/bootstrap.php';
requireLogin();

$id = requestId();
$postModel = new Post($pdo);
$post = $id > 0 ? $postModel->find($id) : null;
if (!$post) {
    renderError(404, 'Сообщение не найдено.');
}
if (!canManage((int)$post['user_id'])) {
    renderError(403, 'Изменить сообщение может только его автор или модератор.');
}

$error = '';
$content = $post['content'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $content = trim(postString('content'));
    $error = validatePostContent($content) ?? '';
    if ($error === '') {
        $postModel->update($id, $content);
        setFlash('success', 'Сообщение изменено.');
        header('Location: topic.php?id=' . (int)$post['topic_id']);
        exit;
    }
}
$pageTitle = 'Изменить сообщение';
require dirname(__DIR__) . '/views/edit_post.php';
