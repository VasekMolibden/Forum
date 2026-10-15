<?php require __DIR__ . '/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Административная панель</h1>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-4">
                <div class="text-muted mb-2"><i class="bi bi-people"></i> Пользователи</div>
                <div class="display-6 mb-3"><?= $userCount ?></div>
                <a class="btn btn-primary" href="admin_users.php">Управление аккаунтами</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-4">
                <div class="text-muted mb-2"><i class="bi bi-chat-square-text"></i> Темы</div>
                <div class="display-6 mb-3"><?= $topicCount ?></div>
                <a class="btn btn-primary" href="admin_topics.php">Управление темами</a>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <div class="text-muted mb-2"><i class="bi bi-tags"></i> Категории</div>
                        <div class="display-6 mb-3"><?= $categoryCount ?></div>
                        <a class="btn btn-primary" href="admin_categories.php">Управление категориями</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
