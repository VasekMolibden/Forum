<?php require __DIR__ . '/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="bi bi-list-ul"></i> Темы форума</h1>
    <?php if (isLoggedIn()): ?>
        <a href="create_topic.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Новая тема</a>
    <?php endif; ?>
</div>

<?php if (!$topics): ?>
    <div class="alert alert-info">Пока нет ни одной темы. <?= isLoggedIn() ? 'Создайте первую!' : 'Войдите, чтобы создать.' ?></div>
<?php endif; ?>

<div class="row g-3">
    <?php foreach ($topics as $topic): ?>
        <div class="col-12">
            <div class="card topic-card shadow-sm">
                <div class="card-body d-flex align-items-start gap-3">
                    <div class="avatar <?= $topic['role'] === 'moderator' ? 'mod' : '' ?>">
                        <?= e(mb_strtoupper(mb_substr($topic['username'], 0, 1, 'UTF-8'), 'UTF-8')) ?>
                    </div>
                    <div class="flex-grow-1">
                        <h2 class="topic-title h5 mb-1">
                            <a href="topic.php?id=<?= (int)$topic['id'] ?>"><?= e($topic['title']) ?></a>
                        </h2>
                        <div class="text-muted small">
                            <i class="bi bi-person"></i>
                            <a class="text-muted" href="profile.php?id=<?= (int)$topic['user_id'] ?>"><?= e($topic['username']) ?></a>
                            <?php if ($topic['role'] === 'moderator'): ?><span class="badge bg-warning text-dark">модератор</span><?php endif; ?>
                            &nbsp;·&nbsp; <i class="bi bi-clock"></i> <?= e($topic['created_at']) ?>
                        </div>
                        <?php if ($topic['body'] !== ''): ?>
                            <!-- <p class="text-muted mt-2 mb-0"><?= e(mb_strimwidth($topic['body'], 0, 180, '…', 'UTF-8')) ?></p> -->
                        <?php endif; ?>
                    </div>
                    <div class="text-end">
                        <span class="badge rounded-pill text-bg-secondary mb-2">
                            <i class="bi bi-chat-dots"></i> <?= (int)$topic['replies'] ?>
                        </span>
                        <?php if (canManage((int)$topic['user_id'])): ?>
                            <div class="d-flex gap-1">
                                <a href="edit_topic.php?id=<?= (int)$topic['id'] ?>" class="btn btn-outline-secondary btn-sm" aria-label="Изменить тему">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="post" action="delete_topic.php" onsubmit="return confirm('Удалить эту тему?')">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                    <input type="hidden" name="id" value="<?= (int)$topic['id'] ?>">
                                    <button class="btn btn-outline-danger btn-sm" type="submit" aria-label="Удалить тему">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php renderPagination($page, $totalPages, 'index.php'); ?>
<?php require __DIR__ . '/footer.php'; ?>
