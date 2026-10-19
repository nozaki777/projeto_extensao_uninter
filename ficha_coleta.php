<?php
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';

requireLogin();

$userRole = getUserRole();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ficha de Coleta</title>

  <!-- App favicon -->
  <link rel="shortcut icon" href="assets/img/favicon.ico">

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  
  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
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
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item"><a href="dashboard" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard Interno</p></a></li>
          <li class="nav-item"><a href="dashboard_chegada" class="nav-link"><i class="nav-icon fas fa-chart-pie"></i><p>Dashboard Chegada</p></a></li>
          <li class="nav-item"><a href="dashboard_destino" class="nav-link"><i class="nav-icon fas fa-chart-bar"></i><p>Dashboard Destino</p></a></li>
          <li class="nav-item"><a href="dashboard_coleta" class="nav-link"><i class="nav-icon fas fa-chart-line"></i><p>Dashboard Coleta</p></a></li>
          <li class="nav-item"><a href="controle_interno" class="nav-link"><i class="nav-icon fas fa-clipboard-list"></i><p>Controle Interno</p></a></li>
          <li class="nav-item"><a href="chegada_fruto" class="nav-link"><i class="nav-icon fas fa-truck"></i><p>Controle de Chegada</p></a></li>
          <li class="nav-item"><a href="controle_destino" class="nav-link"><i class="nav-icon fas fa-share-square"></i><p>Controle de Destino</p></a></li>
          <li class="nav-item"><a href="ficha_coleta" class="nav-link active"><i class="nav-icon fas fa-file-alt"></i><p>Ficha de Coleta</p></a></li>
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
            <h1 class="m-0">Ficha de Coleta de Saca de Frutos</h1>
          </div>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        
        <!-- Filters -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter"></i> Filtros de Busca</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label>Data Inicial</label>
                        <input type="date" id="filter_data_inicio" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label>Data Final</label>
                        <input type="date" id="filter_data_fim" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label>Aldeia</label>
                        <select id="filter_aldeia" class="form-control">
                            <option value="">Todas</option>
                            <option value="Açaizal">Açaizal</option>
                            <option value="Açaizal-Igarapé">Açaizal-Igarapé</option>
                            <option value="Ahumã">Ahumã</option>
                            <option value="Anawerá">Anawerá</option>
                            <option value="Ariramba">Ariramba</option>
                            <option value="Curipi">Curipi</option>
                            <option value="Encruzo">Encruzo</option>
                            <option value="Estrela">Estrela</option>
                            <option value="Flamã">Flamã</option>
                            <option value="Galibi">Galibi</option>
                            <option value="Japiim">Japiim</option>
                            <option value="Kamuywa">Kamuywa</option>
                            <option value="Kariá">Kariá</option>
                            <option value="Karibuen">Karibuen</option>
                            <option value="Kuahí">Kuahí</option>
                            <option value="Kumarumã">Kumarumã</option>
                            <option value="Kumenê">Kumenê</option>
                            <option value="Kunanã">Kunanã</option>
                            <option value="Manga">Manga</option>
                            <option value="Mangue">Mangue</option>
                            <option value="Piquiá">Piquiá</option>
                            <option value="Samaúma">Samaúma</option>
                            <option value="Santa Isabel">Santa Isabel</option>
                            <option value="Tukay">Tukay</option>
                            <option value="Tuluhi">Tuluhi</option>
                            <option value="Uahá">Uahá</option>
                            <option value="Ykawakun">Ykawakun</option>
                            <option value="Ywawka">Ywawka</option>
                            <option value="Zacarias">Zacarias</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button id="btnFilter" class="btn btn-primary mr-2"><i class="fas fa-search"></i> Filtrar</button>
                        <button id="btnClearFilter" class="btn btn-default"><i class="fas fa-times"></i> Limpar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Registros de Coleta</h3>
            <div class="card-tools">
              <button class="btn btn-success btn-sm" onclick="openModal()"><i class="fas fa-plus"></i> Nova Ficha de Coleta</button>
            </div>
          </div>
          <div class="card-body">
            <table id="coletaTable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Data Coleta</th>
                  <th>Aldeia</th>
                  <th>Coletor Responsável</th>
                  <th>Qtd Ajudantes</th>
                  <th>Qtd Famílias</th>
                  <th>Local Coleta</th>
                  <th>Total Sacas</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                <!-- Populated via AJAX -->
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>

  <footer class="main-footer">
    <strong>Copyright &copy; 2026 UASEI.</strong> Todos os direitos reservados.
  </footer>
</div>

<!-- Modal Coleta -->
<div class="modal fade" id="coletaModal" tabindex="-1" role="dialog" aria-labelledby="coletaModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="coletaModalLabel">Ficha de Coleta</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="coletaForm">
        <input type="hidden" id="coleta_id" name="id">
        <div class="modal-body">
            
            <h5 class="text-primary border-bottom pb-2 mb-3">Informações da Coleta (Para todos)</h5>
            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Data da Coleta *</label>
                    <input type="date" class="form-control" name="data_coleta" id="data_coleta" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Aldeia da Ficha *</label>
                    <select class="form-control" name="aldeia_ficha" id="aldeia_ficha" required>
                        <option value="">Selecione...</option>
                        <option value="Açaizal">Açaizal</option>
                        <option value="Açaizal-Igarapé">Açaizal-Igarapé</option>
                        <option value="Ahumã">Ahumã</option>
                        <option value="Anawerá">Anawerá</option>
                        <option value="Ariramba">Ariramba</option>
                        <option value="Curipi">Curipi</option>
                        <option value="Encruzo">Encruzo</option>
                        <option value="Estrela">Estrela</option>
                        <option value="Flamã">Flamã</option>
                        <option value="Galibi">Galibi</option>
                        <option value="Japiim">Japiim</option>
                        <option value="Kamuywa">Kamuywa</option>
                        <option value="Kariá">Kariá</option>
                        <option value="Karibuen">Karibuen</option>
                        <option value="Kuahí">Kuahí</option>
                        <option value="Kumarumã">Kumarumã</option>
                        <option value="Kumenê">Kumenê</option>
                        <option value="Kunanã">Kunanã</option>
                        <option value="Manga">Manga</option>
                        <option value="Mangue">Mangue</option>
                        <option value="Piquiá">Piquiá</option>
                        <option value="Samaúma">Samaúma</option>
                        <option value="Santa Isabel">Santa Isabel</option>
                        <option value="Tukay">Tukay</option>
                        <option value="Tuluhi">Tuluhi</option>
                        <option value="Uahá">Uahá</option>
                        <option value="Ykawakun">Ykawakun</option>
                        <option value="Ywawka">Ywawka</option>
                        <option value="Zacarias">Zacarias</option>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label>Coletor Responsável *</label>
                    <input type="text" class="form-control" name="coletor_resposavel" id="coletor_resposavel" required>
                </div>
                <div class="col-md-4 form-group">
                    <label>Qtd Ajudantes *</label>
                    <input type="number" class="form-control" name="qtd_ajudantes" id="qtd_ajudantes" required>
                </div>
                <div class="col-md-4 form-group">
                    <label>Qtd Famílias *</label>
                    <input type="number" class="form-control" name="qtd_familias" id="qtd_familias" required>
                </div>
                <div class="col-md-4 form-group">
                    <label>Local de Coleta *</label>
                    <select class="form-control" name="local_coleta" id="local_coleta" required>
                        <option value="">Selecione...</option>
                        <option value="Área de manejo">Área de manejo</option>
                        <option value="Área de mata">Área de mata</option>
                    </select>
                </div>
            </div>

            <h5 class="text-primary border-bottom pb-2 mt-3 mb-3">Venda da saca para fora da TI</h5>
            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Qtd Sacas</label>
                    <input type="number" class="form-control" name="fora_qtd_saca" id="fora_qtd_saca" value="0" min="0">
                </div>
                <div class="col-md-3 form-group">
                    <label>Preço por Saca (R$)</label>
                    <input type="text" class="form-control money2" name="fora_preco_saca" id="fora_preco_saca" value="0,00">
                </div>
                <div class="col-md-3 form-group">
                    <label>Quem Comprou?</label>
                    <input type="text" class="form-control" name="fora_quem_comprou" id="fora_quem_comprou">
                </div>
                <div class="col-md-3 form-group">
                    <label>Local do Comprador</label>
                    <input type="text" class="form-control" name="local_comprador" id="local_comprador">
                </div>
            </div>

            <h5 class="text-primary border-bottom pb-2 mt-3 mb-3">Para consumo</h5>
            <div class="row">
              <div class="col-md-6 border-right">
                  <div class="row">
                      <div class="col-md-6 form-group">
                          <label>Qtd Sacas (Fruto)</label>
                          <input type="number" class="form-control" name="consumo_qtd_saca" id="consumo_qtd_saca" value="0" min="0">
                      </div>
                      <div class="col-md-6 form-group">
                          <label>Qtd Litros</label>
                          <input type="number" class="form-control" name="consumo_qtd_litros" id="consumo_qtd_litros" value="0" min="0">
                      </div>
                  </div>
              </div>
            </div>
            <h5 class="text-primary border-bottom pb-2 mt-3 mb-3">Vender na aldeia</h5>
            <div class="row">
              <div class="col-md-6">
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Qtd Sacas</label>
                        <input type="number" class="form-control" name="venda_qtd_saca" id="venda_qtd_saca" value="0" min="0">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Qtd Litros</label>
                        <input type="number" class="form-control" name="venda_qtd_litros" id="venda_qtd_litros" value="0" min="0">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Preço por Litro (R$)</label>
                        <input type="text" class="form-control money2" name="venda_preco_litro" id="venda_preco_litro" value="0,00">
                    </div>
                </div>
              </div>
            </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Registro</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- REQUIRED SCRIPTS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<!-- DataTables & Plugins -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskplugin/1.14.16/jquery.mask.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const userRole = '<?= $userRole ?>';
    let dataTable;

    function formatMoney(amount) {
        return parseFloat(amount).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function loadColeta() {
        let dataInicio = $('#filter_data_inicio').val();
        let dataFim = $('#filter_data_fim').val();
        let aldeiaFicha = $('#filter_aldeia').val();

        $.ajax({
            url: 'api/list_coleta.php',
            type: 'GET',
            data: { data_inicio: dataInicio, data_fim: dataFim, aldeia_ficha: aldeiaFicha },
            dataType: 'json',
            success: function(res) {
                if (res.data) {
                    let tbody = '';
                    res.data.forEach(c => {
                        let btns = `<button class="btn btn-sm btn-secondary mr-1" onclick='viewRecord(${JSON.stringify(c)})' title="Visualizar"><i class="fas fa-eye"></i></button>`;
                        if (userRole === 'admin' || userRole === 'supervisor') {
                            btns += `<button class="btn btn-sm btn-info mr-1" onclick='editRecord(${JSON.stringify(c)})' title="Editar"><i class="fas fa-edit"></i></button>`;
                        }
                        if (userRole === 'admin') {
                            btns += `<button class="btn btn-sm btn-danger" onclick="deleteRecord(${c.id})" title="Excluir"><i class="fas fa-trash"></i></button>`;
                        }
                        if (!btns) btns = '-';

                        let dateParts = c.data_coleta ? c.data_coleta.split('-') : [];
                        let brDate = dateParts.length === 3 ? dateParts[2] + '/' + dateParts[1] + '/' + dateParts[0] : '';

                        tbody += `<tr>
                            <td>${c.id}</td>
                            <td>${brDate}</td>
                            <td>${c.aldeia_ficha || ''}</td>
                            <td>${c.coletor_resposavel || ''}</td>
                            <td>${c.qtd_ajudantes || 0}</td>
                            <td>${c.qtd_familias || 0}</td>
                            <td>${c.local_coleta || ''}</td>
                            <td>${c.qtd_sacas_total || 0}</td>
                            <td>${btns}</td>
                        </tr>`;
                    });

                    if ($.fn.DataTable.isDataTable('#coletaTable')) {
                        $('#coletaTable').DataTable().destroy();
                    }
                    $('#coletaTable tbody').html(tbody);
                    dataTable = $('#coletaTable').DataTable({
                        "responsive": true,
                        "lengthChange": false,
                        "autoWidth": false,
                        "order": [[0, "desc"]],
                        "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                               "<'row'<'col-sm-12 mb-3'B>>" +
                               "<'row'<'col-sm-12'tr>>" +
                               "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                        "buttons": [
                            { extend: 'excelHtml5', className: 'btn btn-success', text: '<i class="fas fa-file-excel"></i> Excel', exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6, 7 ] } },
                            { extend: 'pdfHtml5', className: 'btn btn-danger', text: '<i class="fas fa-file-pdf"></i> PDF', exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6, 7 ] } },
                            { extend: 'print', className: 'btn btn-secondary', text: '<i class="fas fa-print"></i> Imprimir', exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6, 7 ] } }
                        ],
                        "language": {
                            "url": "https://cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json"
                        }
                    });
                } else if (res.error) {
                    console.error("Erro na API:", res.error);
                }
            },
            error: function(xhr, status, error) {
                console.error("Erro ao carregar tabela:", error);
            }
        });
    }

    $(document).ready(function() {
        if ($.fn.mask) {
            $('.money2').mask("#.##0,00", {reverse: true});
        } else {
            console.warn("Plugin Mask não carregado");
        }

        loadColeta();

        $('#btnFilter').click(function() {
            loadColeta();
        });

        $('#btnClearFilter').click(function() {
            $('#filter_data_inicio').val('');
            $('#filter_data_fim').val('');
            $('#filter_aldeia').val('');
            loadColeta();
        });

        $('#coletaForm').submit(function(e) {
            e.preventDefault();
            
            // Validação manual para evitar falhas silenciosas do HTML5 em modais
            let dataColeta = $('#data_coleta').val();
            let aldeiaFicha = $('#aldeia_ficha').val();
            let coletor = $('#coletor_resposavel').val();
            let local = $('#local_coleta').val();

            if (!dataColeta || !aldeiaFicha || !coletor || !local) {
                Swal.fire('Atenção', 'Por favor, preencha todos os campos obrigatórios (com asterisco *).', 'warning');
                return;
            }
            
            let id = $('#coleta_id').val();
            let url = id ? 'api/edit_coleta.php' : 'api/create_coleta.php';
            
            $.ajax({
                type: 'POST',
                url: url,
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Sucesso!', response.message, 'success');
                        $('#coletaModal').modal('hide');
                        loadColeta();
                    } else {
                        Swal.fire('Erro!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Erro!', 'Ocorreu um erro no servidor.', 'error');
                }
            });
        });
    });

    function openModal() {
        $('#coletaForm')[0].reset();
        $('#coleta_id').val('');
        
        $('#coletaForm input, #coletaForm select').prop('disabled', false);
        $('#coletaForm button[type="submit"]').show();
        
        $('#coletaModalLabel').text('Nova Ficha de Coleta');
        $('#coletaModal').modal('show');
    }

    function viewRecord(data) {
        $('#coletaForm')[0].reset();
        $('#coleta_id').val(data.id);
        
        $('#data_coleta').val(data.data_coleta);
        $('#aldeia_ficha').val(data.aldeia_ficha);
        $('#coletor_resposavel').val(data.coletor_resposavel);
        $('#qtd_ajudantes').val(data.qtd_ajudantes);
        $('#qtd_familias').val(data.qtd_familias);
        $('#local_coleta').val(data.local_coleta);
        
        $('#fora_qtd_saca').val(data.fora_qtd_saca);
        let fps = parseFloat(data.fora_preco_saca).toFixed(2).replace('.', ',');
        $('#fora_preco_saca').val(fps).trigger('input');
        $('#fora_quem_comprou').val(data.fora_quem_comprou);
        $('#local_comprador').val(data.local_comprador);
        
        $('#consumo_qtd_saca').val(data.consumo_qtd_saca);
        $('#consumo_qtd_litros').val(data.consumo_qtd_litros);
        
        $('#venda_qtd_saca').val(data.venda_qtd_saca);
        $('#venda_qtd_litros').val(data.venda_qtd_litros);
        let vpl = parseFloat(data.venda_preco_litro).toFixed(2).replace('.', ',');
        $('#venda_preco_litro').val(vpl).trigger('input');

        $('#coletaForm input, #coletaForm select').prop('disabled', true);
        $('#coletaForm button[type="submit"]').hide();

        $('#coletaModalLabel').text('Visualizar Ficha de Coleta');
        $('#coletaModal').modal('show');
    }

    function editRecord(data) {
        if (userRole === 'colaborador') return;
        
        $('#coletaForm')[0].reset();
        $('#coleta_id').val(data.id);
        
        $('#data_coleta').val(data.data_coleta);
        $('#aldeia_ficha').val(data.aldeia_ficha);
        $('#coletor_resposavel').val(data.coletor_resposavel);
        $('#qtd_ajudantes').val(data.qtd_ajudantes);
        $('#qtd_familias').val(data.qtd_familias);
        $('#local_coleta').val(data.local_coleta);
        
        $('#fora_qtd_saca').val(data.fora_qtd_saca);
        let fps = parseFloat(data.fora_preco_saca).toFixed(2).replace('.', ',');
        $('#fora_preco_saca').val(fps).trigger('input');
        $('#fora_quem_comprou').val(data.fora_quem_comprou);
        $('#local_comprador').val(data.local_comprador);
        
        $('#consumo_qtd_saca').val(data.consumo_qtd_saca);
        $('#consumo_qtd_litros').val(data.consumo_qtd_litros);
        
        $('#venda_qtd_saca').val(data.venda_qtd_saca);
        $('#venda_qtd_litros').val(data.venda_qtd_litros);
        let vpl = parseFloat(data.venda_preco_litro).toFixed(2).replace('.', ',');
        $('#venda_preco_litro').val(vpl).trigger('input');

        $('#coletaForm input, #coletaForm select').prop('disabled', false);
        $('#coletaForm button[type="submit"]').show();

        $('#coletaModalLabel').text('Editar Ficha de Coleta');
        $('#coletaModal').modal('show');
    }

    function deleteRecord(id) {
        if (userRole !== 'admin') return;

        Swal.fire({
            title: 'Tem certeza?',
            text: "Esta ação não pode ser desfeita!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: 'api/delete_coleta.php',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Excluído!', response.message, 'success');
                            loadColeta();
                        } else {
                            Swal.fire('Erro!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Erro!', 'Ocorreu um erro no servidor.', 'error');
                    }
                });
            }
        });
    }
</script>
</body>
</html>
