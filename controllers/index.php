<?php
require_once __DIR__ . '/bootstrap.php';

$topicModel = new Topic($pdo);
$totalTopics = $topicModel->count();
$totalPages = max(1, (int)ceil($totalTopics / PAGE_SIZE));
$page = min(requestPage(), $totalPages);
$topics = $topicModel->paginate(PAGE_SIZE, ($page - 1) * PAGE_SIZE);
$pageTitle = 'Темы форума';
require dirname(__DIR__) . '/views/index.php';
