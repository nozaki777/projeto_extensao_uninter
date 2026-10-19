<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/utils.php';

requireRoleApi(['admin']); // Apenas admin deleta

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método inválido.');
}

$id = $_POST['id'] ?? null;

if (!$id) {
    jsonResponse(false, 'ID não fornecido.');
}

try {
    $stmt = $pdo->prepare("DELETE FROM chegada_fruto WHERE id = ?");
    $stmt->execute([$id]);
    jsonResponse(true, 'Registro deletado com sucesso.');
} catch (\PDOException $e) {
    jsonResponse(false, 'Erro ao deletar: ' . $e->getMessage());
}
?>
