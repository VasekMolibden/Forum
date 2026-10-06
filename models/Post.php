<?php
declare(strict_types=1);

// Модель содержит запросы для ответов в темах.
final class Post
{
    public function __construct(private PDO $pdo)
    {
    }

    // Считает ответы в теме для отображения количества и расчёта страниц.
    public function countForTopic(int $topicId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM posts WHERE topic_id = ?');
        $stmt->execute([$topicId]);
        return (int)$stmt->fetchColumn();
    }

    // Загружает страницу ответов вместе с данными их авторов.
    public function paginateForTopic(int $topicId, int $limit, int $offset): array
    {
        $stmt = $this->pdo->prepare('
            SELECT p.id, p.topic_id, p.user_id, p.content, p.created_at, u.username, u.role
            FROM posts p JOIN users u ON u.id = p.user_id
            WHERE p.topic_id = ?
            ORDER BY p.created_at ASC, p.id ASC
            LIMIT ? OFFSET ?
        ');
        $stmt->bindValue(1, $topicId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Находит ответ и возвращает его автора и идентификатор темы.
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, topic_id, user_id, content FROM posts WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // Добавляет ответ пользователя в тему.
    public function create(int $topicId, int $userId, string $content): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO posts (topic_id, user_id, content) VALUES (?, ?, ?)');
        $stmt->execute([$topicId, $userId, $content]);
    }

    // Сохраняет исправленный текст ответа.
    public function update(int $id, string $content): void
    {
        $stmt = $this->pdo->prepare('UPDATE posts SET content = ? WHERE id = ?');
        $stmt->execute([$content, $id]);
    }

    // Удаляет ответ по идентификатору.
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM posts WHERE id = ?');
        $stmt->execute([$id]);
    }
}
