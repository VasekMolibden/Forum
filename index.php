<?php
require 'config.php';
$pageTitle = 'Темы форума';

// Список тем вместе с автором и количеством ответов для главной страницы.
$topics = $pdo->query("
    SELECT t.id, t.user_id, t.title, t.created_at, u.username, u.role,
           (SELECT COUNT(*) FROM posts p WHERE p.topic_id = t.id) AS replies
    FROM topics t
    JOIN users u ON u.id = t.user_id
    ORDER BY t.created_at DESC
")->fetchAll();

require 'header.php';
?>
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
                            <i class="bi bi-person"></i> <?= e($topic['username']) ?>
                            <?php if ($topic['role'] === 'moderator'): ?><span class="badge bg-warning text-dark">модератор</span><?php endif; ?>
                            &nbsp;·&nbsp; <i class="bi bi-clock"></i> <?= e($topic['created_at']) ?>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge rounded-pill text-bg-secondary"><i class="bi bi-chat-dots"></i> <?= (int)$topic['replies'] ?></span>
                        <?php if (isModerator() || (isLoggedIn() && (int)$_SESSION['user_id'] === (int)$topic['user_id'])): ?>
                            <form method="post" action="delete_topic.php" class="mt-2" onsubmit="return confirm('Удалить эту тему?')">
                                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                <input type="hidden" name="id" value="<?= (int)$topic['id'] ?>">
                                <button class="btn btn-outline-danger btn-sm" type="submit" aria-label="Удалить тему">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php require 'footer.php'; ?>
