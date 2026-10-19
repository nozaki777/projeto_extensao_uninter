<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/utils.php';

requireLoginApi();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método inválido.');
}

$role = getUserRole();
if ($role !== 'admin') {
    jsonResponse(false, 'Apenas administradores podem excluir registros.');
}

$id = $_POST['id'] ?? null;

if (!$id) {
    jsonResponse(false, 'ID não fornecido.');
}

try {
    $stmt = $pdo->prepare("DELETE FROM ficha_coleta WHERE id = ?");
    $stmt->execute([$id]);
    
    jsonResponse(true, 'Registro excluído com sucesso.');
} catch (\PDOException $e) {
    jsonResponse(false, 'Erro ao excluir registro: ' . $e->getMessage());
}
?>
