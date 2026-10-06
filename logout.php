<?php
require 'config.php';

// Выход меняет состояние сессии, поэтому принимаем только POST с действительным токеном.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Используйте форму выхода на сайте.');
}
verifyCsrfToken();
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit;
