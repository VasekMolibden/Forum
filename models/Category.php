<?php
declare(strict_types=1);

// Модель выполняет запросы для категорий тем.
final class Category
{
    public function __construct(private PDO $pdo)
    {
    }

    // Возвращает все категории в алфавитном порядке.
    public function all(): array
    {
        return $this->pdo->query('SELECT id, name FROM categories ORDER BY name, id')->fetchAll();
    }

    // Загружает категории вместе с числом связанных тем для админки.
    public function allWithTopicCounts(): array
    {
        return $this->pdo->query('
            SELECT c.id, c.name, COUNT(t.id) AS topic_count
            FROM categories c
            LEFT JOIN topics t ON t.category_id = c.id
            GROUP BY c.id, c.name
            ORDER BY c.name, c.id
        ')->fetchAll();
    }

    // Проверяет наличие категории перед изменением темы.
    public function exists(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        return (bool)$stmt->fetchColumn();
    }

    // Возвращает количество тем, привязанных к категории.
    public function topicCount(int $id): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM topics WHERE category_id = ?');
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn();
    }

    // Создаёт категорию с указанным названием.
    public function create(string $name): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO categories (name) VALUES (?)');
        $stmt->execute([$name]);
    }

    // Изменяет название существующей категории.
    public function rename(int $id, string $name): void
    {
        $stmt = $this->pdo->prepare('UPDATE categories SET name = ? WHERE id = ?');
        $stmt->execute([$name, $id]);
    }

    // Удаляет категорию только если она не содержит тем.
    public function deleteIfEmpty(int $id): string
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('SELECT id FROM categories WHERE id = ? FOR UPDATE');
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                $this->pdo->commit();
                return 'not_found';
            }

            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM topics WHERE category_id = ?');
            $stmt->execute([$id]);
            if ((int)$stmt->fetchColumn() > 0) {
                $this->pdo->commit();
                return 'not_empty';
            }

            $stmt = $this->pdo->prepare('DELETE FROM categories WHERE id = ?');
            $stmt->execute([$id]);
            $this->pdo->commit();
            return 'deleted';
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
