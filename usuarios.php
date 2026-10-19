<?php
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/utils.php';

requireLogin();

// Qualquer um pode ver usuários? O requisito diz:
// Admin: visualizar, editar, deletar todos
// Supervisor: visualizar e editar todos
// Colaborador: visualizar e editar apenas o seu usuário (mas precisa da tela para isso)
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sistema de Safra | Usuários</title>

  <!-- App favicon -->
  <link rel="shortcut icon" href="assets/img/favicon.ico">

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a class="nav-link" href="api/logout" role="button">
          <i class="fas fa-sign-out-alt"></i> Sair
        </a>
      </li>
    </ul>
  </nav>

  <!-- Sidebar -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="dashboard" class="brand-link">
      <span class="brand-text font-weight-light ml-3"><b>Sistema de Safra - UASEI</b></span>
    </a>
    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="info">
          <a href="#" class="d-block"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuário') ?></a>
        </div>
      </div>
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <li class="nav-item"><a href="dashboard" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard Interno</p></a></li>
          <li class="nav-item"><a href="dashboard_chegada" class="nav-link"><i class="nav-icon fas fa-chart-pie"></i><p>Dashboard Chegada</p></a></li>
          <li class="nav-item"><a href="dashboard_destino" class="nav-link"><i class="nav-icon fas fa-chart-bar"></i><p>Dashboard Destino</p></a></li>
          <li class="nav-item"><a href="dashboard_coleta" class="nav-link"><i class="nav-icon fas fa-chart-line"></i><p>Dashboard Coleta</p></a></li>
          <li class="nav-item"><a href="controle_interno" class="nav-link"><i class="nav-icon fas fa-clipboard-list"></i><p>Controle Interno</p></a></li>
          <li class="nav-item"><a href="chegada_fruto" class="nav-link"><i class="nav-icon fas fa-truck"></i><p>Controle de Chegada</p></a></li>
          <li class="nav-item"><a href="controle_destino" class="nav-link"><i class="nav-icon fas fa-share-square"></i><p>Controle de Destino</p></a></li>
          <li class="nav-item"><a href="ficha_coleta" class="nav-link"><i class="nav-icon fas fa-file-alt"></i><p>Ficha de Coleta</p></a></li>
          <li class="nav-item"><a href="usuarios" class="nav-link active"><i class="nav-icon fas fa-users"></i><p>Usuários</p></a></li>
        </ul>
      </nav>
    </div>
  </aside>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Gestão de Usuários</h1>
          </div>
          <div class="col-sm-6 text-right">
            <?php if (getUserRole() === 'admin'): ?>
            <button class="btn btn-primary" data-toggle="modal" data-target="#userModal" onclick="newUser()">
              <i class="fas fa-plus"></i> Novo Usuário
            </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="content">
      <div class="container-fluid">
        <div class="card">
          <div class="card-body">
            <table id="usersTable" class="table table-bordered table-striped">
              <thead>
              <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Contato</th>
                <th>Nível</th>
                <th>Ações</th>
              </tr>
              </thead>
              <tbody>
                <!-- Load via AJAX -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <footer class="main-footer">
    <strong>Copyright &copy; 2026 UASEI.</strong>
  </footer>
</div>

<!-- Modal Usuário -->
<div class="modal fade" id="userModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <form id="userForm">
      <input type="hidden" name="id" id="userId">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Novo Usuário</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nome</label>
            <input type="text" name="nome" id="userName" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" id="userEmail" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Contato</label>
            <input type="text" name="contato" id="userContato" class="form-control">
          </div>
          <div class="form-group">
            <label>Senha <small>(Deixe em branco para não alterar)</small></label>
            <input type="password" name="senha" id="userSenha" class="form-control">
          </div>
          <?php if (getUserRole() === 'admin'): ?>
          <div class="form-group">
            <label>Role</label>
            <select name="role" id="userRole" class="form-control">
              <option value="colaborador">Colaborador</option>
              <option value="supervisor">Supervisor</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <?php endif; ?>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Salvar</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
  let table;
  let loggedUserId = <?= $_SESSION['user_id'] ?>;
  let loggedUserRole = '<?= getUserRole() ?>';

  $(document).ready(function() {
    loadUsers();

    $('#userForm').submit(function(e) {
      e.preventDefault();
      let id = $('#userId').val();
      let url = id ? 'api/edit_usuario.php' : 'api/create_usuario.php';
      
      $.ajax({
        url: url,
        type: 'POST',
        data: $(this).serialize(),
        success: function(res) {
          if (res.success) {
            $('#userModal').modal('hide');
            loadUsers();
          } else {
            alert(res.message);
          }
        }
      });
    });
  });

  function loadUsers() {
    $.get('api/list_usuarios.php', function(res) {
      if (res.success) {
        let tbody = '';
        res.data.forEach(u => {
          // Colaborador só vê a si mesmo, ou se a API retornar tudo, filtramos aqui ou no backend.
          // O backend (list_usuarios) só permite admin/supervisor. Mas se colaborador acessar essa página?
          // O frontend esconde a lista se a API falhar.
          
          let actions = '';
          if (loggedUserRole === 'admin' || (loggedUserRole === 'supervisor' && u.role !== 'admin') || u.id == loggedUserId) {
            actions += `<button class="btn btn-sm btn-info" onclick='editUser(${JSON.stringify(u).replace(/'/g, "&#39;")})'><i class="fas fa-edit"></i></button> `;
          }
          if (loggedUserRole === 'admin' && u.id != loggedUserId) {
            actions += `<button class="btn btn-sm btn-danger" onclick="deleteUser(${u.id})"><i class="fas fa-trash"></i></button>`;
          }

          tbody += `<tr>
            <td>${u.id}</td>
            <td>${u.nome}</td>
            <td>${u.email}</td>
            <td>${u.contato || ''}</td>
            <td>${u.role}</td>
            <td>${actions}</td>
          </tr>`;
        });
        
        if ($.fn.DataTable.isDataTable('#usersTable')) {
            $('#usersTable').DataTable().destroy();
        }
        $('#usersTable tbody').html(tbody);
        $('#usersTable').DataTable({
          "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json" }
        });
      } else {
        $('#usersTable tbody').html(`<tr><td colspan="6" class="text-center">${res.message}</td></tr>`);
      }
    });
  }

  function newUser() {
    $('#userForm')[0].reset();
    $('#userId').val('');
    $('#modalTitle').text('Novo Usuário');
    $('#userSenha').attr('required', true);
  }

  function editUser(user) {
    $('#userForm')[0].reset();
    $('#userId').val(user.id);
    $('#userName').val(user.nome);
    $('#userEmail').val(user.email);
    $('#userContato').val(user.contato);
    if ($('#userRole').length) $('#userRole').val(user.role);
    $('#userSenha').removeAttr('required');

    if (loggedUserRole === 'colaborador') {
        $('#userName, #userEmail, #userContato').prop('readonly', true);
    } else {
        $('#userName, #userEmail, #userContato').prop('readonly', false);
        $('#userSenha').prop('readonly', false).attr('placeholder', '');
    }

    $('#modalTitle').text('Editar Usuário');
    $('#userModal').modal('show');
  }

  function deleteUser(id) {
    if (confirm('Deseja realmente deletar este usuário?')) {
      $.post('api/delete_usuario.php', {id: id}, function(res) {
        if (res.success) loadUsers();
        else alert(res.message);
      });
    }
  }
</script>
</body>
</html>
