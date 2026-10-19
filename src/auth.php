<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

function requireRole($allowedRoles) {
    requireLogin();
    if (!in_array(getUserRole(), $allowedRoles)) {
        http_response_code(403);
        die("Acesso Negado. Você não tem permissão para visualizar esta página.");
    }
}

// Para usar nas requisições da API que retornam JSON em vez de redirect
function requireLoginApi() {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Não autenticado.']);
        exit;
    }
}

function requireRoleApi($allowedRoles) {
    requireLoginApi();
    if (!in_array(getUserRole(), $allowedRoles)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
        exit;
    }
}
?>
