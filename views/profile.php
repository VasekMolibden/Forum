<?php require __DIR__ . '/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <h1 class="h4 mb-0"><i class="bi bi-person-circle"></i> <?= e($profile['username']) ?></h1>
                    <?php if ($isOwnProfile): ?>
                        <a class="btn btn-outline-primary btn-sm" href="settings.php"><i class="bi bi-gear"></i> Настройки профиля</a>
                    <?php endif; ?>
                </div>
                <dl class="row mb-0">
                    <dt class="col-sm-5">Роль</dt>
                    <dd class="col-sm-7"><?= e(match ($profile['role']) {
                        'admin' => 'Администратор',
                        'moderator' => 'Модератор',
                        default => 'Пользователь',
                    }) ?></dd>
                    <dt class="col-sm-5">Дата регистрации</dt>
                    <dd class="col-sm-7"><?= e($profile['created_at']) ?></dd>
                    <dt class="col-sm-5">Сообщений</dt>
                    <dd class="col-sm-7"><?= $messageCount ?></dd>
                </dl>
            </div>
        </div>

        <section>
            <h2 class="h5 mb-3">Темы пользователя</h2>
            <?php if (!$topics): ?>
                <div class="alert alert-info">Пользователь ещё не создал ни одной темы.</div>
            <?php else: ?>
                <div class="list-group shadow-sm">
                    <?php foreach ($topics as $topic): ?>
                        <a class="list-group-item list-group-item-action py-3" href="topic.php?id=<?= (int)$topic['id'] ?>">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h3 class="h6 mb-1"><?= e($topic['title']) ?></h3>
                                    <div class="text-muted small"><?= e($topic['created_at']) ?></div>
                                </div>
                                <span class="badge rounded-pill text-bg-secondary">
                                    <i class="bi bi-chat-dots"></i> <?= (int)$topic['replies'] ?>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php renderPagination($page, $totalPages, 'profile.php?id=' . $userId); ?>
            <?php endif; ?>
        </section>
    </div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
