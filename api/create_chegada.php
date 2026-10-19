<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/utils.php';

requireLoginApi();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método inválido.');
}

$data_fruto = sanitizeInput($_POST['data_fruto'] ?? '');
$lote_fruto = sanitizeInput($_POST['lote_fruto'] ?? '');
$fornecedor = sanitizeInput($_POST['fornecedor'] ?? '');
$origem = sanitizeInput($_POST['origem'] ?? '');
$temp_transporte = sanitizeInput($_POST['temp_transporte'] ?? '');
$data_colheita = sanitizeInput($_POST['data_colheita'] ?? '');
$tipo_embalagem = sanitizeInput($_POST['tipo_embalagem'] ?? '');
$qtd_saca = !empty($_POST['qtd_saca']) ? (float)str_replace(',', '.', $_POST['qtd_saca']) : 0;
$maturacao = sanitizeInput($_POST['maturacao'] ?? '');
$odor_fruto = sanitizeInput($_POST['odor_fruto'] ?? '');
$cor_fruto = sanitizeInput($_POST['cor_fruto'] ?? '');
$situacao = sanitizeInput($_POST['situacao'] ?? 'Selecione');
$valor_real = !empty($_POST['valor_real']) ? (float)str_replace(',', '.', str_replace('.', '', $_POST['valor_real'])) : 0.00;
$valor_frete = !empty($_POST['valor_frete']) ? (float)str_replace(',', '.', str_replace('.', '', $_POST['valor_frete'])) : 0.00;
$nome_motorista = sanitizeInput($_POST['nome_motorista'] ?? '');
$gasolina_valor = !empty($_POST['gasolina_valor']) ? (float)str_replace(',', '.', str_replace('.', '', $_POST['gasolina_valor'])) : 0.00;

$user_id = $_SESSION['user_id'];

if (empty($data_fruto) || empty($lote_fruto) || empty($fornecedor) || empty($origem) || empty($qtd_saca)) {
    jsonResponse(false, 'Data, Lote, Fornecedor, Origem e Quantidade de Sacas são obrigatórios.');
}

try {
    $stmt = $pdo->prepare("INSERT INTO chegada_fruto 
        (data_fruto, lote_fruto, fornecedor, origem, temp_transporte, data_colheita, tipo_embalagem, qtd_saca, maturacao, odor_fruto, cor_fruto, situacao, valor_real, valor_frete, nome_motorista, gasolina_valor, user_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
    $stmt->execute([
        $data_fruto, $lote_fruto, $fornecedor, $origem, $temp_transporte, $data_colheita, 
        $tipo_embalagem, $qtd_saca, $maturacao, $odor_fruto, $cor_fruto, $situacao, 
        $valor_real, $valor_frete, $nome_motorista, $gasolina_valor, $user_id
    ]);
    
    jsonResponse(true, 'Registro criado com sucesso.');
} catch (\PDOException $e) {
    jsonResponse(false, 'Erro ao criar registro: ' . $e->getMessage());
}
?>
