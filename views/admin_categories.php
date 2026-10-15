<?php require __DIR__ . '/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="admin.php" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Админ-панель</a>
        <h1 class="h3 mb-0 mt-1">Категории</h1>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <h2 class="h5">Добавить категорию</h2>
        <form method="post" action="admin_categories.php" class="row g-2">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="action" value="create">
            <div class="col-sm">
                <label class="visually-hidden" for="new_category_name">Название категории</label>
                <input id="new_category_name" type="text" name="name" maxlength="100" class="form-control" placeholder="Название категории" required>
            </div>
            <div class="col-sm-auto"><button class="btn btn-primary" type="submit">Добавить</button></div>
        </form>
    </div>
</div>

<?php if (!$categories): ?>
    <div class="alert alert-info">Категорий пока нет. Темы можно создавать без категории.</div>
<?php else: ?>
    <div class="list-group shadow-sm">
        <?php foreach ($categories as $category): ?>
            <div class="list-group-item py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <strong><?= e($category['name']) ?></strong>
                        <div class="text-muted small">Тем: <?= (int)$category['topic_count'] ?></div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <form method="post" action="admin_categories.php" class="d-flex gap-1">
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <input type="hidden" name="action" value="rename">
                            <input type="hidden" name="category_id" value="<?= (int)$category['id'] ?>">
                            <label class="visually-hidden" for="category_name_<?= (int)$category['id'] ?>">Новое название</label>
                            <input id="category_name_<?= (int)$category['id'] ?>" type="text" name="name" maxlength="100" class="form-control form-control-sm" value="<?= e($category['name']) ?>" required>
                            <button class="btn btn-outline-primary btn-sm" type="submit">Переименовать</button>
                        </form>
                        <form method="post" action="admin_categories.php" onsubmit="return confirm('Удалить эту категорию?')">
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="category_id" value="<?= (int)$category['id'] ?>">
                            <button class="btn btn-outline-danger btn-sm" type="submit" <?= (int)$category['topic_count'] > 0 ? 'disabled title="Сначала перенесите темы из этой категории"' : '' ?>>
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/footer.php'; ?>
