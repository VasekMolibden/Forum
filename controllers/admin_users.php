<?php
require_once __DIR__ . '/bootstrap.php';
requireAdmin();

$userModel = new User($pdo);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $targetId = filter_var(postString('user_id'), FILTER_VALIDATE_INT);
    if ($targetId === false || $targetId < 1) {
        renderError(400, 'Некорректный пользователь.');
    }

    $action = postString('action');
    if ($action === 'change_role') {
        $role = postString('role');
        if (!in_array($role, ['user', 'moderator', 'admin'], true)) {
            renderError(400, 'Выбрана неизвестная роль.');
        }

        $result = $userModel->changeRole($targetId, $role);
        if ($result === 'last_admin') {
            setFlash('danger', 'Нельзя понизить роль последнего администратора.');
        } elseif ($result === 'not_found') {
            setFlash('danger', 'Пользователь не найден.');
        } else {
            if ($targetId === (int)$_SESSION['user_id']) {
                $_SESSION['role'] = $role;
            }
            setFlash('success', 'Роль пользователя обновлена.');
        }

        if ($targetId === (int)$_SESSION['user_id'] && $role !== 'admin') {
            header('Location: index.php');
        } else {
            header('Location: admin_users.php');
        }
        exit;
    }

    if ($action === 'delete_user') {
        if ($targetId === (int)$_SESSION['user_id']) {
            setFlash('danger', 'Нельзя удалить собственную учётную запись.');
        } else {
            $result = $userModel->deleteSafely($targetId);
            if ($result === 'last_admin') {
                setFlash('danger', 'Нельзя удалить последнего администратора.');
            } elseif ($result === 'not_found') {
                setFlash('danger', 'Пользователь не найден.');
            } else {
                setFlash('success', 'Пользователь и связанные с ним темы и сообщения удалены.');
            }
        }
        header('Location: admin_users.php');
        exit;
    }

    renderError(400, 'Неизвестное действие.');
}

$totalUsers = $userModel->count();
$totalPages = max(1, (int)ceil($totalUsers / PAGE_SIZE));
$page = min(requestPage(), $totalPages);
$users = $userModel->paginate(PAGE_SIZE, ($page - 1) * PAGE_SIZE);
$pageTitle = 'Управление пользователями';
require dirname(__DIR__) . '/views/admin_users.php';
