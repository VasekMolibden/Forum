<?php require __DIR__ . '/header.php'; ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Темы форума</a></li>
        <li class="breadcrumb-item"><a href="topic.php?id=<?= (int)$post['topic_id'] ?>">Вернуться к теме</a></li>
        <li class="breadcrumb-item active">Изменить сообщение</li>
    </ol>
</nav>
<div class="card shadow-sm border-0">
    <div class="card-body">
        <h1 class="h4 mb-3">Изменить сообщение</h1>
        <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <div class="mb-3">
                <label class="form-label" for="content">Текст сообщения</label>
                <textarea id="content" name="content" rows="6" maxlength="5000" class="form-control" required><?= e($content) ?></textarea>
            </div>
            <button class="btn btn-primary" type="submit">Сохранить</button>
            <a class="btn btn-outline-secondary" href="topic.php?id=<?= (int)$post['topic_id'] ?>">Отмена</a>
        </form>
    </div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
