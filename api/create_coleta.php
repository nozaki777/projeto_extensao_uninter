<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/utils.php';

requireLoginApi();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método inválido.');
}

$user_id = $_SESSION['user_id'];

$data_coleta = sanitizeInput($_POST['data_coleta'] ?? '');
$aldeia_ficha = sanitizeInput($_POST['aldeia_ficha'] ?? '');
$coletor_resposavel = sanitizeInput($_POST['coletor_resposavel'] ?? '');
$qtd_ajudantes = (int)($_POST['qtd_ajudantes'] ?? 0);
$qtd_familias = (int)($_POST['qtd_familias'] ?? 0);
$local_coleta = sanitizeInput($_POST['local_coleta'] ?? '');
$fora_qtd_saca = (int)($_POST['fora_qtd_saca'] ?? 0);

$fora_preco_saca = sanitizeInput($_POST['fora_preco_saca'] ?? '0');
$fora_preco_saca = (float)str_replace(',', '.', str_replace('.', '', $fora_preco_saca));

$fora_quem_comprou = sanitizeInput($_POST['fora_quem_comprou'] ?? '');
$local_comprador = sanitizeInput($_POST['local_comprador'] ?? '');

$consumo_qtd_saca = (int)($_POST['consumo_qtd_saca'] ?? 0);
$consumo_qtd_litros = (int)($_POST['consumo_qtd_litros'] ?? 0);
$venda_qtd_saca = (int)($_POST['venda_qtd_saca'] ?? 0);
$venda_qtd_litros = (int)($_POST['venda_qtd_litros'] ?? 0);

$venda_preco_litro = sanitizeInput($_POST['venda_preco_litro'] ?? '0');
$venda_preco_litro = (float)str_replace(',', '.', str_replace('.', '', $venda_preco_litro));

$fora_valor_total = $fora_preco_saca * $fora_qtd_saca;
$venda_valor_total = $venda_preco_litro * $venda_qtd_litros;

$qtd_sacas_total = $fora_qtd_saca + $consumo_qtd_saca + $venda_qtd_saca;

if (empty($data_coleta) || empty($aldeia_ficha) || empty($coletor_resposavel) || empty($local_coleta)) {
    jsonResponse(false, 'Campos obrigatórios não preenchidos.');
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO ficha_coleta (
            data_coleta, aldeia_ficha, coletor_resposavel, qtd_ajudantes, qtd_familias, local_coleta,
            fora_qtd_saca, fora_preco_saca, fora_valor_total, fora_quem_comprou, local_comprador,
            consumo_qtd_saca, consumo_qtd_litros, venda_qtd_saca, venda_qtd_litros, venda_preco_litro,
            venda_valor_total, qtd_sacas_total, user_id
        ) VALUES (
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?
        )
    ");
    $stmt->execute([
        $data_coleta, $aldeia_ficha, $coletor_resposavel, $qtd_ajudantes, $qtd_familias, $local_coleta,
        $fora_qtd_saca, $fora_preco_saca, $fora_valor_total, $fora_quem_comprou, $local_comprador,
        $consumo_qtd_saca, $consumo_qtd_litros, $venda_qtd_saca, $venda_qtd_litros, $venda_preco_litro,
        $venda_valor_total, $qtd_sacas_total, $user_id
    ]);
    
    jsonResponse(true, 'Registro criado com sucesso.');
} catch (\PDOException $e) {
    jsonResponse(false, 'Erro ao criar registro: ' . $e->getMessage());
}
?>
