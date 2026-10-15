<?php
require_once __DIR__ . '/bootstrap.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    renderError(405, 'Удалять тему можно только через форму на сайте.');
}
verifyCsrfToken();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$topic = $id !== false && $id !== null && $id > 0 ? (new Topic($pdo))->find($id) : null;
if (!$topic) {
    setFlash('warning', 'Тема не найдена.');
    header('Location: index.php');
    exit;
}
if (!canManage((int)$topic['user_id'])) {
    renderError(403, 'Удалить тему может только её автор или модератор.');
}

(new Topic($pdo))->delete((int)$topic['id']);
setFlash('success', 'Тема удалена.');
header('Location: index.php');
exit;
