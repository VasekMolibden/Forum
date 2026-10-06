<?php
require 'config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Удалять тему можно только через форму на сайте.');
}
verifyCsrfToken();

$topicId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($topicId === false || $topicId === null || $topicId < 1) {
    http_response_code(400);
    exit('Некорректный номер темы.');
}

$stmt = $pdo->prepare('SELECT id, user_id FROM topics WHERE id = ?');
$stmt->execute([$topicId]);
$topic = $stmt->fetch();
if (!$topic) {
    setFlash('warning', 'Тема уже удалена или не найдена.');
    header('Location: index.php');
    exit;
}

// Автор может удалить свою тему, а модератор — любую; проверяем право на сервере.
if (!isModerator() && (int)$_SESSION['user_id'] !== (int)$topic['user_id']) {
    http_response_code(403);
    exit('Удалить тему может только её автор или модератор.');
}

// В таблице posts включено каскадное удаление ответов вместе с темой.
$stmt = $pdo->prepare('DELETE FROM topics WHERE id = ?');
$stmt->execute([$topicId]);
setFlash('success', 'Тема удалена.');
header('Location: index.php');
exit;
