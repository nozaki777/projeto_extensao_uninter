<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/utils.php';

requireLoginApi();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método inválido.');
}

$data_destino = sanitizeInput($_POST['data_destino'] ?? '');
$lote_destino = sanitizeInput($_POST['lote_destino'] ?? '');
$local_destino = sanitizeInput($_POST['local_destino'] ?? '');
$tipo_envase = sanitizeInput($_POST['tipo_envase'] ?? '');
$qtd_destino = sanitizeInput($_POST['qtd_destino'] ?? '0');
$valor_venda = sanitizeInput($_POST['valor_venda'] ?? '0');

$user_id = $_SESSION['user_id'];

if (empty($data_destino) || empty($lote_destino) || empty($local_destino) || empty($tipo_envase) || empty($qtd_destino)) {
    jsonResponse(false, 'Campos obrigatórios não preenchidos.');
}

// Format valor_venda (remove dot for thousand separators, replace comma with dot)
$valor_venda = str_replace('.', '', $valor_venda);
$valor_venda = str_replace(',', '.', $valor_venda);
$valor_venda = (float) $valor_venda;
$qtd_destino = (int) $qtd_destino;

try {
    $stmt = $pdo->prepare("
        INSERT INTO destino_prod 
        (data_destino, lote_destino, local_destino, tipo_envase, qtd_destino, valor_venda, user_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$data_destino, $lote_destino, $local_destino, $tipo_envase, $qtd_destino, $valor_venda, $user_id]);
    
    jsonResponse(true, 'Registro criado com sucesso.');
} catch (\PDOException $e) {
    jsonResponse(false, 'Erro ao criar registro: ' . $e->getMessage());
}
?>
