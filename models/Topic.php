<?php
declare(strict_types=1);

// Модель содержит запросы для списка тем и отдельных тем.
final class Topic
{
    public function __construct(private PDO $pdo)
    {
    }

    // Возвращает общее количество тем для расчёта страниц.
    public function count(): int
    {
        return (int)$this->pdo->query('SELECT COUNT(*) FROM topics')->fetchColumn();
    }

    // Возвращает число тем после поиска и фильтрации по категории.
    public function countFiltered(string $search, ?int $categoryId): int
    {
        [$where, $params] = $this->filters($search, $categoryId);
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM topics t' . $where);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // Загружает страницу тем с выбранными поиском, категорией и сортировкой.
    public function paginate(int $limit, int $offset): array
    {
        return $this->paginateFiltered($limit, $offset, '', null, 'newest');
    }

    // Загружает список тем с авторами, ответами и временем последней активности.
    public function paginateFiltered(int $limit, int $offset, string $search, ?int $categoryId, string $sort): array
    {
        [$where, $params] = $this->filters($search, $categoryId);
        $orderBy = match ($sort) {
            'activity' => 'activity_at DESC, t.created_at DESC, t.id DESC',
            'replies' => 'replies DESC, t.created_at DESC, t.id DESC',
            default => 't.created_at DESC, t.id DESC',
        };
        $stmt = $this->pdo->prepare('
            SELECT t.id, t.user_id, t.title, COALESCE(t.body, \'\') AS body, t.category_id,
                   c.name AS category_name, t.is_pinned, t.is_closed, t.created_at,
                   COALESCE(MAX(p.created_at), t.created_at) AS activity_at,
                   u.username, u.role, COUNT(p.id) AS replies
            FROM topics t
            JOIN users u ON u.id = t.user_id
            LEFT JOIN categories c ON c.id = t.category_id
            LEFT JOIN posts p ON p.topic_id = t.id' . $where . '
            GROUP BY t.id, t.user_id, t.title, t.body, t.category_id, c.name,
                     t.is_pinned, t.is_closed, t.created_at, u.username, u.role
            ORDER BY t.is_pinned DESC, ' . $orderBy . '
            LIMIT :limit OFFSET :offset
        ');
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Возвращает количество тем, созданных указанным пользователем.
    public function countByUser(int $userId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM topics WHERE user_id = ?');
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    // Загружает страницу тем указанного пользователя с числом ответов.
    public function paginateByUser(int $userId, int $limit, int $offset): array
    {
        $stmt = $this->pdo->prepare('
            SELECT t.id, t.user_id, t.title, COALESCE(t.body, \'\') AS body, t.created_at, u.username, u.role,
                   (SELECT COUNT(*) FROM posts p WHERE p.topic_id = t.id) AS replies
            FROM topics t JOIN users u ON u.id = t.user_id
            WHERE t.user_id = :user_id
            ORDER BY t.created_at DESC, t.id DESC
            LIMIT :limit OFFSET :offset
        ');
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Находит тему с данными автора и категорией.
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT t.id, t.user_id, t.title, COALESCE(t.body, \'\') AS body, t.category_id,
                   c.name AS category_name, t.is_pinned, t.is_closed, t.created_at, u.username, u.role
            FROM topics t
            JOIN users u ON u.id = t.user_id
            LEFT JOIN categories c ON c.id = t.category_id
            WHERE t.id = ?
        ');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // Создаёт тему и возвращает её новый идентификатор.
    public function create(int $userId, string $title, string $body, ?int $categoryId): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO topics (user_id, title, body, category_id) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $title, $body, $categoryId]);
        return (int)$this->pdo->lastInsertId();
    }

    // Изменяет заголовок, описание и категорию существующей темы.
    public function update(int $id, string $title, string $body, ?int $categoryId): void
    {
        $stmt = $this->pdo->prepare('UPDATE topics SET title = ?, body = ?, category_id = ? WHERE id = ?');
        $stmt->execute([$title, $body, $categoryId, $id]);
    }

    // Закрепляет или открепляет тему.
    public function setPinned(int $id, bool $pinned): void
    {
        $stmt = $this->pdo->prepare('UPDATE topics SET is_pinned = ? WHERE id = ?');
        $stmt->execute([$pinned ? 1 : 0, $id]);
    }

    // Закрывает или открывает тему для новых ответов.
    public function setClosed(int $id, bool $closed): void
    {
        $stmt = $this->pdo->prepare('UPDATE topics SET is_closed = ? WHERE id = ?');
        $stmt->execute([$closed ? 1 : 0, $id]);
    }

    // Удаляет тему; ответы удаляются каскадно настройкой внешнего ключа.
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM topics WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    // Создаёт безопасные условия поиска и фильтрации.
    private function filters(string $search, ?int $categoryId): array
    {
        $conditions = [];
        $params = [];
        if ($search !== '') {
            $escapedSearch = addcslashes($search, '\\%_');
            $conditions[] = "(t.title LIKE :search_title OR COALESCE(t.body, '') LIKE :search_body)";
            $params[':search_title'] = '%' . $escapedSearch . '%';
            $params[':search_body'] = '%' . $escapedSearch . '%';
        }
        if ($categoryId === 0) {
            $conditions[] = 't.category_id IS NULL';
        } elseif ($categoryId !== null) {
            $conditions[] = 't.category_id = :category_id';
            $params[':category_id'] = $categoryId;
        }
        return [$conditions ? ' WHERE ' . implode(' AND ', $conditions) : '', $params];
    }
}
