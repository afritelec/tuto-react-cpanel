<?php

$config = require dirname(__DIR__, 3) . '/config/tuto-react.php';

$db = new PDO("mysql:host=localhost;dbname={$config['base']};charset=utf8mb4", $config['utilisateur'], $config['mot_de_passe'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$db->exec('CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    done TINYINT(1) NOT NULL DEFAULT 0
) DEFAULT CHARSET=utf8mb4');

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET' && ($_SERVER['CONTENT_TYPE'] ?? '') !== 'application/json') {
    http_response_code(415);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true) ?? [];

if ($method === 'POST') {
    $db->prepare('INSERT INTO tasks (title) VALUES (?)')->execute([$data['title']]);
} elseif ($method === 'PATCH') {
    $db->prepare('UPDATE tasks SET title = ?, done = ? WHERE id = ?')->execute([$data['title'], (int) $data['done'], $data['id']]);
} elseif ($method === 'DELETE') {
    $db->prepare('DELETE FROM tasks WHERE id = ?')->execute([$data['id']]);
}

echo json_encode($db->query('SELECT id, title, done FROM tasks ORDER BY id DESC')->fetchAll());
