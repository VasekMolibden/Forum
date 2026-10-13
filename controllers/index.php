<?php
require_once __DIR__ . '/bootstrap.php';

$topicModel = new Topic($pdo);
$categoryModel = new Category($pdo);
$categories = $categoryModel->all();
$rawSearch = $_GET['q'] ?? '';
if (!is_string($rawSearch)) {
    renderError(400, 'Некорректный поисковый запрос.');
}
$search = mb_substr(trim($rawSearch), 0, 100, 'UTF-8');

$rawCategory = $_GET['category'] ?? '';
if (!is_string($rawCategory)) {
    renderError(400, 'Некорректная категория.');
}
$categoryId = null;
if ($rawCategory !== '') {
    $validatedCategory = filter_var($rawCategory, FILTER_VALIDATE_INT);
    if ($validatedCategory === false || $validatedCategory < 0) {
        renderError(400, 'Некорректная категория.');
    }
    $categoryId = (int)$validatedCategory;
    if ($categoryId > 0 && !$categoryModel->exists($categoryId)) {
        renderError(404, 'Категория не найдена.');
    }
}

$rawSort = $_GET['sort'] ?? 'newest';
if (!is_string($rawSort)) {
    renderError(400, 'Некорректная сортировка.');
}
$sort = in_array($rawSort, ['newest', 'activity', 'replies'], true) ? $rawSort : 'newest';
$totalTopics = $topicModel->countFiltered($search, $categoryId);
$totalPages = max(1, (int)ceil($totalTopics / PAGE_SIZE));
$page = min(requestPage(), $totalPages);
$topics = $topicModel->paginateFiltered(PAGE_SIZE, ($page - 1) * PAGE_SIZE, $search, $categoryId, $sort);
$paginationParams = array_filter([
    'q' => $search,
    'category' => $categoryId,
    'sort' => $sort !== 'newest' ? $sort : null,
], static fn($value): bool => $value !== null && $value !== '');
$paginationUrl = 'index.php' . ($paginationParams ? '?' . http_build_query($paginationParams) : '');
$pageTitle = 'Темы форума';
require dirname(__DIR__) . '/views/index.php';
