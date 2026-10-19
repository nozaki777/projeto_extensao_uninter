<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/utils.php';

requireRoleApi(['admin']); // Apenas admin pode criar

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método inválido.');
}

$nome = sanitizeInput($_POST['nome'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$contato = sanitizeInput($_POST['contato'] ?? '');
$senha = sanitizeInput($_POST['senha'] ?? '');
$role = sanitizeInput($_POST['role'] ?? 'colaborador');

if (empty($nome) || empty($email) || empty($senha)) {
    jsonResponse(false, 'Nome, Email e Senha são obrigatórios.');
}

if (!in_array($role, ['admin', 'supervisor', 'colaborador'])) {
    jsonResponse(false, 'Role inválido.');
}

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        jsonResponse(false, 'Email já cadastrado.');
    }

    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (nome, email, contato, senha_hash, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nome, $email, $contato, $senha_hash, $role]);
    
    jsonResponse(true, 'Usuário criado com sucesso.');
} catch (\PDOException $e) {
    jsonResponse(false, 'Erro ao criar usuário: ' . $e->getMessage());
}
?>
