<?php
require_once __DIR__ . '/bootstrap.php';
requireAdmin();

$userModel = new User($pdo);
$topicModel = new Topic($pdo);
$categoryModel = new Category($pdo);
$userCount = $userModel->count();
$topicCount = $topicModel->count();
$categoryCount = count($categoryModel->all());
$pageTitle = 'Административная панель';
require dirname(__DIR__) . '/views/admin.php';
