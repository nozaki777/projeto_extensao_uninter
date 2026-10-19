<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/utils.php';

requireLoginApi();

$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$origem = $_GET['origem'] ?? null;

$query = "SELECT c.*, u.nome as user_name FROM chegada_fruto c JOIN users u ON c.user_id = u.id WHERE 1=1";
$params = [];

if ($start_date) {
    $query .= " AND c.data_fruto >= ?";
    $params[] = $start_date;
}
if ($end_date) {
    $query .= " AND c.data_fruto <= ?";
    $params[] = $end_date;
}
if ($origem && $origem !== 'Todas') {
    $query .= " AND c.origem = ?";
    $params[] = $origem;
}

$query .= " ORDER BY c.data_fruto DESC, c.id DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    
    foreach ($data as &$row) {
        $row['data_fruto_br'] = formatDateToBR($row['data_fruto']);
        $row['data_colheita_br'] = formatDateToBR($row['data_colheita']);
        $row['temp_transporte'] = substr($row['temp_transporte'], 0, 5); // Retorna HH:MM
    }
    
    jsonResponse(true, 'Sucesso', $data);
} catch (\PDOException $e) {
    jsonResponse(false, 'Erro ao listar: ' . $e->getMessage());
}
?>
