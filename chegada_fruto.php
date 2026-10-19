<?php
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/utils.php';

requireLogin();

// Permissões
$role = getUserRole();
$canEdit = in_array($role, ['admin', 'supervisor']);
$canDelete = ($role === 'admin');

$origens = ['Açaizal', 'Açaizal-Igarapé', 'Ahumã', 'Anawerá', 'Ariramba', 'Benoá', 'Curipi', 'Estrela', 'Flamã', 'Galibi', 'Japiim', 'Kamuywa', 'Kariá', 'Karibuen', 'Kuahí', 'Kumarumã', 'Kumenê', 'Kunanã', 'Manga', 'Piquiá', 'Samaúma', 'Santa Isabel', 'Tukay', 'Tuluhi', 'Uahá', 'Wapá', 'Ykawakun', 'Ywawka', 'Zacarias'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Controle de Chegada de Frutos</title>

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
      <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item"><a class="nav-link" href="api/logout" role="button"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
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
          <li class="nav-item"><a href="chegada_fruto" class="nav-link active"><i class="nav-icon fas fa-truck"></i><p>Controle de Chegada</p></a></li>
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
            <h1 class="m-0">Controle de Chegada de Frutos</h1>
          </div>
          <div class="col-sm-6 text-right">
            <button class="btn btn-primary" data-toggle="modal" data-target="#chegadaModal" onclick="newChegada()">
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
          <div class="card-header"><h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3></div>
          <div class="card-body">
            <form id="filterForm" class="row">
              <div class="col-md-3 form-group">
                <label>Data Início</label>
                <input type="date" name="start_date" id="filterStart" class="form-control">
              </div>
              <div class="col-md-3 form-group">
                <label>Data Fim</label>
                <input type="date" name="end_date" id="filterEnd" class="form-control">
              </div>
              <div class="col-md-3 form-group">
                <label>Origem</label>
                <select name="origem" id="filterOrigem" class="form-control">
                  <option value="Todas">Todas</option>
                  <?php foreach ($origens as $o): ?>
                  <option value="<?= htmlspecialchars($o) ?>"><?= htmlspecialchars($o) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3 d-flex align-items-end form-group">
                <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-search"></i> Filtrar</button>
                <button type="button" class="btn btn-default" onclick="clearFilters()"><i class="fas fa-eraser"></i> Limpar</button>
              </div>
            </form>
          </div>
        </div>

        <div class="card">
          <div class="card-body table-responsive">
            <table id="chegadaTable" class="table table-bordered table-striped text-nowrap">
              <thead>
              <tr>
                <th>ID</th>
                <th>Data Fruto</th>
                <th>Lote</th>
                <th>Fornecedor</th>
                <th>Origem</th>
                <th>Qtd. Sacas</th>
                <th>Situação</th>
                <th>Valor Compras (R$)</th>
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

  <footer class="main-footer"><strong>Copyright &copy; 2026 UASEI.</strong></footer>
</div>

<!-- Modal Chegada -->
<div class="modal fade" id="chegadaModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl" role="document">
    <form id="chegadaForm">
      <input type="hidden" name="id" id="formId">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Novo Registro de Chegada</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-3 form-group">
              <label>Data Fruto *</label>
              <input type="date" name="data_fruto" id="formDataFruto" class="form-control" required>
            </div>
            <div class="col-md-3 form-group">
              <label>Lote Fruto *</label>
              <input type="text" name="lote_fruto" id="formLote" class="form-control" required>
            </div>
            <div class="col-md-3 form-group">
              <label>Fornecedor *</label>
              <input type="text" name="fornecedor" id="formFornecedor" class="form-control" required>
            </div>
            <div class="col-md-3 form-group">
              <label>Origem *</label>
              <select name="origem" id="formOrigem" class="form-control" required>
                <option value="">Selecione...</option>
                <?php foreach ($origens as $o): ?>
                <option value="<?= htmlspecialchars($o) ?>"><?= htmlspecialchars($o) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-3 form-group">
              <label>Tempo Transporte (Horas) *</label>
              <input type="time" name="temp_transporte" id="formTemp" class="form-control" required>
            </div>
            <div class="col-md-3 form-group">
              <label>Data Colheita *</label>
              <input type="date" name="data_colheita" id="formDataColheita" class="form-control" required>
            </div>
            <div class="col-md-3 form-group">
              <label>Tipo Embalagem</label>
              <input type="text" name="tipo_embalagem" id="formEmbalagem" class="form-control">
            </div>
            <div class="col-md-3 form-group">
              <label>Qtd. Sacas *</label>
              <input type="number" name="qtd_saca" id="formQtdSaca" class="form-control" step="any" required>
            </div>
          </div>
          <div class="row">
            <div class="col-md-3 form-group">
              <label>Maturação</label>
              <select name="maturacao" id="formMaturacao" class="form-control" required>
                <option value="">Selecione</option>
                <option value="Maduro/parral">Maduro/parral</option>
                <option value="Verde">Verde</option>
              </select>
            </div>
            <div class="col-md-3 form-group">
              <label>Aroma do Fruto</label>
              <select name="odor_fruto" id="formOdor" class="form-control" required>
                <option value="">Selecione</option>
                <option value="Aroma Natural">Aroma Natural</option>
                <option value="Aroma Azedo">Aroma Azedo</option>
              </select>
            </div>
            <div class="col-md-3 form-group">
              <label>Cor do Fruto</label>
              <select name="cor_fruto" id="formCor" class="form-control" required>
                <option value="">Selecione</option>
                <option value="Roxo">Roxo</option>
                <option value="Verde">Verde</option>
              </select>
            </div>
            <div class="col-md-3 form-group">
              <label>Situação</label>
              <select name="situacao" id="formSituacao" class="form-control" required>
                <option value="">Selecione</option>
                <option value="Aprovado">Aprovado</option>
                <option value="Reprovado">Reprovado</option>
              </select>
            </div>
          </div>
          <h5 class="mt-3 border-bottom pb-2">Informações Financeiras</h5>
          <div class="row">
            <div class="col-md-3 form-group">
              <label>Valor em Compras (R$)</label>
              <input type="text" name="valor_real" id="formValorReal" class="form-control money2" required>
            </div>
            <div class="col-md-3 form-group">
              <label>Valor do Frete (R$)</label>
              <input type="text" name="valor_frete" id="formValorFrete" class="form-control money2">
            </div>
            <div class="col-md-3 form-group">
              <label>Nome do Motorista</label>
              <input type="text" name="nome_motorista" id="formMotorista" class="form-control">
            </div>
            <div class="col-md-3 form-group">
              <label>Valor Gasolina (R$)</label>
              <input type="text" name="gasolina_valor" id="formGasolina" class="form-control money2">
            </div>
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
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalhes do Registro</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="viewDetails"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
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
    loadChegada();

    $('#filterForm').submit(function(e) {
      e.preventDefault();
      loadChegada();
    });

    $('#chegadaForm').submit(function(e) {
      e.preventDefault();
      let id = $('#formId').val();
      let url = id ? 'api/edit_chegada.php' : 'api/create_chegada.php';
      
      $.ajax({
        url: url,
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            Swal.fire('Sucesso!', res.message || 'Registro salvo com sucesso.', 'success');
            $('#chegadaModal').modal('hide');
            loadChegada();
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

  function loadChegada() {
    let data = $('#filterForm').serialize();
    $.get('api/list_chegada.php', data, function(res) {
      if (res.success) {
        let tbody = '';
        res.data.forEach(c => {
          let actions = `<button class="btn btn-sm btn-secondary" title="Visualizar" onclick='viewChegada(${JSON.stringify(c).replace(/'/g, "&#39;")})'><i class="fas fa-eye"></i></button> `;
          if (canEdit) {
            actions += `<button class="btn btn-sm btn-info" title="Editar" onclick='editChegada(${JSON.stringify(c).replace(/'/g, "&#39;")})'><i class="fas fa-edit"></i></button> `;
          }
          if (canDelete) {
            actions += `<button class="btn btn-sm btn-danger" title="Excluir" onclick="deleteChegada(${c.id})"><i class="fas fa-trash"></i></button>`;
          }

          let valReal = parseFloat(c.valor_real).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
          let sacaFormatted = parseFloat(c.qtd_saca).toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
          
          tbody += `<tr>
            <td>${c.id}</td>
            <td>${c.data_fruto_br}</td>
            <td>${c.lote_fruto}</td>
            <td>${c.fornecedor}</td>
            <td>${c.origem}</td>
            <td>${sacaFormatted}</td>
            <td><span class="badge ${c.situacao == 'Aprovado' ? 'badge-success' : (c.situacao == 'Reprovado' ? 'badge-danger' : 'badge-secondary')}">${c.situacao}</span></td>
            <td>${valReal}</td>
            <td>${c.user_name}</td>
            <td>${actions}</td>
          </tr>`;
        });
        
        if ($.fn.DataTable.isDataTable('#chegadaTable')) {
            $('#chegadaTable').DataTable().destroy();
        }
        $('#chegadaTable tbody').html(tbody);
        $('#chegadaTable').DataTable({
          "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json" },
          "order": [[1, "desc"]],
          "scrollX": true,
          "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12 mb-3'B>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
          "buttons": [
              { extend: 'excelHtml5', className: 'btn btn-success', text: '<i class="fas fa-file-excel"></i> Excel', exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6, 7, 8 ] } },
              { extend: 'pdfHtml5', className: 'btn btn-danger', text: '<i class="fas fa-file-pdf"></i> PDF', orientation: 'landscape', exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6, 7, 8 ] } },
              { extend: 'print', className: 'btn btn-secondary', text: '<i class="fas fa-print"></i> Imprimir', exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6, 7, 8 ] } }
          ]
        });
      }
    });
  }

  function clearFilters() {
    $('#filterForm')[0].reset();
    loadChegada();
  }

  function newChegada() {
    $('#chegadaForm')[0].reset();
    $('#formId').val('');
    $('#modalTitle').text('Novo Registro de Chegada');
  }

  function editChegada(c) {
    $('#chegadaForm')[0].reset();
    $('#formId').val(c.id);
    $('#formDataFruto').val(c.data_fruto);
    $('#formLote').val(c.lote_fruto);
    $('#formFornecedor').val(c.fornecedor);
    $('#formOrigem').val(c.origem);
    $('#formTemp').val(c.temp_transporte);
    $('#formDataColheita').val(c.data_colheita);
    $('#formEmbalagem').val(c.tipo_embalagem);
    $('#formQtdSaca').val(parseFloat(c.qtd_saca));
    $('#formMaturacao').val(c.maturacao);
    $('#formOdor').val(c.odor_fruto);
    $('#formCor').val(c.cor_fruto);
    $('#formSituacao').val(c.situacao);
    let vReal = parseFloat(c.valor_real || 0).toFixed(2).replace('.', ',');
    let vFrete = parseFloat(c.valor_frete || 0).toFixed(2).replace('.', ',');
    let vGasolina = parseFloat(c.gasolina_valor || 0).toFixed(2).replace('.', ',');

    $('#formValorReal').val(vReal).trigger('input');
    $('#formValorFrete').val(vFrete).trigger('input');
    $('#formMotorista').val(c.nome_motorista);
    $('#formGasolina').val(vGasolina).trigger('input');
    
    $('#modalTitle').text('Editar Registro de Chegada');
    $('#chegadaModal').modal('show');
  }

  function deleteChegada(id) {
    if (confirm('Deseja realmente deletar este registro?')) {
      $.post('api/delete_chegada.php', {id: id}, function(res) {
        if (res.success) loadChegada();
        else alert(res.message);
      });
    }
  }

  function viewChegada(c) {
    let valReal = parseFloat(c.valor_real).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    let valFrete = parseFloat(c.valor_frete).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    let valGasolina = parseFloat(c.gasolina_valor).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    let sacaFormatted = parseFloat(c.qtd_saca).toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 2 });

    let details = `
      <div class="row">
        <div class="col-md-3 form-group">
          <label>Data Fruto</label>
          <input type="text" class="form-control" readonly value="${c.data_fruto_br}">
        </div>
        <div class="col-md-3 form-group">
          <label>Lote Fruto</label>
          <input type="text" class="form-control" readonly value="${c.lote_fruto}">
        </div>
        <div class="col-md-3 form-group">
          <label>Fornecedor</label>
          <input type="text" class="form-control" readonly value="${c.fornecedor}">
        </div>
        <div class="col-md-3 form-group">
          <label>Origem</label>
          <input type="text" class="form-control" readonly value="${c.origem}">
        </div>
      </div>
      <div class="row">
        <div class="col-md-3 form-group">
          <label>Tempo Transporte (Horas)</label>
          <input type="text" class="form-control" readonly value="${c.temp_transporte}">
        </div>
        <div class="col-md-3 form-group">
          <label>Data Colheita</label>
          <input type="text" class="form-control" readonly value="${c.data_colheita_br}">
        </div>
        <div class="col-md-3 form-group">
          <label>Tipo Embalagem</label>
          <input type="text" class="form-control" readonly value="${c.tipo_embalagem || '-'}">
        </div>
        <div class="col-md-3 form-group">
          <label>Qtd. Sacas</label>
          <input type="text" class="form-control" readonly value="${sacaFormatted}">
        </div>
      </div>
      <div class="row">
        <div class="col-md-3 form-group">
          <label>Maturação</label>
          <input type="text" class="form-control" readonly value="${c.maturacao || '-'}">
        </div>
        <div class="col-md-3 form-group">
          <label>Aroma do Fruto</label>
          <input type="text" class="form-control" readonly value="${c.odor_fruto || '-'}">
        </div>
        <div class="col-md-3 form-group">
          <label>Cor do Fruto</label>
          <input type="text" class="form-control" readonly value="${c.cor_fruto || '-'}">
        </div>
        <div class="col-md-3 form-group">
          <label>Situação</label>
          <input type="text" class="form-control" readonly value="${c.situacao}">
        </div>
      </div>
      <h5 class="mt-3 border-bottom pb-2">Informações Financeiras</h5>
      <div class="row">
        <div class="col-md-3 form-group">
          <label>Valor em Compras (R$)</label>
          <input type="text" class="form-control" readonly value="${valReal}">
        </div>
        <div class="col-md-3 form-group">
          <label>Valor do Frete (R$)</label>
          <input type="text" class="form-control" readonly value="${valFrete}">
        </div>
        <div class="col-md-3 form-group">
          <label>Nome do Motorista</label>
          <input type="text" class="form-control" readonly value="${c.nome_motorista || '-'}">
        </div>
        <div class="col-md-3 form-group">
          <label>Valor Gasolina (R$)</label>
          <input type="text" class="form-control" readonly value="${valGasolina}">
        </div>
      </div>
    `;
    $('#viewDetails').html(details);
    $('#viewModal').modal('show');
  }
</script>
</body>
</html>
