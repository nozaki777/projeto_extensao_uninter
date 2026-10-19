<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/utils.php';

requireRoleApi(['admin', 'supervisor']); // Apenas admin e supervisor editam

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método inválido.');
}

$id = $_POST['id'] ?? null;
$data_chegada = sanitizeInput($_POST['data_chegada'] ?? '');
$lote = sanitizeInput($_POST['lote'] ?? '');
$qtd_produzido = !empty($_POST['qtd_produzido']) ? (int)$_POST['qtd_produzido'] : 0;
$tipo_envase = sanitizeInput($_POST['tipo_envase'] ?? '');
$rendimento = !empty($_POST['rendimento']) ? (float)str_replace(',', '.', str_replace('.', '', $_POST['rendimento'])) : null;
$obs_cont_interno = sanitizeInput($_POST['obs_cont_interno'] ?? '');

if (!$id || empty($data_chegada) || empty($lote) || empty($qtd_produzido) || empty($tipo_envase)) {
    jsonResponse(false, 'Campos obrigatórios não preenchidos.');
}

try {
    $stmt = $pdo->prepare("UPDATE controle_interno SET data_chegada=?, lote=?, qtd_produzido=?, tipo_envase=?, rendimento=?, obs_cont_interno=? WHERE id=?");
    $stmt->execute([$data_chegada, $lote, $qtd_produzido, $tipo_envase, $rendimento, $obs_cont_interno, $id]);
    jsonResponse(true, 'Registro atualizado com sucesso.');
} catch (\PDOException $e) {
    jsonResponse(false, 'Erro ao atualizar: ' . $e->getMessage());
}
?>
