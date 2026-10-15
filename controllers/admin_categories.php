<?php
require_once __DIR__ . '/bootstrap.php';
requireAdmin();

$categoryModel = new Category($pdo);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = postString('action');
    if ($action === 'create' || $action === 'rename') {
        $name = trim(postString('name'));
        $categoryId = null;
        if ($action === 'rename') {
            $parsedId = filter_var(postString('category_id'), FILTER_VALIDATE_INT);
            if ($parsedId === false || $parsedId < 1) {
                renderError(400, 'Некорректная категория.');
            }
            $categoryId = (int)$parsedId;
            if (!$categoryModel->exists($categoryId)) {
                setFlash('danger', 'Категория не найдена.');
                header('Location: admin_categories.php');
                exit;
            }
        }
        if ($name === '' || mb_strlen($name, 'UTF-8') > 100) {
            setFlash('danger', 'Название категории обязательно и не должно превышать 100 символов.');
        } else {
            try {
                if ($action === 'create') {
                    $categoryModel->create($name);
                } else {
                    $categoryModel->rename($categoryId, $name);
                }
                setFlash('success', $action === 'create' ? 'Категория создана.' : 'Категория переименована.');
            } catch (PDOException $e) {
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
                setFlash('danger', 'Категория с таким названием уже существует.');
            }
        }
        header('Location: admin_categories.php');
        exit;
    }

    if ($action === 'delete') {
        $categoryId = filter_var(postString('category_id'), FILTER_VALIDATE_INT);
        if ($categoryId === false || $categoryId < 1) {
            renderError(400, 'Некорректная категория.');
        }
        $result = $categoryModel->deleteIfEmpty((int)$categoryId);
        if ($result === 'not_empty') {
            setFlash('danger', 'Нельзя удалить категорию, пока в ней есть темы. Сначала перенесите их.');
        } elseif ($result === 'not_found') {
            setFlash('danger', 'Категория не найдена.');
        } else {
            setFlash('success', 'Категория удалена.');
        }
        header('Location: admin_categories.php');
        exit;
    }

    renderError(400, 'Неизвестное действие.');
}

$categories = $categoryModel->allWithTopicCounts();
$pageTitle = 'Управление категориями';
require dirname(__DIR__) . '/views/admin_categories.php';
