<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/utils.php';

requireLoginApi();

$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$tipo = $_GET['tipo'] ?? null;

$query = "SELECT c.*, u.nome as user_name FROM controle_interno c JOIN users u ON c.user_id = u.id WHERE 1=1";
$params = [];

if ($start_date) {
    $query .= " AND c.data_chegada >= ?";
    $params[] = $start_date;
}
if ($end_date) {
    $query .= " AND c.data_chegada <= ?";
    $params[] = $end_date;
}
if ($tipo && $tipo !== 'Todos') {
    $query .= " AND c.tipo_envase = ?";
    $params[] = $tipo;
}

$query .= " ORDER BY c.data_chegada DESC, c.id DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    
    // Format dates and fields for response
    foreach ($data as &$row) {
        $row['data_chegada_br'] = formatDateToBR($row['data_chegada']);
        if ($row['rendimento'] !== null) {
            $row['rendimento'] = number_format((float)$row['rendimento'], 2, ',', '');
        }
    }
    
    jsonResponse(true, 'Sucesso', $data);
} catch (\PDOException $e) {
    jsonResponse(false, 'Erro ao listar: ' . $e->getMessage());
}
?>
