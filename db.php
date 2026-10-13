<?php
declare(strict_types=1);

$host = 'localhost';
$user = 'forumuser';
$pass = 'wiakapebeb';
$dbname = 'forum_db';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

try {
    // При первом запуске создаём базу и таблицы форума, если их ещё нет.
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('user', 'moderator', 'admin') NOT NULL DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    // Добавляем роль администратора в существующих базах.
    $roleColumn = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch();
    if ($roleColumn && !str_contains((string)$roleColumn['Type'], "'admin'")) {
        $pdo->exec("ALTER TABLE users MODIFY role ENUM('user', 'moderator', 'admin') NOT NULL DEFAULT 'user'");
    }
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS topics (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            body TEXT NOT NULL,
            category_id INT NULL,
            is_pinned TINYINT(1) NOT NULL DEFAULT 0,
            is_closed TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    // Добавляем описание темам в существующих базах, не затрагивая старые записи.
    $topicBodyColumn = $pdo->query("SHOW COLUMNS FROM topics LIKE 'body'")->fetch();
    if (!$topicBodyColumn) {
        $pdo->exec('ALTER TABLE topics ADD COLUMN body TEXT NULL');
    }
    $topicColumns = $pdo->query('SHOW COLUMNS FROM topics')->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('category_id', $topicColumns, true)) {
        $pdo->exec('ALTER TABLE topics ADD COLUMN category_id INT NULL');
    }
    if (!in_array('is_pinned', $topicColumns, true)) {
        $pdo->exec('ALTER TABLE topics ADD COLUMN is_pinned TINYINT(1) NOT NULL DEFAULT 0');
    }
    if (!in_array('is_closed', $topicColumns, true)) {
        $pdo->exec('ALTER TABLE topics ADD COLUMN is_closed TINYINT(1) NOT NULL DEFAULT 0');
    }
    $categoryForeignKey = $pdo->query("
        SELECT CONSTRAINT_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'topics'
          AND COLUMN_NAME = 'category_id' AND REFERENCED_TABLE_NAME = 'categories'
    ")->fetchColumn();
    if (!$categoryForeignKey) {
        $pdo->exec('ALTER TABLE topics ADD CONSTRAINT fk_topics_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL');
    }
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            topic_id INT NOT NULL,
            user_id INT NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    // Учётная запись модератора создаётся один раз для локального демонстрационного запуска.
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute(['moderator']);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'moderator')");
        $stmt->execute(['moderator', password_hash('moderator123', PASSWORD_DEFAULT)]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    exit('Не удалось подключиться к MySQL или создать базу форума. Проверьте параметры в db.php.');
}

// Проверяет, вошёл ли пользователь в аккаунт.
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

// Проверяет, вошёл ли пользователь с ролью модератора.
function isModerator(): bool
{
    return in_array($_SESSION['role'] ?? '', ['moderator', 'admin'], true);
}

// Проверяет, вошёл ли пользователь с ролью администратора.
function isAdmin(): bool
{
    return ($_SESSION['role'] ?? '') === 'admin';
}

// Перенаправляет гостя на страницу входа.
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Проверяет, может ли текущий пользователь управлять записью указанного автора.
function canManage(int $ownerId): bool
{
    return isLoggedIn() && (isModerator() || (int)$_SESSION['user_id'] === $ownerId);
}

// Экранирует значение перед выводом в HTML.
function e(string|int|null $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Возвращает CSRF-токен текущей сессии, создавая его при необходимости.
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Проверяет токен формы и завершает запрос, если он отсутствует или неверен.
function verifyCsrfToken(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrfToken(), $token)) {
        http_response_code(400);
        exit('Ошибка проверки формы. Обновите страницу и попробуйте ещё раз.');
    }
}

// Безопасно получает строковое значение из POST-запроса.
function postString(string $name): string
{
    $value = $_POST[$name] ?? '';
    return is_string($value) ? $value : '';
}

// Сохраняет сообщение, которое будет показано один раз после перенаправления.
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Возвращает и удаляет одноразовое сообщение текущей сессии.
function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

// Обновляет роль и имя пользователя, чтобы изменения аккаунта сразу отзывали старые права сессии.
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare('SELECT username, role FROM users WHERE id = ?');
    $stmt->execute([(int)$_SESSION['user_id']]);
    $currentUser = $stmt->fetch();
    if ($currentUser) {
        $_SESSION['username'] = $currentUser['username'];
        $_SESSION['role'] = $currentUser['role'];
    } else {
        unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['role']);
    }
}
