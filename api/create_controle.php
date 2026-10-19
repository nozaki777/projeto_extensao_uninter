<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/utils.php';

requireLoginApi();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método inválido.');
}

$data_chegada = sanitizeInput($_POST['data_chegada'] ?? '');
$lote = sanitizeInput($_POST['lote'] ?? '');
$qtd_produzido = !empty($_POST['qtd_produzido']) ? (int)$_POST['qtd_produzido'] : 0;
$tipo_envase = sanitizeInput($_POST['tipo_envase'] ?? '');
$rendimento = !empty($_POST['rendimento']) ? (float)str_replace(',', '.', str_replace('.', '', $_POST['rendimento'])) : null;
$obs_cont_interno = sanitizeInput($_POST['obs_cont_interno'] ?? '');

$user_id = $_SESSION['user_id'];

if (empty($data_chegada) || empty($lote) || empty($qtd_produzido) || empty($tipo_envase)) {
    jsonResponse(false, 'Data, Lote, Quantidade e Tipo são obrigatórios.');
}

try {
    $stmt = $pdo->prepare("INSERT INTO controle_interno (data_chegada, lote, qtd_produzido, tipo_envase, rendimento, obs_cont_interno, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$data_chegada, $lote, $qtd_produzido, $tipo_envase, $rendimento, $obs_cont_interno, $user_id]);
    jsonResponse(true, 'Registro criado com sucesso.');
} catch (\PDOException $e) {
    jsonResponse(false, 'Erro ao criar registro: ' . $e->getMessage());
}
?>
