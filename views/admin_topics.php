<?php require __DIR__ . '/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="admin.php" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Админ-панель</a>
        <h1 class="h3 mb-0 mt-1">Темы форума</h1>
    </div>
</div>

<?php if (!$topics): ?>
    <div class="alert alert-info">Тем пока нет.</div>
<?php else: ?>
    <div class="list-group shadow-sm">
        <?php foreach ($topics as $topic): ?>
            <div class="list-group-item py-3">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <h2 class="h6 mb-1"><a href="topic.php?id=<?= (int)$topic['id'] ?>"><?= e($topic['title']) ?></a></h2>
                        <div class="text-muted small">
                            Автор: <a href="profile.php?id=<?= (int)$topic['user_id'] ?>"><?= e($topic['username']) ?></a>
                            · <?= e($topic['created_at']) ?>
                            · Ответов: <?= (int)$topic['replies'] ?>
                            · <?= e($topic['category_name'] ?? 'Без категории') ?>
                        </div>
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            <?php if ((int)$topic['is_pinned'] === 1): ?><span class="badge text-bg-primary">Закреплена</span><?php endif; ?>
                            <?php if ((int)$topic['is_closed'] === 1): ?><span class="badge text-bg-secondary">Закрыта</span><?php endif; ?>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                        <form method="post" action="moderate_topic.php">
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <input type="hidden" name="topic_id" value="<?= (int)$topic['id'] ?>">
                            <input type="hidden" name="action" value="toggle_pin">
                            <input type="hidden" name="return_to" value="admin_topics">
                            <button class="btn btn-outline-primary btn-sm" type="submit">
                                <?= (int)$topic['is_pinned'] === 1 ? 'Открепить' : 'Закрепить' ?>
                            </button>
                        </form>
                        <form method="post" action="moderate_topic.php">
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <input type="hidden" name="topic_id" value="<?= (int)$topic['id'] ?>">
                            <input type="hidden" name="action" value="toggle_closed">
                            <input type="hidden" name="return_to" value="admin_topics">
                            <button class="btn btn-outline-secondary btn-sm" type="submit">
                                <?= (int)$topic['is_closed'] === 1 ? 'Открыть' : 'Закрыть' ?>
                            </button>
                        </form>
                        <form method="post" action="admin_topics.php" onsubmit="return confirm('Удалить тему и все её ответы?')">
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <input type="hidden" name="topic_id" value="<?= (int)$topic['id'] ?>">
                            <button class="btn btn-outline-danger btn-sm" type="submit" aria-label="Удалить тему">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php renderPagination($page, $totalPages, 'admin_topics.php'); ?>
<?php endif; ?>
<?php require __DIR__ . '/footer.php'; ?>
