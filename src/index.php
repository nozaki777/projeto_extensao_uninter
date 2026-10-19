<?php
session_start();
$_SESSION['login_error'] = "Acesso Negado: Você não tem permissão para acessar este diretório.";
header("Location: ../index");
exit;
