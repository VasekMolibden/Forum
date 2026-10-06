<?php require __DIR__ . '/header.php'; ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Темы форума</a></li>
        <li class="breadcrumb-item active"><?= e($topic['title']) ?></li>
    </ol>
</nav>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
                <h1 class="h4 mb-2"><?= e($topic['title']) ?></h1>
                <div class="text-muted small">
                    <i class="bi bi-person"></i>
                    <a class="text-muted" href="profile.php?id=<?= (int)$topic['user_id'] ?>"><?= e($topic['username']) ?></a>
                    <?php if ($topic['role'] === 'moderator'): ?><span class="badge bg-warning text-dark">модератор</span><?php endif; ?>
                    &nbsp;·&nbsp; <?= e($topic['created_at']) ?> · <?= $totalPosts ?> ответов
                </div>
            </div>
            <?php if (canManage((int)$topic['user_id'])): ?>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-secondary btn-sm" href="edit_topic.php?id=<?= (int)$topic['id'] ?>">
                        <i class="bi bi-pencil"></i> Изменить
                    </a>
                    <form method="post" action="delete_topic.php" onsubmit="return confirm('Удалить тему и все ответы?')">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <input type="hidden" name="id" value="<?= (int)$topic['id'] ?>">
                        <button class="btn btn-outline-danger btn-sm" type="submit"><i class="bi bi-trash"></i> Удалить</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        <hr>
        <div class="topic-body"><?= nl2br(e($topic['body'] !== '' ? $topic['body'] : 'Описание темы не добавлено.')) ?></div>
    </div>
</div>

<?php foreach ($posts as $post): ?>
    <div class="card post-card shadow-sm mb-3 <?= $post['role'] === 'moderator' ? 'is-moderator' : '' ?>">
        <div class="card-body d-flex gap-3">
            <div class="avatar <?= $post['role'] === 'moderator' ? 'mod' : '' ?>">
                <?= e(mb_strtoupper(mb_substr($post['username'], 0, 1, 'UTF-8'), 'UTF-8')) ?>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong><a class="text-body text-decoration-none" href="profile.php?id=<?= (int)$post['user_id'] ?>"><?= e($post['username']) ?></a></strong>
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-muted"><?= e($post['created_at']) ?></small>
                        <?php if (canManage((int)$post['user_id'])): ?>
                            <a href="edit_post.php?id=<?= (int)$post['id'] ?>" class="btn btn-outline-secondary btn-sm" aria-label="Изменить сообщение">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="post" action="delete_post.php" onsubmit="return confirm('Удалить это сообщение?')">
                                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
                                <button class="btn btn-outline-danger btn-sm" type="submit" aria-label="Удалить сообщение">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <div><?= nl2br(e($post['content'])) ?></div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
<?php renderPagination($page, $totalPages, 'topic.php?id=' . (int)$topic['id']); ?>

<?php if (isLoggedIn()): ?>
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body">
            <h2 class="h5 mb-3"><i class="bi bi-reply text-primary"></i> Ответить</h2>
            <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <div class="mb-3">
                    <textarea name="content" rows="4" maxlength="5000" class="form-control" required><?= e($content) ?></textarea>
                </div>
                <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i> Отправить</button>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-secondary mt-4"><a href="login.php" class="alert-link">Войдите</a>, чтобы ответить в теме.</div>
<?php endif; ?>
<?php require __DIR__ . '/footer.php'; ?>
