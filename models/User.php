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

    // Возвращает общее число учётных записей.
    public function count(): int
    {
        return (int)$this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    // Загружает страницу пользователей вместе с числами тем и сообщений.
    public function paginate(int $limit, int $offset): array
    {
        $stmt = $this->pdo->prepare('
            SELECT u.id, u.username, u.role, u.created_at,
                   (SELECT COUNT(*) FROM topics t WHERE t.user_id = u.id) AS topic_count,
                   (SELECT COUNT(*) FROM posts p WHERE p.user_id = u.id) AS post_count
            FROM users u
            ORDER BY u.created_at DESC, u.id DESC
            LIMIT :limit OFFSET :offset
        ');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Изменяет роль с защитой от удаления последнего администратора.
    public function changeRole(int $id, string $role): string
    {
        $this->pdo->beginTransaction();
        try {
            $admins = $this->pdo->query("SELECT id FROM users WHERE role = 'admin' FOR UPDATE")->fetchAll();
            $stmt = $this->pdo->prepare('SELECT role FROM users WHERE id = ? FOR UPDATE');
            $stmt->execute([$id]);
            $currentRole = $stmt->fetchColumn();
            if (!is_string($currentRole)) {
                $this->pdo->commit();
                return 'not_found';
            }

            if ($currentRole === 'admin' && $role !== 'admin' && count($admins) <= 1) {
                $this->pdo->commit();
                return 'last_admin';
            }

            $stmt = $this->pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
            $stmt->execute([$role, $id]);
            $this->pdo->commit();
            return 'updated';
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    // Удаляет аккаунт, не позволяя удалить последнего администратора.
    public function deleteSafely(int $id): string
    {
        $this->pdo->beginTransaction();
        try {
            $admins = $this->pdo->query("SELECT id FROM users WHERE role = 'admin' FOR UPDATE")->fetchAll();
            $stmt = $this->pdo->prepare('SELECT role FROM users WHERE id = ? FOR UPDATE');
            $stmt->execute([$id]);
            $role = $stmt->fetchColumn();
            if (!is_string($role)) {
                $this->pdo->commit();
                return 'not_found';
            }

            if ($role === 'admin' && count($admins) <= 1) {
                $this->pdo->commit();
                return 'last_admin';
            }

            $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
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
