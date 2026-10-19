<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';

requireLoginApi();

$data_inicio = $_GET['data_inicio'] ?? null;
$data_fim = $_GET['data_fim'] ?? null;
$aldeia_ficha = $_GET['aldeia_ficha'] ?? null;

$query = "
    SELECT f.*, u.nome as user_name 
    FROM ficha_coleta f
    LEFT JOIN users u ON f.user_id = u.id
    WHERE 1=1
";
$params = [];

if ($data_inicio) {
    $query .= " AND f.data_coleta >= ?";
    $params[] = $data_inicio;
}

if ($data_fim) {
    $query .= " AND f.data_coleta <= ?";
    $params[] = $data_fim;
}

if ($aldeia_ficha) {
    $query .= " AND f.aldeia_ficha = ?";
    $params[] = $aldeia_ficha;
}

$query .= " ORDER BY f.data_coleta DESC, f.id DESC";

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
