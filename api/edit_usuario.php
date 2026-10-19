<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/utils.php';

requireLoginApi();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método inválido.');
}

$id = $_POST['id'] ?? null;
$nome = sanitizeInput($_POST['nome'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$contato = sanitizeInput($_POST['contato'] ?? '');
$senha = sanitizeInput($_POST['senha'] ?? '');
$role = sanitizeInput($_POST['role'] ?? '');

$loggedUserId = $_SESSION['user_id'];
$loggedUserRole = getUserRole();

if (!$id || empty($nome) || empty($email)) {
    jsonResponse(false, 'Campos obrigatórios não preenchidos.');
}

// Verifica permissões
if ($loggedUserRole === 'colaborador' && $id != $loggedUserId) {
    jsonResponse(false, 'Você só pode editar o seu próprio usuário.');
}

try {
    if ($loggedUserRole === 'colaborador') {
        if (empty($senha)) {
            jsonResponse(false, 'Nenhuma senha informada para atualização.');
        }
        $updateQuery = "UPDATE users SET senha_hash = ? WHERE id = ?";
        $params = [password_hash($senha, PASSWORD_DEFAULT), $id];
    } else {
        // Check se email já existe em outro id
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            jsonResponse(false, 'Email já está em uso por outro usuário.');
        }

        // Check target user's role to enforce supervisor restrictions
        $stmtTarget = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmtTarget->execute([$id]);
        $targetRole = $stmtTarget->fetchColumn();

        if ($loggedUserRole === 'supervisor' && $targetRole === 'admin') {
            jsonResponse(false, 'Supervisores não têm permissão para editar usuários Administradores.');
        }

        $updateQuery = "UPDATE users SET nome = ?, email = ?, contato = ?";
        $params = [$nome, $email, $contato];

        if (!empty($senha)) {
            $updateQuery .= ", senha_hash = ?";
            $params[] = password_hash($senha, PASSWORD_DEFAULT);
        }

        if ($loggedUserRole === 'admin' && !empty($role)) {
            $updateQuery .= ", role = ?";
            $params[] = $role;
        }

        $updateQuery .= " WHERE id = ?";
        $params[] = $id;
    }

    $stmt = $pdo->prepare($updateQuery);
    $stmt->execute($params);

    jsonResponse(true, 'Usuário atualizado com sucesso.');
} catch (\PDOException $e) {
    jsonResponse(false, 'Erro ao atualizar usuário: ' . $e->getMessage());
}
?>
