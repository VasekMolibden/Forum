<?php
require_once __DIR__ . '/bootstrap.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    renderError(405, 'Удалять сообщение можно только через форму на сайте.');
}
verifyCsrfToken();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$postModel = new Post($pdo);
$post = $id !== false && $id !== null && $id > 0 ? $postModel->find($id) : null;
if (!$post) {
    setFlash('warning', 'Сообщение не найдено.');
    header('Location: index.php');
    exit;
}
if (!canManage((int)$post['user_id'])) {
    renderError(403, 'Удалить сообщение может только его автор или модератор.');
}

$postModel->delete((int)$post['id']);
setFlash('success', 'Сообщение удалено.');
header('Location: topic.php?id=' . (int)$post['topic_id']);
exit;
