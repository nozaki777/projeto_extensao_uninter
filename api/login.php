<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/utils.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método inválido.');
}

$email = sanitizeInput($_POST['email'] ?? '');
$senha = sanitizeInput($_POST['senha'] ?? '');

if (empty($email) || empty($senha)) {
    jsonResponse(false, 'Preencha todos os campos.');
}

try {
    $stmt = $pdo->prepare("SELECT id, nome, email, senha_hash, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($senha, $user['senha_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nome'];
        $_SESSION['user_role'] = $user['role'];
        jsonResponse(true, 'Login realizado com sucesso.');
    } else {
        jsonResponse(false, 'E-mail ou senha incorretos.');
    }
} catch (\PDOException $e) {
    jsonResponse(false, 'Erro no servidor. Tente novamente mais tarde.');
}
?>
