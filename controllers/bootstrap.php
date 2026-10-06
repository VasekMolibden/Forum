<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/Topic.php';
require_once dirname(__DIR__) . '/models/Post.php';

// Размер страницы одинаков для списка тем и ответов в теме.
const PAGE_SIZE = 10;

// Читает положительный идентификатор из GET-параметра id.
function requestId(): int
{
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    return $id !== false && $id !== null && $id > 0 ? $id : 0;
}

// Читает номер страницы и для некорректного значения начинает с первой.
function requestPage(): int
{
    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
    return $page !== false && $page !== null && $page > 0 ? $page : 1;
}

// Показывает пагинацию, сохраняя переданные параметры страницы.
function renderPagination(int $page, int $totalPages, string $baseUrl): void
{
    if ($totalPages <= 1) {
        return;
    }

    echo '<nav aria-label="Страницы"><ul class="pagination justify-content-center mt-4">';
    for ($number = 1; $number <= $totalPages; $number++) {
        $separator = str_contains($baseUrl, '?') ? '&' : '?';
        $active = $number === $page ? ' active' : '';
        echo '<li class="page-item' . $active . '"><a class="page-link" href="' .
            e($baseUrl . $separator . 'page=' . $number) . '">' . $number . '</a></li>';
    }
    echo '</ul></nav>';
}

// Проверяет пустой заголовок и максимальную длину темы.
function validateTopicTitle(string $title): ?string
{
    if ($title === '') {
        return 'Введите название темы.';
    }
    if (mb_strlen($title, 'UTF-8') > 255) {
        return 'Название темы не должно превышать 255 символов.';
    }
    return null;
}

// Проверяет обязательное описание темы и ограничивает его длину.
function validateTopicBody(string $body): ?string
{
    if ($body === '') {
        return 'Введите описание темы.';
    }
    if (mb_strlen($body, 'UTF-8') > 5000) {
        return 'Описание темы не должно превышать 5000 символов.';
    }
    return null;
}

// Проверяет пустое сообщение и максимальную длину ответа.
function validatePostContent(string $content): ?string
{
    if ($content === '') {
        return 'Введите сообщение.';
    }
    if (mb_strlen($content, 'UTF-8') > 5000) {
        return 'Сообщение не должно превышать 5000 символов.';
    }
    return null;
}

// Рендерит простую страницу ошибки с нужным HTTP-кодом.
function renderError(int $status, string $message): never
{
    http_response_code($status);
    $pageTitle = 'Ошибка';
    $errorMessage = $message;
    require dirname(__DIR__) . '/views/error.php';
    exit;
}
