<?php
declare(strict_types=1);

// Модель выполняет запросы к таблице пользователей.
final class User
{
    public function __construct(private PDO $pdo)
    {
    }

    // Находит профиль по идентификатору.
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, username, role, created_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // Находит учётную запись по логину для входа и проверки уникальности.
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    // Создаёт обычную учётную запись с хешированным паролем.
    public function create(string $username, string $password): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
    }

    // Возвращает число сообщений, написанных пользователем.
    public function messageCount(int $id): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM posts WHERE user_id = ?');
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn();
    }

    // Проверяет пароль пользователя по сохранённому хешу.
    public function verifyPassword(int $id, string $password): bool
    {
        $stmt = $this->pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $hash = $stmt->fetchColumn();
        return is_string($hash) && password_verify($password, $hash);
    }

    // Сохраняет новый пароль в виде безопасного хеша.
    public function updatePassword(int $id, string $password): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
    }
}
