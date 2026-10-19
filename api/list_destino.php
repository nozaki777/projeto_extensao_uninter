<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';

requireLoginApi();

$data_inicio = $_GET['data_inicio'] ?? null;
$data_fim = $_GET['data_fim'] ?? null;
$local_destino = $_GET['local_destino'] ?? null;

$query = "
    SELECT d.*, u.nome as user_name 
    FROM destino_prod d
    LEFT JOIN users u ON d.user_id = u.id
    WHERE 1=1
";
$params = [];

if ($data_inicio) {
    $query .= " AND d.data_destino >= ?";
    $params[] = $data_inicio;
}

if ($data_fim) {
    $query .= " AND d.data_destino <= ?";
    $params[] = $data_fim;
}

if ($local_destino) {
    $query .= " AND d.local_destino = ?";
    $params[] = $local_destino;
}

$query .= " ORDER BY d.data_destino DESC, d.id DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode(['data' => $results]);
} catch (\PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['data' => [], 'error' => $e->getMessage()]);
}
?>
