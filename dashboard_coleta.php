<?php
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/utils.php';

requireLogin();

$userRole = getUserRole();
$anoAtual = date('Y');
$anoAnterior = (string)((int)$anoAtual - 1);

// Lista canônica das 29 aldeias
$aldeiasList = [
    'Açaizal', 'Açaizal-Igarapé', 'Ahumã', 'Anawerá', 'Ariramba',
    'Curipi', 'Encruzo', 'Estrela', 'Flamã', 'Galibi',
    'Japiim', 'Kamuywa', 'Kariá', 'Karibuen', 'Kuahí',
    'Kumarumã', 'Kumenê', 'Kunanã', 'Manga', 'Mangue',
    'Piquiá', 'Samaúma', 'Santa Isabel', 'Tukay', 'Tuluhi',
    'Uahá', 'Ykawakun', 'Ywawka', 'Zacarias'
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - Ficha de Coleta | Sistema de Safra</title>

  <!-- App favicon -->
  <link rel="shortcut icon" href="assets/img/favicon.ico">

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,600,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <!-- AdminLTE -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

  <style>
    .kpi-card {
      border-radius: 8px;
      transition: all 0.25s ease-in-out;
      overflow: hidden;
      border: none;
    }
    .kpi-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
    }
    .kpi-card .inner h3 {
      font-size: 2.05rem;
      font-weight: 700;
      letter-spacing: -0.5px;
    }
    #coletaMap {
      height: 680px;
      width: 100%;
      border-radius: 8px;
      z-index: 1;
      background-color: #dce7eb;
    }
    .leaflet-popup-content-wrapper {
      border-radius: 8px;
      padding: 4px;
      box-shadow: 0 4px 18px rgba(0,0,0,0.25);
    }
    .leaflet-popup-content {
      margin: 10px 14px;
      line-height: 1.5;
    }
    .aldeia-pin {
      display: flex;
      align-items: center;
      justify-content: center;
      color: #ffffff;
      font-weight: 800;
      border-radius: 50%;
      border: 2px solid #ffffff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.45);
      font-size: 11px;
      cursor: pointer;
      transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .aldeia-pin:hover {
      transform: scale(1.25);
      box-shadow: 0 0 12px rgba(0,0,0,0.6);
      z-index: 900 !important;
    }
    .aldeia-pin.active-pin {
      transform: scale(1.4);
      border: 3px solid #ffeb3b !important;
      box-shadow: 0 0 15px rgba(255, 235, 59, 0.9), 0 4px 10px rgba(0,0,0,0.6) !important;
      z-index: 1000 !important;
    }
    .ti-color-bullet {
      width: 11px;
      height: 11px;
      border-radius: 50%;
      margin-right: 6px;
      border: 1px solid rgba(0,0,0,0.2);
      flex-shrink: 0;
    }
    .custom-control-label {
      cursor: pointer;
      user-select: none;
    }
    .btn-action-custom {
      font-weight: 600;
      border-radius: 6px;
      font-size: 13px;
    }
    .side-detail-scroll {
      max-height: 630px;
      overflow-y: auto;
      padding-right: 4px;
    }
    .side-detail-scroll::-webkit-scrollbar {
      width: 5px;
    }
    .side-detail-scroll::-webkit-scrollbar-thumb {
      background-color: #ced4da;
      border-radius: 4px;
    }
    .progress-dest {
      height: 18px;
      border-radius: 4px;
      background-color: #e9ecef;
    }
    .stat-mini-badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 4px;
      font-size: 11px;
      font-weight: 600;
      margin: 2px 2px 2px 0;
    }
    .chart-type-select {
      font-size: 12px;
      height: 28px;
      padding: 2px 8px;
      display: inline-block;
      width: auto;
      border-radius: 4px;
    }
    @media print {
      .main-sidebar, .main-header, .card-header .card-tools, .btn-action-custom, #filterFormContainer, .main-footer, .chart-controls {
        display: none !important;
      }
      .content-wrapper {
        margin-left: 0 !important;
      }
      #coletaMap {
        height: 500px !important;
      }
    }
  </style>
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
          <li class="nav-item"><a href="dashboard_coleta" class="nav-link active"><i class="nav-icon fas fa-chart-line"></i><p>Dashboard Coleta</p></a></li>
          <li class="nav-item"><a href="controle_interno" class="nav-link"><i class="nav-icon fas fa-clipboard-list"></i><p>Controle Interno</p></a></li>
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
    <!-- Content Header -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-7">
            <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.55rem;">
              <i class="fas fa-map-marked-alt text-success mr-2"></i>Dashboard Ficha de Coleta
            </h1>
            <small class="text-muted font-weight-bold">
              Painel Integrado de Monitoramento Espacial e Econômico &bull; 29 Aldeias Indígenas de Oiapoque / AP
            </small>
          </div>
          <div class="col-sm-5 text-right d-flex align-items-center justify-content-end flex-wrap">
            <button type="button" class="btn btn-outline-success btn-sm shadow-sm btn-action-custom mr-2 mb-1" onclick="exportColetaExcel()" title="Exportar dados filtrados para Excel formatado">
              <i class="fas fa-file-excel mr-1"></i> Excel
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm shadow-sm btn-action-custom mb-1" onclick="window.print()" title="Imprimir Relatório Completo">
              <i class="fas fa-print mr-1"></i> Imprimir / PDF
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="content">
      <div class="container-fluid">

        <!-- KPI Cards -->
        <div class="row">
          <!-- Total Sacas -->
          <div class="col-xl-3 col-lg-6 col-sm-6 col-12 mb-3">
            <div class="small-box bg-gradient-success kpi-card shadow-sm h-100">
              <div class="inner">
                <h3 id="kpiTotalSacas">...</h3>
                <p class="font-weight-bold mb-0">Total Sacas Coletadas</p>
                <small id="kpiSacasSub">Carregando destinações...</small>
              </div>
              <div class="icon">
                <i class="fas fa-seedling"></i>
              </div>
              <a href="ficha_coleta" class="small-box-footer">Ver Fichas de Coleta <i class="fas fa-arrow-circle-right ml-1"></i></a>
            </div>
          </div>

          <!-- Venda Externa -->
          <div class="col-xl-3 col-lg-6 col-sm-6 col-12 mb-3">
            <div class="small-box bg-gradient-info kpi-card shadow-sm h-100">
              <div class="inner">
                <h3 id="kpiVendaForaValor">...</h3>
                <p class="font-weight-bold mb-0">Venda Externa (Fora da TI)</p>
                <small id="kpiVendaForaSacas">Carregando sacas...</small>
              </div>
              <div class="icon">
                <i class="fas fa-truck-loading"></i>
              </div>
              <a href="ficha_coleta" class="small-box-footer">Ver Detalhes <i class="fas fa-arrow-circle-right ml-1"></i></a>
            </div>
          </div>

          <!-- Venda na Aldeia -->
          <div class="col-xl-3 col-lg-6 col-sm-6 col-12 mb-3">
            <div class="small-box bg-gradient-danger kpi-card shadow-sm h-100">
              <div class="inner">
                <h3 id="kpiVendaAldeiaValor">...</h3>
                <p class="font-weight-bold mb-0">Venda na Aldeia</p>
                <small id="kpiVendaAldeiaSub">Carregando litros e sacas...</small>
              </div>
              <div class="icon">
                <i class="fas fa-store"></i>
              </div>
              <a href="ficha_coleta" class="small-box-footer">Ver Detalhes <i class="fas fa-arrow-circle-right ml-1"></i></a>
            </div>
          </div>

          <!-- Aldeias / Famílias -->
          <div class="col-xl-3 col-lg-6 col-sm-6 col-12 mb-3">
            <div class="small-box bg-gradient-warning kpi-card shadow-sm h-100">
              <div class="inner text-white">
                <h3 id="kpiAldeiasCount" class="text-white">29</h3>
                <p class="font-weight-bold mb-0 text-white">Aldeias Mapeadas</p>
                <small id="kpiFamiliasSub" class="text-white">Carregando famílias...</small>
              </div>
              <div class="icon">
                <i class="fas fa-users"></i>
              </div>
              <a href="javascript:void(0)" onclick="resetColetaFilters()" class="small-box-footer" style="color: #fff !important;">Ver Todas no Mapa <i class="fas fa-globe ml-1"></i></a>
            </div>
          </div>
        </div>

        <!-- Filtros Dinâmicos -->
        <div class="card card-outline card-success shadow-sm mb-3" id="filterFormContainer">
          <div class="card-header py-2">
            <h3 class="card-title text-bold font-weight-bold" style="font-size: 15px;">
              <i class="fas fa-filter text-success mr-1"></i> Filtros de Coleta e Território
            </h3>
            <div class="card-tools">
              <div class="custom-control custom-switch d-inline-block mr-3">
                <input type="checkbox" class="custom-control-input" id="togglePinsSwitch" checked onchange="toggleMapPins()">
                <label class="custom-control-label font-weight-bold text-dark small" for="togglePinsSwitch">Marcadores Numéricos (#1 - #29)</label>
              </div>
              <div class="custom-control custom-switch d-inline-block mr-3">
                <input type="checkbox" class="custom-control-input" id="toggleCirclesSwitch" checked onchange="toggleMapCircles()">
                <label class="custom-control-label font-weight-bold text-dark small" for="toggleCirclesSwitch">Círculos de Volume</label>
              </div>
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="card-body py-2">
            <div class="row">
              <!-- Ano -->
              <div class="col-lg-3 col-md-6 col-sm-6 mb-2">
                <label class="small text-muted font-weight-bold mb-1">Ano da Coleta</label>
                <select id="filterAno" class="form-control form-control-sm" onchange="applyColetaFilters()">
                  <option value="Todos" selected>Todos os Anos</option>
                  <option value="<?= htmlspecialchars($anoAtual) ?>">Ano Corrente (<?= htmlspecialchars($anoAtual) ?>)</option>
                  <option value="<?= htmlspecialchars($anoAnterior) ?>">Ano Anterior (<?= htmlspecialchars($anoAnterior) ?>)</option>
                </select>
              </div>

              <!-- Aldeia -->
              <div class="col-lg-3 col-md-6 col-sm-6 mb-2">
                <label class="small text-muted font-weight-bold mb-1">Aldeia</label>
                <select id="filterAldeia" class="form-control form-control-sm" onchange="onAldeiaSelectChange()">
                  <option value="">Todas as 29 Aldeias</option>
                  <?php foreach ($aldeiasList as $ald): ?>
                  <option value="<?= htmlspecialchars($ald) ?>"><?= htmlspecialchars($ald) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Local de Coleta -->
              <div class="col-lg-3 col-md-6 col-sm-6 mb-2">
                <label class="small text-muted font-weight-bold mb-1">Local de Coleta</label>
                <select id="filterLocal" class="form-control form-control-sm" onchange="applyColetaFilters()">
                  <option value="">Todos os Locais</option>
                  <option value="Área de manejo">Área de manejo</option>
                  <option value="Área de mata">Área de mata</option>
                </select>
              </div>

              <!-- Busca Livre e Limpar -->
              <div class="col-lg-3 col-md-6 col-sm-6 mb-2">
                <label class="small text-muted font-weight-bold mb-1">Busca Livre</label>
                <div class="input-group input-group-sm">
                  <input type="text" id="filterSearch" class="form-control" placeholder="Buscar coletor, comprador..." onkeyup="applyColetaFilters()">
                  <div class="input-group-append">
                    <button type="button" class="btn btn-default" onclick="resetColetaFilters()" title="Limpar Filtros e Ver Todas">
                      <i class="fas fa-undo mr-1"></i> Limpar
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Área Central do Mapa e Painel de Detalhes -->
        <div class="row">
          <!-- Coluna do Mapa -->
          <div class="col-xl-8 col-lg-7 col-md-12 mb-3">
            <div class="card card-outline card-success shadow-sm mb-3">
              <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="card-title text-bold font-weight-bold" style="font-size: 15px;">
                  <i class="fas fa-globe-americas text-success mr-1"></i> Mapa de Distribuição Espacial da Coleta de Açaí (Oiapoque - AP)
                </h3>
                <div>
                  <small class="text-muted"><i class="fas fa-mouse-pointer mr-1"></i> Clique em qualquer aldeia no mapa para focar e detalhar seus dados.</small>
                </div>
              </div>
              <div class="card-body p-2 position-relative">
                <div id="coletaMap"></div>
              </div>
              <!-- Rodapé do Mapa -->
              <div class="card-footer bg-light p-2 d-flex justify-content-between align-items-center flex-wrap">
                <div class="small text-muted">
                  <i class="fas fa-info-circle text-success mr-1"></i>
                  <b>29 Aldeias Indígenas</b> georreferenciadas no Município de Oiapoque &bull; SIRGAS 2000 / IBGE
                </div>
                <div>
                  <button type="button" class="btn btn-xs btn-outline-secondary" onclick="resetMapView()" title="Centralizar visão geral de Oiapoque">
                    <i class="fas fa-expand-arrows-alt mr-1"></i> Visão Geral
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Coluna do Painel Lateral de Detalhes da Aldeia -->
          <div class="col-xl-4 col-lg-5 col-md-12 mb-3">
            <div class="card card-outline card-info shadow-sm" style="min-height: 760px;">
              <div class="card-header py-2 bg-light d-flex justify-content-between align-items-center">
                <h3 class="card-title text-bold" style="font-size: 15px;" id="panelAldeiaTitle">
                  <i class="fas fa-layer-group text-primary mr-1"></i> Resumo Consolidado Geral
                </h3>
                <span id="badgeSelectedStatus" class="badge badge-primary" style="display: none;">Todas as Aldeias</span>
              </div>
              <div class="card-body p-3 side-detail-scroll" id="panelAldeiaContent">
                <div class="text-center text-muted py-5">
                  <i class="fas fa-spinner fa-spin fa-2x mb-3 text-secondary"></i>
                  <p class="small text-muted">Carregando dados consolidados da coleta...</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Seção de Gráficos Analíticos com Controles Dinâmicos de Tipo -->
        <div class="row">
          <!-- Gráfico 1: Volume de Coleta e Destinação por Aldeia -->
          <div class="col-lg-6 col-md-12 mb-3">
            <div class="card card-outline card-primary shadow-sm h-100">
              <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="card-title text-bold font-weight-bold" style="font-size: 14px;">
                  <i class="fas fa-chart-bar text-primary mr-1"></i> Destinação das Sacas por Aldeia
                </h3>
                <div class="chart-controls d-flex align-items-center">
                  <label class="small text-muted mr-1 mb-0"><i class="fas fa-exchange-alt mr-1"></i>Tipo:</label>
                  <select class="form-control form-control-sm chart-type-select" id="selectTypeChart1" onchange="changeChartType('chart1', this.value)">
                    <option value="column" selected>Colunas</option>
                    <option value="bar">Barras</option>
                    <option value="line">Linhas</option>
                    <option value="area">Área</option>
                  </select>
                </div>
              </div>
              <div class="card-body p-2">
                <div id="chartDestinoAldeias" style="height: 350px;"></div>
              </div>
            </div>
          </div>

          <!-- Gráfico 2: Mão de Obra e Famílias Envolvidas -->
          <div class="col-lg-6 col-md-12 mb-3">
            <div class="card card-outline card-success shadow-sm h-100">
              <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="card-title text-bold font-weight-bold" style="font-size: 14px;">
                  <i class="fas fa-users text-success mr-1"></i> Mão de Obra e Famílias por Aldeia
                </h3>
                <div class="chart-controls d-flex align-items-center">
                  <label class="small text-muted mr-1 mb-0"><i class="fas fa-exchange-alt mr-1"></i>Tipo:</label>
                  <select class="form-control form-control-sm chart-type-select" id="selectTypeChart2" onchange="changeChartType('chart2', this.value)">
                    <option value="column" selected>Colunas</option>
                    <option value="bar">Barras</option>
                    <option value="line">Linhas</option>
                    <option value="area">Área</option>
                  </select>
                </div>
              </div>
              <div class="card-body p-2">
                <div id="chartPessoasAldeias" style="height: 350px;"></div>
              </div>
            </div>
          </div>

          <!-- Gráfico 3: Produção de Açaí Volume (Litros) vs Venda (R$) -->
          <div class="col-lg-6 col-md-12 mb-3">
            <div class="card card-outline card-info shadow-sm h-100">
              <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="card-title text-bold font-weight-bold" style="font-size: 14px;">
                  <i class="fas fa-money-bill-wave text-info mr-1"></i> Volume de Litros vs Venda na Aldeia (R$)
                </h3>
                <div class="chart-controls d-flex align-items-center">
                  <label class="small text-muted mr-1 mb-0"><i class="fas fa-exchange-alt mr-1"></i>Tipo:</label>
                  <select class="form-control form-control-sm chart-type-select" id="selectTypeChart3" onchange="changeChartType('chart3', this.value)">
                    <option value="column" selected>Colunas</option>
                    <option value="bar">Barras</option>
                    <option value="line">Linhas</option>
                    <option value="area">Área</option>
                  </select>
                </div>
              </div>
              <div class="card-body p-2">
                <div id="chartDualYVenda" style="height: 350px;"></div>
              </div>
            </div>
          </div>

          <!-- Gráfico 4: Estrutura da Destinação Total das Sacas -->
          <div class="col-lg-6 col-md-12 mb-3">
            <div class="card card-outline card-warning shadow-sm h-100">
              <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="card-title text-bold font-weight-bold" style="font-size: 14px;">
                  <i class="fas fa-chart-pie text-warning mr-1"></i> Proporção Total da Destinação das Sacas
                </h3>
                <div class="chart-controls d-flex align-items-center">
                  <label class="small text-muted mr-1 mb-0"><i class="fas fa-exchange-alt mr-1"></i>Tipo:</label>
                  <select class="form-control form-control-sm chart-type-select" id="selectTypeChart4" onchange="changeChartType('chart4', this.value)">
                    <option value="pie" selected>Pizza / Donut</option>
                    <option value="column">Colunas</option>
                    <option value="bar">Barras</option>
                  </select>
                </div>
              </div>
              <div class="card-body p-2">
                <div id="chartDestinoTotal" style="height: 350px;"></div>
              </div>
            </div>
          </div>
        </div>

      </div><!-- /.container-fluid -->
    </div><!-- /.content -->
  </div><!-- /.content-wrapper -->

  <footer class="main-footer">
    <strong>Copyright &copy; 2026 UASEI.</strong> Sistema de Safra - Todos os direitos reservados.
  </footer>
</div><!-- ./wrapper -->

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Highcharts -->
<script src="assets/highcharts/code/highcharts.js"></script>
<script src="assets/highcharts/code/modules/exporting.js"></script>
<script src="assets/highcharts/code/modules/export-data.js"></script>
<script src="assets/highcharts/code/modules/accessibility.js"></script>

<script>
  let map = null;
  let pinsLayerGroup = null;
  let circlesLayerGroup = null;
  let allAldeiasList = [];
  let currentFilteredList = [];
  let rawApiData = null;
  let selectedAldeiaNome = null;

  // Variáveis para instâncias e tipos de gráficos
  let chart1 = null;
  let chart2 = null;
  let chart3 = null;
  let chart4 = null;
  let chartTypes = {
    chart1: 'column',
    chart2: 'column',
    chart3: 'column',
    chart4: 'pie'
  };

  $(document).ready(function() {
    initLeafletMap();
    loadColetaData();
  });

  function initLeafletMap() {
    // Coordenadas centrais das Terras Indígenas de Oiapoque / AP
    map = L.map('coletaMap', {
      center: [3.45, -51.52],
      zoom: 9,
      minZoom: 7,
      maxZoom: 16
    });

    // Basemaps de alta qualidade
    let esriTopo = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {
      attribution: 'Tiles &copy; Esri &mdash; Topo Map',
      maxZoom: 18
    }).addTo(map);

    let esriSat = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
      attribution: 'Tiles &copy; Esri &mdash; Satellite Imagery',
      maxZoom: 18
    });

    let osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors',
      maxZoom: 19
    });

    let cartoPositron = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
      attribution: '&copy; CARTO &copy; OpenStreetMap',
      maxZoom: 19
    });

    let baseLayers = {
      "Mapa Topográfico (Esri)": esriTopo,
      "Satélite de Alta Resolução (Esri)": esriSat,
      "OpenStreetMap": osmLayer,
      "CartoDB Positron": cartoPositron
    };

    L.control.layers(baseLayers, null, { position: 'topright' }).addTo(map);

    circlesLayerGroup = L.layerGroup().addTo(map);
    pinsLayerGroup = L.layerGroup().addTo(map);
  }

  function loadColetaData(callback) {
    let ano = $('#filterAno').val();
    let aldeia = $('#filterAldeia').val();
    let local = $('#filterLocal').val();
    let busca = $('#filterSearch').val();

    let queryParams = {
      ano: ano,
      aldeia: aldeia,
      local_coleta: local,
      busca: busca
    };

    $.get('api/dashboard_coleta_geo_data.php', queryParams, function(res) {
      if (res.success) {
        rawApiData = res.data;
        currentFilteredList = res.data.aldeias;

        if (!aldeia && (!allAldeiasList || allAldeiasList.length === 0)) {
          allAldeiasList = res.data.aldeias;
        }

        // Atualizar Anos no Select caso ainda não populado
        if (res.data.available_years && $('#filterAno option').length <= 3) {
          let currentSelectedAno = $('#filterAno').val();
          let opts = '<option value="Todos">Todos os Anos</option>';
          res.data.available_years.forEach(y => {
            opts += `<option value="${y}" ${y === currentSelectedAno ? 'selected' : ''}>Ano ${y}</option>`;
          });
          $('#filterAno').html(opts);
        }

        // Atualizar KPI Cards
        updateKpiCards(res.data.stats, res.data.filter_ano);

        // Renderizar Marcadores e Círculos no Mapa
        renderMapMarkersAndCircles(allAldeiasList.length > 0 ? allAldeiasList : currentFilteredList);

        // Renderizar Gráficos Highcharts
        renderCharts(res.data.rankings, res.data.filter_ano);

        // Atualizar o Painel Lateral
        if (aldeia) {
          selectedAldeiaNome = aldeia;
          let found = currentFilteredList.find(a => a.nome === aldeia) || allAldeiasList.find(a => a.nome === aldeia);
          if (found) {
            showAldeiaSidePanel(found, true);
          } else {
            showGeneralSidePanel(res.data.stats, res.data.filter_ano);
          }
        } else if (selectedAldeiaNome) {
          let found = currentFilteredList.find(a => a.nome === selectedAldeiaNome) || allAldeiasList.find(a => a.nome === selectedAldeiaNome);
          if (found) {
            showAldeiaSidePanel(found, false);
          } else {
            showGeneralSidePanel(res.data.stats, res.data.filter_ano);
          }
        } else {
          showGeneralSidePanel(res.data.stats, res.data.filter_ano);
        }

        if (typeof callback === 'function') callback();
      } else {
        alert('Erro ao carregar dados da coleta: ' + (res.message || 'Falha de comunicação.'));
      }
    }).fail(function() {
      alert('Erro de conexão ao carregar a dashboard de coleta.');
    });
  }

  function updateKpiCards(stats, ano) {
    let anoLabel = (ano && ano !== 'Todos') ? `Ano ${ano}` : 'Todos os Anos';

    // Total de Sacas
    $('#kpiTotalSacas').text(Number(stats.total_sacas || 0).toLocaleString('pt-BR'));
    $('#kpiSacasSub').text(`Fora: ${Number(stats.fora_sacas || 0).toLocaleString('pt-BR')} | Consumo: ${Number(stats.consumo_sacas || 0).toLocaleString('pt-BR')} | Venda: ${Number(stats.venda_sacas || 0).toLocaleString('pt-BR')} (${anoLabel})`);

    // Venda Externa
    $('#kpiVendaForaValor').html(`<sup style="font-size: 19px">R$</sup> ${Number(stats.fora_valor || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`);
    $('#kpiVendaForaSacas').text(`${Number(stats.fora_sacas || 0).toLocaleString('pt-BR')} sacas vendidas para fora da TI`);

    // Venda na Aldeia
    $('#kpiVendaAldeiaValor').html(`<sup style="font-size: 19px">R$</sup> ${Number(stats.venda_valor || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`);
    $('#kpiVendaAldeiaSub').text(`${Number(stats.venda_litros || 0).toLocaleString('pt-BR')} L processados (${Number(stats.venda_sacas || 0).toLocaleString('pt-BR')} sacas)`);

    // Aldeias e Famílias
    $('#kpiAldeiasCount').text(`${stats.aldeias_ativas || 0} / ${stats.total_aldeias || 29}`);
    $('#kpiFamiliasSub').text(`${Number(stats.familias || 0).toLocaleString('pt-BR')} famílias &bull; ${Number(stats.ajudantes || 0).toLocaleString('pt-BR')} ajudantes`);
  }

  function renderMapMarkersAndCircles(aldeias) {
    pinsLayerGroup.clearLayers();
    circlesLayerGroup.clearLayers();

    if (!aldeias || aldeias.length === 0) return;

    let selectedFilterAldeia = $('#filterAldeia').val();

    aldeias.forEach(ald => {
      let lat = ald.lat;
      let lng = ald.lng;
      if (!lat || !lng) return;

      let isSelected = (selectedFilterAldeia && selectedFilterAldeia === ald.nome) || (selectedAldeiaNome === ald.nome);

      // 1. Círculo Proporcional ao Volume de Sacas
      let volume = ald.total_sacas || 0;
      let radius = Math.max(400, Math.min(2800, Math.sqrt(volume) * 90));

      let circle = L.circle([lat, lng], {
        radius: radius,
        color: isSelected ? '#ffc107' : (ald.color || '#28a745'),
        fillColor: ald.color || '#28a745',
        fillOpacity: isSelected ? 0.48 : (volume > 0 ? 0.25 : 0.10),
        weight: isSelected ? 3.5 : 1.5
      });
      circlesLayerGroup.addLayer(circle);

      // 2. Marcador Numérico (#1 - #29)
      let pinHtml = `<div class="aldeia-pin ${isSelected ? 'active-pin' : ''}" style="background-color: ${ald.color}; width: 26px; height: 26px;">#${ald.num}</div>`;
      let pinIcon = L.divIcon({
        html: pinHtml,
        className: 'custom-aldeia-pin-container',
        iconSize: [26, 26],
        iconAnchor: [13, 13]
      });

      let marker = L.marker([lat, lng], { icon: pinIcon, zIndexOffset: isSelected ? 1000 : 500 });

      // Formatação do Tooltip de Hover
      let topColetores = Object.entries(ald.coletores || {}).slice(0, 2).map(([c, s]) => `${escapeHtml(c)} (${s} sc)`).join(', ');

      let tooltipHtml = `
        <div style="min-width: 270px; max-width: 330px;">
          <div class="d-flex align-items-center mb-2">
            <span class="badge badge-dark mr-2" style="font-size: 11px;">#${ald.num}</span>
            <h6 class="font-weight-bold text-dark m-0" style="font-size:14px;">${escapeHtml(ald.nome)}</h6>
            <span class="badge badge-light border ml-auto text-muted small">${ald.ti || 'TI Oiapoque'}</span>
          </div>
          <div class="small mb-1">
            <i class="fas fa-seedling text-success mr-1"></i><b>Total Coletado:</b> <span class="badge badge-success" style="font-size: 11px;">${Number(ald.total_sacas).toLocaleString('pt-BR')} sacas</span> (${ald.fichas_count} fichas)
          </div>
          <div class="small mb-1 border-top pt-1 text-muted">
            <b>Destinação:</b>
            <div class="mt-1">
              <span class="stat-mini-badge badge-info text-white">Fora: ${Number(ald.fora_sacas).toLocaleString('pt-BR')} sc</span>
              <span class="stat-mini-badge badge-warning text-dark">Consumo: ${Number(ald.consumo_sacas).toLocaleString('pt-BR')} sc (${Number(ald.consumo_litros).toLocaleString('pt-BR')} L)</span>
              <span class="stat-mini-badge badge-danger text-white">Venda: ${Number(ald.venda_sacas).toLocaleString('pt-BR')} sc (${Number(ald.venda_litros).toLocaleString('pt-BR')} L)</span>
            </div>
          </div>
          <div class="small mb-1 border-top pt-1">
            <i class="fas fa-money-bill-wave text-success mr-1"></i><b>Receita Total:</b> R$ ${Number(ald.total_receita_bruta).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
          </div>
          <div class="small mb-1 text-muted">
            <i class="fas fa-users text-primary mr-1"></i>${Number(ald.familias).toLocaleString('pt-BR')} famílias &bull; ${Number(ald.ajudantes).toLocaleString('pt-BR')} ajudantes
          </div>
          ${topColetores ? `<div class="text-muted mt-1 small"><b>Coletores:</b> ${topColetores}</div>` : ''}
          <small class="text-primary d-block mt-2 font-weight-bold">
            <i class="fas fa-hand-pointer mr-1"></i> Clique para focar e detalhar esta aldeia
          </small>
        </div>
      `;

      marker.bindTooltip(tooltipHtml, {
        sticky: true,
        direction: 'auto',
        opacity: 0.98
      });

      let selectThisAldeia = function() {
        selectedAldeiaNome = ald.nome;
        $('#filterAldeia').val(ald.nome);
        applyColetaFilters();
        map.setView([lat, lng], 13, { animate: true });
      };

      marker.on('click', selectThisAldeia);
      circle.on('click', selectThisAldeia);

      pinsLayerGroup.addLayer(marker);
    });
  }

  function showGeneralSidePanel(stats, ano) {
    if (!stats) return;

    let anoLabel = (ano && ano !== 'Todos') ? `Ano ${ano}` : 'Todos os Anos';

    $('#panelAldeiaTitle').html(`<i class="fas fa-layer-group text-primary mr-1"></i> Resumo Consolidado Geral`);
    $('#badgeSelectedStatus').removeClass('badge-success').addClass('badge-primary').text('Todas as Aldeias').show();

    // Cálculos percentuais de destinação das sacas
    let sacasTot = stats.total_sacas || 0;
    let pctFora = sacasTot > 0 ? Math.round((stats.fora_sacas / sacasTot) * 100) : 0;
    let pctCons = sacasTot > 0 ? Math.round((stats.consumo_sacas / sacasTot) * 100) : 0;
    let pctVenda = sacasTot > 0 ? Math.round((stats.venda_sacas / sacasTot) * 100) : 0;

    // Locais de coleta
    let pctManejo = sacasTot > 0 ? Math.round((stats.local_manejo_sacas / sacasTot) * 100) : 0;
    let pctMata = sacasTot > 0 ? Math.round((stats.local_mata_sacas / sacasTot) * 100) : 0;

    let html = `
      <!-- Cabeçalho Consolidado -->
      <div class="mb-3 border-bottom pb-2">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <div class="d-flex align-items-center">
            <span class="badge badge-primary mr-2" style="font-size: 13px;"><i class="fas fa-globe-americas mr-1"></i> Geral</span>
            <h5 class="font-weight-bold text-dark m-0">29 Aldeias de Oiapoque</h5>
          </div>
          <span class="badge badge-light border text-muted">${stats.aldeias_ativas || 0} ativas com coletas</span>
        </div>
        <div class="mt-2 text-muted small">
          <i class="fas fa-calendar-alt mr-1"></i> Base Consolidada &bull; <b>${anoLabel}</b> &bull; Total de <b>${Number(stats.total_fichas || 0).toLocaleString('pt-BR')}</b> fichas
        </div>
      </div>

      <!-- Bloco 1: Destinação das Sacas e Valores Totais -->
      <div class="card card-outline card-success mb-3 shadow-none border">
        <div class="card-header py-1 px-2 bg-light d-flex justify-content-between align-items-center">
          <span class="font-weight-bold text-success small"><i class="fas fa-boxes mr-1"></i> Destinação e Valores Totais (${Number(stats.total_sacas || 0).toLocaleString('pt-BR')} sacas)</span>
          <a href="ficha_coleta" class="btn btn-xs btn-outline-success">Ver Fichas</a>
        </div>
        <div class="card-body p-2">
          <!-- Linha 1: Venda Fora -->
          <div class="p-2 mb-2 rounded bg-light border">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="badge badge-info mr-1">Venda Fora da TI</span>
                <span class="small font-weight-bold text-dark">${Number(stats.fora_sacas || 0).toLocaleString('pt-BR')} sacas</span>
              </div>
              <div class="text-right font-weight-bold text-info" style="font-size: 13px;">
                R$ ${Number(stats.fora_valor || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
              </div>
            </div>
          </div>

          <!-- Linha 2: Venda na Aldeia -->
          <div class="p-2 mb-2 rounded bg-light border">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="badge badge-danger mr-1">Venda na Aldeia</span>
                <span class="small font-weight-bold text-dark">${Number(stats.venda_sacas || 0).toLocaleString('pt-BR')} sacas (${Number(stats.venda_litros || 0).toLocaleString('pt-BR')} L)</span>
              </div>
              <div class="text-right font-weight-bold text-danger" style="font-size: 13px;">
                R$ ${Number(stats.venda_valor || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
              </div>
            </div>
          </div>

          <!-- Linha 3: Consumo na Aldeia (com Valor Estimado) -->
          <div class="p-2 mb-2 rounded bg-light border">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="badge badge-warning text-dark mr-1">Consumo na Aldeia</span>
                <span class="small font-weight-bold text-dark">${Number(stats.consumo_sacas || 0).toLocaleString('pt-BR')} sacas (${Number(stats.consumo_litros || 0).toLocaleString('pt-BR')} L)</span>
              </div>
              <div class="text-right font-weight-bold text-warning" style="font-size: 13px;" title="Estimativa econômica pelo preço médio de venda na aldeia">
                R$ ${Number(stats.consumo_valor_estimado || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
              </div>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 10px;">* Valor econômico do autoconsumo baseado no preço médio de venda na aldeia.</small>
          </div>

          <!-- Barra de Progresso Percentual -->
          <div class="progress progress-dest mb-2">
            <div class="progress-bar bg-info" style="width: ${pctFora}%" title="Venda Fora: ${pctFora}%">${pctFora > 12 ? pctFora + '%' : ''}</div>
            <div class="progress-bar bg-danger" style="width: ${pctVenda}%" title="Venda Aldeia: ${pctVenda}%">${pctVenda > 12 ? pctVenda + '%' : ''}</div>
            <div class="progress-bar bg-warning" style="width: ${pctCons}%" title="Consumo: ${pctCons}%">${pctCons > 12 ? pctCons + '%' : ''}</div>
          </div>

          <!-- Totais Financeiros Consolidados -->
          <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
            <div>
              <span class="small text-muted d-block">Receita Bruta Direta:</span>
              <b class="text-success" style="font-size: 14px;">R$ ${Number(stats.total_receita_bruta || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</b>
            </div>
            <div class="text-right">
              <span class="small text-muted d-block">Movimentação Econômica Total:</span>
              <b class="text-primary" style="font-size: 14px;">R$ ${Number(stats.total_valor_economico || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</b>
            </div>
          </div>
        </div>
      </div>

      <!-- Bloco 2: Local de Coleta -->
      <div class="card card-outline card-primary mb-3 shadow-none border">
        <div class="card-header py-1 px-2 bg-light d-flex justify-content-between align-items-center">
          <span class="font-weight-bold text-primary small"><i class="fas fa-map-pin mr-1"></i> Local de Coleta (Manejo vs Mata)</span>
        </div>
        <div class="card-body p-2">
          <div class="row text-center mb-2">
            <div class="col-6">
              <span class="text-muted small d-block">Área de Manejo</span>
              <span class="badge badge-success" style="font-size:12px;">${Number(stats.local_manejo_sacas || 0).toLocaleString('pt-BR')} sc (${pctManejo}%)</span>
              <small class="text-muted d-block mt-1">${stats.local_manejo_fichas || 0} coletas</small>
            </div>
            <div class="col-6">
              <span class="text-muted small d-block">Área de Mata</span>
              <span class="badge badge-secondary" style="font-size:12px;">${Number(stats.local_mata_sacas || 0).toLocaleString('pt-BR')} sc (${pctMata}%)</span>
              <small class="text-muted d-block mt-1">${stats.local_mata_fichas || 0} coletas</small>
            </div>
          </div>
          <div class="progress progress-dest mb-1">
            <div class="progress-bar bg-success" style="width: ${pctManejo}%" title="Manejo: ${pctManejo}%">${pctManejo > 15 ? pctManejo + '%' : ''}</div>
            <div class="progress-bar bg-secondary" style="width: ${pctMata}%" title="Mata: ${pctMata}%">${pctMata > 15 ? pctMata + '%' : ''}</div>
          </div>
        </div>
      </div>

      <!-- Bloco 3: Mão de Obra e Comunidade -->
      <div class="card card-outline card-warning mb-2 shadow-none border">
        <div class="card-header py-1 px-2 bg-light d-flex justify-content-between align-items-center">
          <span class="font-weight-bold text-warning small"><i class="fas fa-users mr-1"></i> Mão de Obra e Beneficiários</span>
        </div>
        <div class="card-body p-2">
          <div class="row text-center">
            <div class="col-6">
              <span class="text-muted small d-block">Famílias Beneficiadas</span>
              <h5 class="font-weight-bold text-dark m-0">${Number(stats.familias || 0).toLocaleString('pt-BR')}</h5>
            </div>
            <div class="col-6">
              <span class="text-muted small d-block">Ajudantes Envolvidos</span>
              <h5 class="font-weight-bold text-dark m-0">${Number(stats.ajudantes || 0).toLocaleString('pt-BR')}</h5>
            </div>
          </div>
        </div>
      </div>
    `;

    $('#panelAldeiaContent').html(html);
  }

  function showAldeiaSidePanel(ald, isFiltered) {
    $('#panelAldeiaTitle').html(`<i class="fas fa-map-marker-alt text-success mr-1"></i> #${ald.num} Aldeia ${escapeHtml(ald.nome)}`);
    if (isFiltered) {
      $('#badgeSelectedStatus').removeClass('badge-primary').addClass('badge-success').text('Selecionada no Filtro').show();
    } else {
      $('#badgeSelectedStatus').removeClass('badge-primary').addClass('badge-success').text('Inspecionada no Mapa').show();
    }

    let sacasTot = ald.total_sacas || 0;
    let pctFora = sacasTot > 0 ? Math.round((ald.fora_sacas / sacasTot) * 100) : 0;
    let pctCons = sacasTot > 0 ? Math.round((ald.consumo_sacas / sacasTot) * 100) : 0;
    let pctVenda = sacasTot > 0 ? Math.round((ald.venda_sacas / sacasTot) * 100) : 0;

    let pctManejo = sacasTot > 0 ? Math.round((ald.local_manejo_sacas / sacasTot) * 100) : 0;
    let pctMata = sacasTot > 0 ? Math.round((ald.local_mata_sacas / sacasTot) * 100) : 0;

    // Coletores da Aldeia
    let coletoresHtml = '';
    let colEntries = Object.entries(ald.coletores || {});
    if (colEntries.length > 0) {
      colEntries.slice(0, 6).forEach(([c, sc]) => {
        coletoresHtml += `<span class="stat-mini-badge badge-light border"><i class="fas fa-user text-success mr-1"></i>${escapeHtml(c)}: <b>${Number(sc).toLocaleString('pt-BR')} sc</b></span> `;
      });
      if (colEntries.length > 6) {
        coletoresHtml += `<span class="small text-muted font-italic">+${colEntries.length - 6} outros</span>`;
      }
    } else {
      coletoresHtml = '<small class="text-muted">Nenhum coletor registrado.</small>';
    }

    let html = `
      <!-- Cabeçalho da Aldeia -->
      <div class="mb-3 border-bottom pb-2">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <div class="d-flex align-items-center">
            <span class="badge badge-dark mr-2" style="font-size: 13px;">#${ald.num}</span>
            <h5 class="font-weight-bold text-dark m-0">${escapeHtml(ald.nome)}</h5>
          </div>
          <button type="button" class="btn btn-xs btn-outline-secondary" onclick="resetAldeiaSelection()" title="Limpar seleção de aldeia e ver resumo geral consolidado">
            <i class="fas fa-undo mr-1"></i> Ver Geral
          </button>
        </div>
        <div class="mt-2 d-flex justify-content-between align-items-center flex-wrap">
          <span class="badge badge-light border px-2 py-1 text-muted"><i class="fas fa-map-pin mr-1"></i>${ald.lat.toFixed(4)}, ${ald.lng.toFixed(4)}</span>
          <span class="badge badge-info">${ald.ti || 'TI Oiapoque'} &bull; ${ald.regiao || 'Região'}</span>
        </div>
      </div>

      <!-- Bloco 1: Destinação das Sacas e Valores Totais -->
      <div class="card card-outline card-success mb-3 shadow-none border">
        <div class="card-header py-1 px-2 bg-light d-flex justify-content-between align-items-center">
          <span class="font-weight-bold text-success small"><i class="fas fa-boxes mr-1"></i> Destinação e Valores (${Number(ald.total_sacas || 0).toLocaleString('pt-BR')} sacas)</span>
          <a href="ficha_coleta" class="btn btn-xs btn-outline-success">Ver Fichas</a>
        </div>
        <div class="card-body p-2">
          <!-- Linha 1: Venda Fora -->
          <div class="p-2 mb-2 rounded bg-light border">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="badge badge-info mr-1">Venda Fora da TI</span>
                <span class="small font-weight-bold text-dark">${Number(ald.fora_sacas || 0).toLocaleString('pt-BR')} sacas</span>
              </div>
              <div class="text-right font-weight-bold text-info" style="font-size: 13px;">
                R$ ${Number(ald.fora_valor || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
              </div>
            </div>
          </div>

          <!-- Linha 2: Venda na Aldeia -->
          <div class="p-2 mb-2 rounded bg-light border">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="badge badge-danger mr-1">Venda na Aldeia</span>
                <span class="small font-weight-bold text-dark">${Number(ald.venda_sacas || 0).toLocaleString('pt-BR')} sacas (${Number(ald.venda_litros || 0).toLocaleString('pt-BR')} L)</span>
              </div>
              <div class="text-right font-weight-bold text-danger" style="font-size: 13px;">
                R$ ${Number(ald.venda_valor || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
              </div>
            </div>
          </div>

          <!-- Linha 3: Consumo na Aldeia (com Valor Estimado) -->
          <div class="p-2 mb-2 rounded bg-light border">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="badge badge-warning text-dark mr-1">Consumo na Aldeia</span>
                <span class="small font-weight-bold text-dark">${Number(ald.consumo_sacas || 0).toLocaleString('pt-BR')} sacas (${Number(ald.consumo_litros || 0).toLocaleString('pt-BR')} L)</span>
              </div>
              <div class="text-right font-weight-bold text-warning" style="font-size: 13px;" title="Estimativa econômica pelo preço médio de venda na aldeia">
                R$ ${Number(ald.consumo_valor_estimado || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
              </div>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 10px;">* Valor estimado calculado com base no preço de venda praticado na aldeia.</small>
          </div>

          <!-- Barra de Progresso Percentual -->
          <div class="progress progress-dest mb-2">
            <div class="progress-bar bg-info" style="width: ${pctFora}%" title="Venda Fora: ${pctFora}%">${pctFora > 12 ? pctFora + '%' : ''}</div>
            <div class="progress-bar bg-danger" style="width: ${pctVenda}%" title="Venda Aldeia: ${pctVenda}%">${pctVenda > 12 ? pctVenda + '%' : ''}</div>
            <div class="progress-bar bg-warning" style="width: ${pctCons}%" title="Consumo: ${pctCons}%">${pctCons > 12 ? pctCons + '%' : ''}</div>
          </div>

          <!-- Totais Financeiros Consolidados -->
          <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
            <div>
              <span class="small text-muted d-block">Receita Bruta Direta:</span>
              <b class="text-success" style="font-size: 14px;">R$ ${Number(ald.total_receita_bruta || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</b>
            </div>
            <div class="text-right">
              <span class="small text-muted d-block">Valor Econômico Total:</span>
              <b class="text-primary" style="font-size: 14px;">R$ ${Number(ald.total_valor_economico || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</b>
            </div>
          </div>
        </div>
      </div>

      <!-- Bloco 2: Local de Coleta -->
      <div class="card card-outline card-primary mb-3 shadow-none border">
        <div class="card-header py-1 px-2 bg-light d-flex justify-content-between align-items-center">
          <span class="font-weight-bold text-primary small"><i class="fas fa-map-pin mr-1"></i> Local de Coleta (Manejo vs Mata)</span>
        </div>
        <div class="card-body p-2">
          <div class="row text-center mb-2">
            <div class="col-6">
              <span class="text-muted small d-block">Área de Manejo</span>
              <span class="badge badge-success" style="font-size:12px;">${Number(ald.local_manejo_sacas || 0).toLocaleString('pt-BR')} sc (${pctManejo}%)</span>
              <small class="text-muted d-block mt-1">${ald.local_manejo_fichas || 0} coletas</small>
            </div>
            <div class="col-6">
              <span class="text-muted small d-block">Área de Mata</span>
              <span class="badge badge-secondary" style="font-size:12px;">${Number(ald.local_mata_sacas || 0).toLocaleString('pt-BR')} sc (${pctMata}%)</span>
              <small class="text-muted d-block mt-1">${ald.local_mata_fichas || 0} coletas</small>
            </div>
          </div>
          <div class="progress progress-dest mb-1">
            <div class="progress-bar bg-success" style="width: ${pctManejo}%" title="Manejo: ${pctManejo}%">${pctManejo > 15 ? pctManejo + '%' : ''}</div>
            <div class="progress-bar bg-secondary" style="width: ${pctMata}%" title="Mata: ${pctMata}%">${pctMata > 15 ? pctMata + '%' : ''}</div>
          </div>
        </div>
      </div>

      <!-- Bloco 3: Mão de Obra e Coletores -->
      <div class="card card-outline card-warning mb-2 shadow-none border">
        <div class="card-header py-1 px-2 bg-light d-flex justify-content-between align-items-center">
          <span class="font-weight-bold text-warning small"><i class="fas fa-users mr-1"></i> Mão de Obra e Coletores</span>
        </div>
        <div class="card-body p-2">
          <div class="row text-center mb-2">
            <div class="col-6">
              <span class="text-muted small d-block">Famílias</span>
              <h5 class="font-weight-bold text-dark m-0">${Number(ald.familias || 0).toLocaleString('pt-BR')}</h5>
            </div>
            <div class="col-6">
              <span class="text-muted small d-block">Ajudantes</span>
              <h5 class="font-weight-bold text-dark m-0">${Number(ald.ajudantes || 0).toLocaleString('pt-BR')}</h5>
            </div>
          </div>
          <div class="small font-weight-bold text-muted mb-1 border-top pt-2">Principais Coletores da Aldeia:</div>
          <div>${coletoresHtml}</div>
        </div>
      </div>
    `;

    $('#panelAldeiaContent').html(html);
  }

  function renderCharts(rankings, ano) {
    if (!rankings) return;

    let anoSub = (ano && ano !== 'Todos') ? `Ano: ${ano}` : 'Todos os Anos';
    let aldVal = $('#filterAldeia').val() || selectedAldeiaNome;
    let localVal = $('#filterLocal').val();
    let subParts = [anoSub];
    if (aldVal && aldVal !== 'Todas') subParts.push(`Aldeia: ${aldVal}`);
    if (localVal && localVal !== 'Todos') subParts.push(`Local: ${localVal}`);
    let fullSub = subParts.join(' • ');

    let categories = rankings.chart_aldeias_names || [];

    // 1. Gráfico 1: Destinação das Sacas por Aldeia
    let type1 = chartTypes.chart1 || 'column';
    chart1 = Highcharts.chart('chartDestinoAldeias', {
      chart: { type: type1, style: { fontFamily: 'Source Sans Pro, sans-serif' } },
      title: { text: null },
      subtitle: { text: fullSub, align: 'left', style: { fontSize: '11px', color: '#6c757d' } },
      xAxis: {
        categories: categories,
        crosshair: true,
        labels: { rotation: type1 === 'bar' ? 0 : -35, style: { fontSize: '11px' } }
      },
      yAxis: {
        min: 0,
        title: { text: 'Quantidade de Sacas' }
      },
      tooltip: {
        shared: true,
        headerFormat: '<b>{point.x}</b><br/>',
        pointFormat: '{series.name}: <b>{point.y} sc</b><br/>'
      },
      plotOptions: {
        column: { pointPadding: 0.1, borderWidth: 0, borderRadius: 3 },
        bar: { pointPadding: 0.1, borderWidth: 0, borderRadius: 3 },
        area: { fillOpacity: 0.3 }
      },
      series: [
        { name: 'Venda Fora da TI', data: rankings.chart_sacas_fora || [], color: '#17a2b8' },
        { name: 'Consumo na Aldeia', data: rankings.chart_sacas_consumo || [], color: '#ffc107' },
        { name: 'Venda na Aldeia', data: rankings.chart_sacas_venda || [], color: '#dc3545' }
      ],
      credits: { enabled: false }
    });

    // 2. Gráfico 2: Mão de Obra e Famílias
    let type2 = chartTypes.chart2 || 'column';
    chart2 = Highcharts.chart('chartPessoasAldeias', {
      chart: { type: type2, style: { fontFamily: 'Source Sans Pro, sans-serif' } },
      title: { text: null },
      subtitle: { text: fullSub, align: 'left', style: { fontSize: '11px', color: '#6c757d' } },
      xAxis: {
        categories: categories,
        crosshair: true,
        labels: { rotation: type2 === 'bar' ? 0 : -35, style: { fontSize: '11px' } }
      },
      yAxis: {
        min: 0,
        title: { text: 'Pessoas / Famílias' }
      },
      tooltip: {
        shared: true,
        headerFormat: '<b>{point.x}</b><br/>'
      },
      plotOptions: {
        column: { pointPadding: 0.1, borderWidth: 0, borderRadius: 3 },
        bar: { pointPadding: 0.1, borderWidth: 0, borderRadius: 3 },
        area: { fillOpacity: 0.3 }
      },
      series: [
        { name: 'Ajudantes Envolvidos', data: rankings.chart_ajudantes || [], color: '#007bff' },
        { name: 'Famílias Beneficiadas', data: rankings.chart_familias || [], color: '#28a745' }
      ],
      credits: { enabled: false }
    });

    // 3. Gráfico 3: Volume de Litros vs Venda na Aldeia (R$)
    let type3 = chartTypes.chart3 || 'column';
    chart3 = Highcharts.chart('chartDualYVenda', {
      chart: { type: type3, style: { fontFamily: 'Source Sans Pro, sans-serif' } },
      title: { text: null },
      subtitle: { text: fullSub, align: 'left', style: { fontSize: '11px', color: '#6c757d' } },
      xAxis: {
        categories: categories,
        crosshair: true,
        labels: { rotation: type3 === 'bar' ? 0 : -35, style: { fontSize: '11px' } }
      },
      yAxis: [
        {
          labels: { format: '{value} L', style: { color: '#17a2b8' } },
          title: { text: 'Volume em Litros', style: { color: '#17a2b8' } }
        },
        {
          title: { text: 'Receita de Venda (R$)', style: { color: '#28a745' } },
          labels: { format: 'R$ {value:.,2f}', style: { color: '#28a745' } },
          opposite: true
        }
      ],
      tooltip: {
        shared: true,
        headerFormat: '<b>{point.x}</b><br/>'
      },
      plotOptions: {
        column: { pointPadding: 0.1, borderWidth: 0, borderRadius: 3 },
        bar: { pointPadding: 0.1, borderWidth: 0, borderRadius: 3 },
        area: { fillOpacity: 0.3 }
      },
      series: [
        { name: 'Volume de Litros Total', yAxis: 0, data: rankings.chart_litros_total || [], color: '#17a2b8' },
        { name: 'Receita Venda Aldeia (R$)', yAxis: 1, data: rankings.chart_valor_receita || [], color: '#28a745' }
      ],
      credits: { enabled: false }
    });

    // 4. Gráfico 4: Proporção Total da Destinação das Sacas
    let type4 = chartTypes.chart4 || 'pie';
    if (type4 === 'pie') {
      chart4 = Highcharts.chart('chartDestinoTotal', {
        chart: { type: 'pie', style: { fontFamily: 'Source Sans Pro, sans-serif' } },
        title: { text: null },
        subtitle: { text: fullSub, align: 'left', style: { fontSize: '11px', color: '#6c757d' } },
        tooltip: {
          pointFormat: '<b>{point.y} sacas</b> ({point.percentage:.1f}%)'
        },
        plotOptions: {
          pie: {
            innerSize: '55%',
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
              enabled: true,
              format: '<b>{point.name}</b>: {point.percentage:.1f}% ({point.y} sc)',
              style: { fontSize: '11px' }
            },
            showInLegend: true
          }
        },
        series: [{
          name: 'Destinação',
          colorByPoint: true,
          data: rankings.destinacao_total_pie || []
        }],
        credits: { enabled: false }
      });
    } else {
      let catPie = (rankings.destinacao_total_pie || []).map(p => p.name);
      let dataPie = (rankings.destinacao_total_pie || []).map(p => ({ y: p.y, color: p.color }));
      chart4 = Highcharts.chart('chartDestinoTotal', {
        chart: { type: type4, style: { fontFamily: 'Source Sans Pro, sans-serif' } },
        title: { text: null },
        subtitle: { text: fullSub, align: 'left', style: { fontSize: '11px', color: '#6c757d' } },
        xAxis: { categories: catPie, crosshair: true },
        yAxis: { min: 0, title: { text: 'Quantidade de Sacas' } },
        tooltip: {
          pointFormat: '<b>{point.y} sacas</b>'
        },
        plotOptions: {
          column: { pointPadding: 0.2, borderWidth: 0, borderRadius: 4, dataLabels: { enabled: true } },
          bar: { pointPadding: 0.2, borderWidth: 0, borderRadius: 4, dataLabels: { enabled: true } }
        },
        series: [{
          name: 'Destinação Total',
          data: dataPie
        }],
        legend: { enabled: false },
        credits: { enabled: false }
      });
    }
  }

  function changeChartType(chartId, newType) {
    chartTypes[chartId] = newType;
    if (rawApiData && rawApiData.rankings) {
      renderCharts(rawApiData.rankings, rawApiData.filter_ano);
    }
  }

  function onAldeiaSelectChange() {
    let ald = $('#filterAldeia').val();
    selectedAldeiaNome = ald || null;
    loadColetaData(function() {
      if (ald) {
        let found = allAldeiasList.find(a => a.nome === ald);
        if (found && found.lat && found.lng) {
          map.setView([found.lat, found.lng], 13, { animate: true });
        }
      }
    });
  }

  function applyColetaFilters() {
    loadColetaData();
  }

  function resetAldeiaSelection() {
    $('#filterAldeia').val('');
    selectedAldeiaNome = null;
    loadColetaData();
    resetMapView();
  }

  function resetColetaFilters() {
    $('#filterAno').val('Todos');
    $('#filterAldeia').val('');
    $('#filterLocal').val('');
    $('#filterSearch').val('');
    selectedAldeiaNome = null;
    loadColetaData();
    resetMapView();
  }

  function resetMapView() {
    map.setView([3.45, -51.52], 9);
  }

  function toggleMapPins() {
    let on = $('#togglePinsSwitch').is(':checked');
    if (on) {
      if (!map.hasLayer(pinsLayerGroup)) map.addLayer(pinsLayerGroup);
    } else {
      if (map.hasLayer(pinsLayerGroup)) map.removeLayer(pinsLayerGroup);
    }
  }

  function toggleMapCircles() {
    let on = $('#toggleCirclesSwitch').is(':checked');
    if (on) {
      if (!map.hasLayer(circlesLayerGroup)) map.addLayer(circlesLayerGroup);
    } else {
      if (map.hasLayer(circlesLayerGroup)) map.removeLayer(circlesLayerGroup);
    }
  }

  function exportColetaExcel() {
    let ano = $('#filterAno').val();
    let aldeia = $('#filterAldeia').val() || selectedAldeiaNome || '';
    let local = $('#filterLocal').val() || '';
    let busca = $('#filterSearch').val() || '';

    let queryParams = {
      ano: ano,
      aldeia: aldeia,
      local_coleta: local,
      busca: busca
    };

    window.location.href = 'api/export_dashboard_coleta_excel.php?' + $.param(queryParams);
  }

  function escapeHtml(text) {
    if (!text) return '';
    return String(text)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }
</script>
</body>
</html>
