<?php require __DIR__ . '/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <!-- <h1 class="h3 mb-0">Темы форума</h1> -->
    <?php if (isLoggedIn()): ?>
        <a href="create_topic.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Новая тема</a>
    <?php endif; ?>
</div>

<form method="get" action="index.php" class="card card-body shadow-sm border-0 mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-lg-5">
            <label class="form-label" for="q">Поиск по темам</label>
            <input id="q" type="search" name="q" class="form-control" maxlength="100" value="<?= e($search) ?>" placeholder="Название или описание">
        </div>
        <div class="col-sm-6 col-lg-3">
            <label class="form-label" for="category">Категория</label>
            <select id="category" name="category" class="form-select">
                <option value="">Все категории</option>
                <option value="0" <?= $categoryId === 0 ? 'selected' : '' ?>>Без категории</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int)$category['id'] ?>" <?= $categoryId === (int)$category['id'] ? 'selected' : '' ?>>
                        <?= e($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-6 col-lg-2">
            <label class="form-label" for="sort">Сортировка</label>
            <select id="sort" name="sort" class="form-select">
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Сначала новые</option>
                <option value="activity" <?= $sort === 'activity' ? 'selected' : '' ?>>По активности</option>
                <option value="replies" <?= $sort === 'replies' ? 'selected' : '' ?>>По ответам</option>
            </select>
        </div>
        <div class="col-lg-2 d-flex gap-2">
            <button class="btn btn-primary flex-grow-1" type="submit"><i class="bi bi-search"></i> Найти</button>
            <a class="btn btn-outline-secondary" href="index.php" aria-label="Сбросить фильтры"><i class="bi bi-x-lg"></i></a>
        </div>
    </div>
</form>

<?php if (!$topics): ?>
    <div class="alert alert-info">
        <?php if ($search !== '' || $categoryId !== null): ?>
            По заданным условиям темы не найдены.
        <?php else: ?>
            Пока нет ни одной темы. <?= isLoggedIn() ? 'Создайте первую!' : 'Войдите, чтобы создать.' ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="row g-3">
    <?php foreach ($topics as $topic): ?>
        <div class="col-12">
            <div class="card topic-card shadow-sm">
                <div class="card-body d-flex align-items-start gap-3">
                    <div class="avatar <?= in_array($topic['role'], ['moderator', 'admin'], true) ? 'mod' : '' ?>">
                        <?= e(mb_strtoupper(mb_substr($topic['username'], 0, 1, 'UTF-8'), 'UTF-8')) ?>
                    </div>
                    <div class="flex-grow-1">
                        <h2 class="topic-title h5 mb-1">
                            <a href="topic.php?id=<?= (int)$topic['id'] ?>"><?= e($topic['title']) ?></a>
                        </h2>
                        <div class="d-flex flex-wrap gap-1 mb-1">
                            <?php if ((int)$topic['is_pinned'] === 1): ?><span class="badge text-bg-primary"><i class="bi bi-pin-angle"></i> Закреплена</span><?php endif; ?>
                            <?php if ((int)$topic['is_closed'] === 1): ?><span class="badge text-bg-secondary"><i class="bi bi-lock"></i> Закрыта</span><?php endif; ?>
                            <span class="badge text-bg-light border"><?= e($topic['category_name'] ?? 'Без категории') ?></span>
                        </div>
                        <div class="text-muted small">
                            <i class="bi bi-person"></i>
                            <a class="text-muted" href="profile.php?id=<?= (int)$topic['user_id'] ?>"><?= e($topic['username']) ?></a>
                            <?php if ($topic['role'] === 'admin'): ?>
                                <span class="badge bg-warning text-dark">администратор</span>
                            <?php elseif ($topic['role'] === 'moderator'): ?>
                                <span class="badge bg-warning text-dark">модератор</span>
                            <?php endif; ?>
                            &nbsp;·&nbsp; <?= e($topic['created_at']) ?>
                        </div>
                        <?php if ($topic['body'] !== ''): ?>
                            <p class="text-muted mt-2 mb-0"><?= e(mb_strimwidth($topic['body'], 0, 180, '…', 'UTF-8')) ?></p>
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
<?php renderPagination($page, $totalPages, $paginationUrl); ?>
<?php require __DIR__ . '/footer.php'; ?>
