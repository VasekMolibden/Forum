<?php
require 'config.php';

$topicId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$topicId = $topicId !== false && $topicId !== null && $topicId > 0 ? $topicId : 0;
$stmt = $pdo->prepare('
    SELECT t.id, t.user_id, t.title, t.created_at, u.username, u.role
    FROM topics t JOIN users u ON u.id = t.user_id
    WHERE t.id = ?
');
$stmt->execute([$topicId]);
$topic = $stmt->fetch();

if (!$topic) {
    http_response_code(404);
    $pageTitle = 'Тема не найдена';
    require 'header.php';
    echo '<div class="alert alert-warning">Тема не найдена.</div><a href="index.php">Вернуться к темам</a>';
    require 'footer.php';
    exit;
}

$error = '';
$content = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    verifyCsrfToken();
    $content = trim(postString('content'));

    if ($content === '') {
        $error = 'Введите сообщение.';
    } elseif (mb_strlen($content, 'UTF-8') > 5000) {
        $error = 'Сообщение не должно превышать 5000 символов.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO posts (topic_id, user_id, content) VALUES (?, ?, ?)');
        $stmt->execute([$topicId, (int)$_SESSION['user_id'], $content]);
        header('Location: topic.php?id=' . $topicId);
        exit;
    }
}

$stmt = $pdo->prepare('
    SELECT p.content, p.created_at, u.username, u.role
    FROM posts p JOIN users u ON u.id = p.user_id
    WHERE p.topic_id = ?
    ORDER BY p.created_at ASC, p.id ASC
');
$stmt->execute([$topicId]);
$posts = $stmt->fetchAll();
$pageTitle = $topic['title'];
require 'header.php';
?>
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
                    Автор: <?= e($topic['username']) ?> · <?= e($topic['created_at']) ?> · Ответов: <?= count($posts) ?>
                </div>
            </div>
            <?php if (isModerator() || (isLoggedIn() && (int)$_SESSION['user_id'] === (int)$topic['user_id'])): ?>
                <form method="post" action="delete_topic.php" onsubmit="return confirm('Удалить тему и все ответы в ней?')">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="id" value="<?= (int)$topic['id'] ?>">
                    <button class="btn btn-outline-danger btn-sm" type="submit">
                        <i class="bi bi-trash"></i> Удалить тему
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php foreach ($posts as $post): ?>
    <div class="card post-card shadow-sm mb-3 <?= $post['role'] === 'moderator' ? 'is-moderator' : '' ?>">
        <div class="card-body d-flex gap-3">
            <div class="avatar <?= $post['role'] === 'moderator' ? 'mod' : '' ?>">
                <?= e(mb_strtoupper(mb_substr($post['username'], 0, 1, 'UTF-8'), 'UTF-8')) ?>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between mb-2">
                    <strong><?= e($post['username']) ?></strong>
                    <small class="text-muted"><?= e($post['created_at']) ?></small>
                </div>
                <!-- Сначала экранируем текст, затем превращаем переносы строк в HTML-переносы. -->
                <div><?= nl2br(e($post['content'])) ?></div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php if (isLoggedIn()): ?>
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body">
            <h2 class="h5 mb-3">Ответить</h2>
            <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <div class="mb-3">
                    <textarea name="content" rows="4" maxlength="5000" class="form-control" required><?= e($content) ?></textarea>
                </div>
                <button class="btn btn-primary" type="submit">Отправить</button>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-secondary mt-4"><a href="login.php" class="alert-link">Войдите</a>, чтобы ответить в теме.</div>
<?php endif; ?>
<?php require 'footer.php'; ?>
