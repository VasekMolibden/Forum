<?php require __DIR__ . '/header.php'; ?>
<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <h1 class="h4 mb-3">Создать тему</h1>
        <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <div class="mb-3">
                <label class="form-label" for="title">Название темы</label>
                <input id="title" type="text" name="title" maxlength="255" class="form-control" value="<?= e($title) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="body">Описание темы</label>
                <textarea id="body" name="body" rows="6" maxlength="5000" class="form-control" required><?= e($body) ?></textarea>
                <div class="form-text">Расскажите, о чём эта тема (до 5000 символов).</div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="category_id">Категория</label>
                <select id="category_id" name="category_id" class="form-select">
                    <option value="">Без категории</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int)$category['id'] ?>" <?= $selectedCategory === (string)$category['id'] ? 'selected' : '' ?>>
                            <?= e($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-primary" type="submit">Создать</button>
            <a href="index.php" class="btn btn-outline-secondary">Отмена</a>
        </form>
    </div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
