<?php require __DIR__ . '/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="admin.php" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Админ-панель</a>
        <h1 class="h3 mb-0 mt-1">Пользователи</h1>
    </div>
</div>

<div class="alert alert-warning" role="alert">
    Удаление аккаунта также удаляет все его темы и сообщения. Последнего администратора и собственный аккаунт удалить нельзя.
</div>

<?php if (!$users): ?>
    <div class="alert alert-info">Пользователей пока нет.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped align-middle bg-white">
            <thead>
            <tr>
                <th>Пользователь</th>
                <th>Роль</th>
                <th>Темы</th>
                <th>Сообщения</th>
                <th>Регистрация</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <a href="profile.php?id=<?= (int)$user['id'] ?>"><?= e($user['username']) ?></a>
                        <?php if ((int)$user['id'] === (int)$_SESSION['user_id']): ?><span class="text-muted small">(вы)</span><?php endif; ?>
                    </td>
                    <td><?= e(match ($user['role']) {
                        'admin' => 'Администратор',
                        'moderator' => 'Модератор',
                        default => 'Пользователь',
                    }) ?></td>
                    <td><?= (int)$user['topic_count'] ?></td>
                    <td><?= (int)$user['post_count'] ?></td>
                    <td><?= e($user['created_at']) ?></td>
                    <td>
                        <div class="d-flex flex-wrap gap-2">
                            <form method="post" action="admin_users.php" class="d-flex gap-1">
                                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                <input type="hidden" name="action" value="change_role">
                                <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                                <select class="form-select form-select-sm" name="role" aria-label="Роль пользователя">
                                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Пользователь</option>
                                    <option value="moderator" <?= $user['role'] === 'moderator' ? 'selected' : '' ?>>Модератор</option>
                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Администратор</option>
                                </select>
                                <button class="btn btn-outline-primary btn-sm" type="submit">Сохранить</button>
                            </form>
                            <?php if ((int)$user['id'] !== (int)$_SESSION['user_id']): ?>
                                <form method="post" action="admin_users.php" onsubmit="return confirm('Удалить аккаунт и все его темы и сообщения?')">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                                    <button class="btn btn-outline-danger btn-sm" type="submit" aria-label="Удалить пользователя">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted small align-self-center">Нельзя удалить себя</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php renderPagination($page, $totalPages, 'admin_users.php'); ?>
<?php endif; ?>
<?php require __DIR__ . '/footer.php'; ?>
