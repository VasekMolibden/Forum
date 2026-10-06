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

    // Загружает страницу тем вместе с именами авторов и числом ответов.
    public function paginate(int $limit, int $offset): array
    {
        $stmt = $this->pdo->prepare('
            SELECT t.id, t.user_id, t.title, COALESCE(t.body, \'\') AS body, t.created_at, u.username, u.role,
                   (SELECT COUNT(*) FROM posts p WHERE p.topic_id = t.id) AS replies
            FROM topics t JOIN users u ON u.id = t.user_id
            ORDER BY t.created_at DESC, t.id DESC
            LIMIT :limit OFFSET :offset
        ');
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

    // Находит тему по идентификатору вместе с автором.
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT t.id, t.user_id, t.title, COALESCE(t.body, \'\') AS body, t.created_at, u.username, u.role
            FROM topics t JOIN users u ON u.id = t.user_id
            WHERE t.id = ?
        ');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // Создаёт тему и возвращает её новый идентификатор.
    public function create(int $userId, string $title, string $body): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO topics (user_id, title, body) VALUES (?, ?, ?)');
        $stmt->execute([$userId, $title, $body]);
        return (int)$this->pdo->lastInsertId();
    }

    // Изменяет заголовок и описание существующей темы.
    public function update(int $id, string $title, string $body): void
    {
        $stmt = $this->pdo->prepare('UPDATE topics SET title = ?, body = ? WHERE id = ?');
        $stmt->execute([$title, $body, $id]);
    }

    // Удаляет тему; ответы удаляются каскадно настройкой внешнего ключа.
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM topics WHERE id = ?');
        $stmt->execute([$id]);
    }
}
