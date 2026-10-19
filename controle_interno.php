<?php
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/utils.php';

requireLogin();

// Permissões
$role = getUserRole();
$canEdit = in_array($role, ['admin', 'supervisor']);
$canDelete = ($role === 'admin');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Controle Interno</title>

  <!-- App favicon -->
  <link rel="shortcut icon" href="assets/img/favicon.ico">

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
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
          <li class="nav-item"><a href="controle_interno" class="nav-link active"><i class="nav-icon fas fa-clipboard-list"></i><p>Controle Interno</p></a></li>
          <li class="nav-item"><a href="chegada_fruto" class="nav-link"><i class="nav-icon fas fa-truck"></i><p>Controle de Chegada</p></a></li>
          <li class="nav-item"><a href="controle_destino" class="nav-link"><i class="nav-icon fas fa-share-square"></i><p>Controle de Destino</p></a></li>
          <li class="nav-item"><a href="ficha_coleta" class="nav-link"><i class="nav-icon fas fa-file-alt"></i><p>Ficha de Coleta</p></a></li>
          <li class="nav-item"><a href="usuarios" class="nav-link"><i class="nav-icon fas fa-users"></i><p>Usuários</p></a></li>
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
            <h1 class="m-0">Controle Interno</h1>
          </div>
          <div class="col-sm-6 text-right">
            <button class="btn btn-primary" data-toggle="modal" data-target="#controleModal" onclick="newControle()">
              <i class="fas fa-plus"></i> Novo Registro
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="content">
      <div class="container-fluid">
        <!-- Filtros -->
        <div class="card card-outline card-primary">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
          </div>
          <div class="card-body">
            <form id="filterForm" class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Data Início</label>
                  <input type="date" name="start_date" id="filterStart" class="form-control">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Data Fim</label>
                  <input type="date" name="end_date" id="filterEnd" class="form-control">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Tipo de Envase</label>
                  <select name="tipo" id="filterTipo" class="form-control">
                    <option value="Todos">Todos</option>
                    <option value="Garrafa 1L">Garrafa 1L</option>
                    <option value="Garrafa 500ml">Garrafa 500ml</option>
                    <option value="Saquinha 1Kg">Saquinha 1Kg</option>
                  </select>
                </div>
              </div>
              <div class="col-md-3 d-flex align-items-end">
                <div class="form-group">
                  <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrar</button>
                  <button type="button" class="btn btn-default" onclick="clearFilters()"><i class="fas fa-eraser"></i> Limpar</button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <div class="card">
          <div class="card-body table-responsive">
            <table id="controleTable" class="table table-bordered table-striped text-nowrap">
              <thead>
              <tr>
                <th>ID</th>
                <th>Data</th>
                <th>Lote</th>
                <th>Qtd. Produzido</th>
                <th>Tipo Envase</th>
                <th>Rendimento</th>
                <th>Usuário</th>
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

<!-- Modal Controle -->
<div class="modal fade" id="controleModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <form id="controleForm">
      <input type="hidden" name="id" id="ctrlId">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Novo Registro</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-4 form-group">
              <label>Data Chegada *</label>
              <input type="date" name="data_chegada" id="ctrlData" class="form-control" required>
            </div>
            <div class="col-md-4 form-group">
              <label>Lote *</label>
              <input type="text" name="lote" id="ctrlLote" class="form-control" required>
            </div>
            <div class="col-md-4 form-group">
              <label>Tipo Envase *</label>
              <select name="tipo_envase" id="ctrlTipo" class="form-control" required>
                <option value="">Selecione</option>
                <option value="Garrafa 1L">Garrafa 1L</option>
                <option value="Garrafa 500ml">Garrafa 500ml</option>
                <option value="Saquinha 1Kg">Saquinha 1Kg</option>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4 form-group">
              <label>Qtd. Produzido *</label>
              <input type="number" name="qtd_produzido" id="ctrlQtd" class="form-control" required>
            </div>
            <div class="col-md-4 form-group">
              <label>Rendimento</label>
              <input type="text" name="rendimento" id="ctrlRendimento" class="form-control money2">
            </div>
          </div>
          <div class="form-group">
            <label>Observações</label>
            <textarea name="obs_cont_interno" id="ctrlObs" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Salvar</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Visualizar -->
<div class="modal fade" id="viewModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalhes do Registro</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p><strong>Observações:</strong> <br><span id="viewObs"></span></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>
<!-- DataTables Buttons -->
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskplugin/1.14.16/jquery.mask.min.js"></script>

<script>
  let canEdit = <?= $canEdit ? 'true' : 'false' ?>;
  let canDelete = <?= $canDelete ? 'true' : 'false' ?>;

  $(document).ready(function() {
    if ($.fn.mask) {
      $('.money2').mask("#.##0,00", {reverse: true});
    }
    loadControle();

    $('#filterForm').submit(function(e) {
      e.preventDefault();
      loadControle();
    });

    $('#controleForm').submit(function(e) {
      e.preventDefault();
      let id = $('#ctrlId').val();
      let url = id ? 'api/edit_controle.php' : 'api/create_controle.php';
      
      $.ajax({
        url: url,
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            Swal.fire('Sucesso!', res.message || 'Registro salvo com sucesso.', 'success');
            $('#controleModal').modal('hide');
            loadControle();
          } else {
            Swal.fire('Erro!', res.message || 'Erro ao salvar o registro.', 'error');
          }
        },
        error: function() {
          Swal.fire('Erro!', 'Ocorreu um erro no servidor.', 'error');
        }
      });
    });
  });

  function loadControle() {
    let data = $('#filterForm').serialize();
    $.get('api/list_controle.php', data, function(res) {
      if (res.success) {
        let tbody = '';
        res.data.forEach(c => {
          let actions = `<button class="btn btn-sm btn-secondary" onclick='viewObs(${JSON.stringify(c.obs_cont_interno || "")})'><i class="fas fa-eye"></i></button> `;
          if (canEdit) {
            actions += `<button class="btn btn-sm btn-info" onclick='editControle(${JSON.stringify(c)})'><i class="fas fa-edit"></i></button> `;
          }
          if (canDelete) {
            actions += `<button class="btn btn-sm btn-danger" onclick="deleteControle(${c.id})"><i class="fas fa-trash"></i></button>`;
          }

          tbody += `<tr>
            <td>${c.id}</td>
            <td>${c.data_chegada_br}</td>
            <td>${c.lote}</td>
            <td>${c.qtd_produzido}</td>
            <td>${c.tipo_envase}</td>
            <td>${c.rendimento || '-'}</td>
            <td>${c.user_name}</td>
            <td>${actions}</td>
          </tr>`;
        });
        
        if ($.fn.DataTable.isDataTable('#controleTable')) {
            $('#controleTable').DataTable().destroy();
        }
        $('#controleTable tbody').html(tbody);
        $('#controleTable').DataTable({
          "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json" },
          "order": [[1, "desc"]],
          "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12 mb-3'B>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
          "buttons": [
              { extend: 'excelHtml5', className: 'btn btn-success', text: '<i class="fas fa-file-excel"></i> Excel', exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6 ] } },
              { extend: 'pdfHtml5', className: 'btn btn-danger', text: '<i class="fas fa-file-pdf"></i> PDF', exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6 ] } },
              { extend: 'print', className: 'btn btn-secondary', text: '<i class="fas fa-print"></i> Imprimir', exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6 ] } }
          ]
        });
      }
    });
  }

  function clearFilters() {
    $('#filterForm')[0].reset();
    loadControle();
  }

  function newControle() {
    $('#controleForm')[0].reset();
    $('#ctrlId').val('');
    $('#modalTitle').text('Novo Registro');
  }

  function editControle(c) {
    $('#controleForm')[0].reset();
    $('#ctrlId').val(c.id);
    $('#ctrlData').val(c.data_chegada);
    $('#ctrlLote').val(c.lote);
    $('#ctrlTipo').val(c.tipo_envase);
    $('#ctrlQtd').val(c.qtd_produzido);
    $('#ctrlRendimento').val(c.rendimento || '').trigger('input');
    $('#ctrlObs').val(c.obs_cont_interno);
    
    $('#modalTitle').text('Editar Registro');
    $('#controleModal').modal('show');
  }

  function deleteControle(id) {
    if (confirm('Deseja realmente deletar este registro?')) {
      $.post('api/delete_controle.php', {id: id}, function(res) {
        if (res.success) loadControle();
        else alert(res.message);
      });
    }
  }

  function viewObs(obs) {
    $('#viewObs').text(obs || 'Sem observações.');
    $('#viewModal').modal('show');
  }
</script>
</body>
</html>
