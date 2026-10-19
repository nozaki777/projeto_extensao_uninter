<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/utils.php';

requireLoginApi(); // Todos podem ver a lista de usuários

try {
    $stmt = $pdo->query("SELECT id, nome, email, contato, role, created_at FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll();
    jsonResponse(true, 'Sucesso', $users);
} catch (\PDOException $e) {
    jsonResponse(false, 'Erro ao listar usuários: ' . $e->getMessage());
}
?>
