<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/utils.php';

requireRoleApi(['admin']); // Apenas admin deleta usuários

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método inválido.');
}

$id = $_POST['id'] ?? null;

if (!$id) {
    jsonResponse(false, 'ID não fornecido.');
}

if ($id == $_SESSION['user_id']) {
    jsonResponse(false, 'Você não pode deletar seu próprio usuário.');
}

try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    jsonResponse(true, 'Usuário deletado com sucesso.');
} catch (\PDOException $e) {
    jsonResponse(false, 'Erro ao deletar usuário: ' . $e->getMessage());
}
?>
