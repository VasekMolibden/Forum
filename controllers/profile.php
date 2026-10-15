<?php
require_once __DIR__ . '/bootstrap.php';

$requestedId = requestId();
$userId = $requestedId ?: (isLoggedIn() ? (int)$_SESSION['user_id'] : 0);
if ($userId < 1) {
    header('Location: login.php');
    exit;
}

$userModel = new User($pdo);
$profile = $userModel->find($userId);
if (!$profile) {
    renderError(404, 'Пользователь не найден.');
}

$isOwnProfile = isLoggedIn() && (int)$_SESSION['user_id'] === $userId;
$messageCount = $userModel->messageCount($userId);
$topicModel = new Topic($pdo);
$totalTopics = $topicModel->countByUser($userId);
$totalPages = max(1, (int)ceil($totalTopics / PAGE_SIZE));
$page = min(requestPage(), $totalPages);
$topics = $topicModel->paginateByUser($userId, PAGE_SIZE, ($page - 1) * PAGE_SIZE);
$pageTitle = 'Профиль: ' . $profile['username'];
require dirname(__DIR__) . '/views/profile.php';
