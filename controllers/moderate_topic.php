<?php
require_once __DIR__ . '/bootstrap.php';
requireLogin();
if (!isModerator()) {
    renderError(403, 'Закреплять и закрывать темы могут только модераторы и администраторы.');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    renderError(405, 'Используйте форму управления темой.');
}

verifyCsrfToken();
$topicId = filter_var(postString('topic_id'), FILTER_VALIDATE_INT);
if ($topicId === false || $topicId < 1) {
    renderError(400, 'Некорректная тема.');
}
$topicModel = new Topic($pdo);
$topic = $topicModel->find((int)$topicId);
if (!$topic) {
    renderError(404, 'Тема не найдена.');
}

$action = postString('action');
if ($action === 'toggle_pin') {
    $topicModel->setPinned((int)$topicId, (int)$topic['is_pinned'] !== 1);
    setFlash('success', (int)$topic['is_pinned'] === 1 ? 'Тема откреплена.' : 'Тема закреплена.');
} elseif ($action === 'toggle_closed') {
    $topicModel->setClosed((int)$topicId, (int)$topic['is_closed'] !== 1);
    setFlash('success', (int)$topic['is_closed'] === 1 ? 'Тема открыта для ответов.' : 'Тема закрыта для новых ответов.');
} else {
    renderError(400, 'Неизвестное действие.');
}

$returnTo = postString('return_to');
if ($returnTo === 'admin_topics' && isAdmin()) {
    header('Location: admin_topics.php');
} else {
    header('Location: topic.php?id=' . (int)$topicId);
}
exit;
