<?php
require_once __DIR__ . '/src/auth.php';
if (isLoggedIn()) {
    header('Location: dashboard');
    exit;
}
$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de Safra | Login</title>
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/img/favicon.ico">
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .login-box { margin-top: 10vh; }
    </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
<div class='d-flex justify-content-center'>
    <img style='height:5.5rem;' src="assets/img/img-1.png" alt="">
</div>    
<div class="login-logo">
    <a href="#"><b>Sistema de Safra</b></a>
</div>
    <!-- /.login-logo -->
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Faça login para iniciar sua sessão</p>

            <form id="loginForm" action="api/login" method="post">
                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="senha" class="form-control" placeholder="Senha" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember">
                            <label for="remember">
                                Lembrar-me
                            </label>
                        </div>
                    </div>
                    <!-- /.col -->
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Entrar</button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>
            <div id="loginAlert" class="alert alert-danger mt-3 <?= empty($error) ? 'd-none' : '' ?>">
                <?= htmlspecialchars($error) ?>
            </div>
        </div>
        <!-- /.login-card-body -->
    </div>
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
$(document).ready(function() {
    $('#loginForm').submit(function(e) {
        e.preventDefault();
        var form = $(this);
        var alertBox = $('#loginAlert');
        alertBox.addClass('d-none').text('');

        $.ajax({
            type: "POST",
            url: form.attr('action') + '.php',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    window.location.href = 'dashboard';
                } else {
                    alertBox.text(response.message).removeClass('d-none');
                }
            },
            error: function() {
                alertBox.text('Erro ao comunicar com o servidor.').removeClass('d-none');
            }
        });
    });
});
</script>
</body>
</html>
