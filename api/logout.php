<?php
require_once __DIR__ . '/../src/auth.php';
session_destroy();
header('Location: ../index');
exit;
?>
