<?php
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/utils.php';

requireLogin();

// Fetch Dashboard Stats
$totalProducao = 0;
$totalGarrafa = 0;
$totalGarrafa500 = 0;
$totalSaquinha = 0;

$chartLabels = [];
$chartDataGarrafa = [];
$chartDataGarrafa500 = [];
$chartDataSaquinha = [];

try {
    // Totais gerais
    $stmt = $pdo->query("SELECT tipo_envase, SUM(qtd_produzido) as total FROM controle_interno GROUP BY tipo_envase");
    while ($row = $stmt->fetch()) {
        $totalProducao += $row['total'];
        if ($row['tipo_envase'] === 'Garrafa 1L') $totalGarrafa = $row['total'];
        if ($row['tipo_envase'] === 'Garrafa 500ml') $totalGarrafa500 = $row['total'];
        if ($row['tipo_envase'] === 'Saquinha 1Kg') $totalSaquinha = $row['total'];
    }

    // Dados para o gráfico (Todos os dias agrupados por data)
    $stmtChart = $pdo->query("
        SELECT data_chegada, tipo_envase, SUM(qtd_produzido) as total 
        FROM controle_interno 
        GROUP BY data_chegada, tipo_envase
        ORDER BY data_chegada ASC
    ");

    $chartRaw = [];
    while ($row = $stmtChart->fetch()) {
        // Concatenar ' UTC' evita que o fuso horário do servidor modifique o timestamp
        $timestamp = strtotime($row['data_chegada'] . ' UTC');
        if (!isset($chartRaw[$timestamp])) {
            $chartRaw[$timestamp] = ['Garrafa 1L' => 0, 'Garrafa 500ml' => 0, 'Saquinha 1Kg' => 0];
        }
        $chartRaw[$timestamp][$row['tipo_envase']] += (float)$row['total'];
    }

    foreach ($chartRaw as $ts => $totals) {
        $ts_js = $ts * 1000;
        $chartDataGarrafa[] = [$ts_js, $totals['Garrafa 1L']];
        $chartDataGarrafa500[] = [$ts_js, $totals['Garrafa 500ml']];
        $chartDataSaquinha[] = [$ts_js, $totals['Saquinha 1Kg']];
    }
} catch (\PDOException $e) {
    // Silently fail or log error
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - Controle Interno</title>

  <!-- App favicon -->
  <link rel="shortcut icon" href="assets/img/favicon.ico">

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a class="nav-link" href="api/logout" role="button">
          <i class="fas fa-sign-out-alt"></i> Sair
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="dashboard" class="brand-link">
      <span class="brand-text font-weight-light ml-3"><b>Sistema de Safra - UASEI</b></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="info">
          <a href="#" class="d-block"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuário') ?></a>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item"><a href="dashboard" class="nav-link active"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard Interno</p></a></li>
          <li class="nav-item"><a href="dashboard_chegada" class="nav-link"><i class="nav-icon fas fa-chart-pie"></i><p>Dashboard Chegada</p></a></li>
          <li class="nav-item"><a href="dashboard_destino" class="nav-link"><i class="nav-icon fas fa-chart-bar"></i><p>Dashboard Destino</p></a></li>
          <li class="nav-item"><a href="dashboard_coleta" class="nav-link"><i class="nav-icon fas fa-chart-line"></i><p>Dashboard Coleta</p></a></li>
          <li class="nav-item"><a href="controle_interno" class="nav-link"><i class="nav-icon fas fa-clipboard-list"></i><p>Controle Interno</p></a></li>
          <li class="nav-item"><a href="chegada_fruto" class="nav-link"><i class="nav-icon fas fa-truck"></i><p>Controle de Chegada</p></a></li>
          <li class="nav-item"><a href="controle_destino" class="nav-link"><i class="nav-icon fas fa-share-square"></i><p>Controle de Destino</p></a></li>
          <li class="nav-item"><a href="ficha_coleta" class="nav-link"><i class="nav-icon fas fa-file-alt"></i><p>Ficha de Coleta</p></a></li>
          <li class="nav-item"><a href="usuarios" class="nav-link"><i class="nav-icon fas fa-users"></i><p>Usuários</p></a></li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Dashboard - Controle Interno</h1>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        
        <!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h3><?= number_format($totalProducao, 0, ',', '.') ?></h3>
                <p>Total Produzido (un.)</p>
              </div>
              <div class="icon">
                <i class="fas fa-boxes"></i>
              </div>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3><?= number_format($totalGarrafa, 0, ',', '.') ?></h3>
                <p>Total de Garrafa 1L</p>
              </div>
              <div class="icon">
                <i class="fas fa-wine-bottle"></i>
              </div>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-4 col-12">
            <div class="small-box bg-success">
              <div class="inner">
                <h3><?= number_format($totalGarrafa500, 0, ',', '.') ?></h3>
                <p>Total de Garrafa 500ml</p>
              </div>
              <div class="icon">
                <i class="fas fa-wine-bottle"></i>
              </div>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-4 col-12">
            <div class="small-box bg-warning">
              <div class="inner">
                <h3><?= number_format($totalSaquinha, 0, ',', '.') ?></h3>
                <p>Total de Saquinha 1Kg</p>
              </div>
              <div class="icon">
                <i class="fas fa-shopping-bag"></i>
              </div>
            </div>
          </div>
          <!-- ./col -->
        </div>
        <!-- /.row -->

        <div class="row">
          <div class="col-12">
            <!-- BAR CHART -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Produção da Agroindústria</h3>
              </div>
              <div class="card-body">
                <div id="barChart" style="min-height: 350px; height: 350px; margin: 0 auto"></div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
        </div>

      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- Main Footer -->
  <footer class="main-footer">
    <strong>Copyright &copy; 2026 UASEI.</strong> Todos os direitos reservados.
  </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Highcharts -->
<script src="assets/highcharts/code/highcharts.js"></script>
<script src="assets/highcharts/code/modules/exporting.js"></script>
<script src="assets/highcharts/code/modules/export-data.js"></script>
<script src="assets/highcharts/code/modules/offline-exporting.js"></script>
<script src="assets/highcharts/code/modules/accessibility.js"></script>

<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
  $(function () {
    Highcharts.setOptions({
        time: {
            useUTC: true
        }
    });

    Highcharts.chart('barChart', {
        chart: { type: 'spline' },
        title: { text: 'Total produzido por tipo de embalagem 2026' },
        xAxis: {
            type: 'datetime',
            tickInterval: 10 * 24 * 3600 * 1000, // 12 dias em milissegundos
            labels: {
                format: '{value:%d/%m}'
            },
            title: { text: 'Data da Produção' }
        },
        yAxis: {
            min: 0,
            title: { text: 'Quantidade Produzida' }
        },
        tooltip: {
            headerFormat: '<b>{series.name}</b><br>',
            pointFormat: '{point.x:%d/%m/%Y}: <b>{point.y}</b>'
        },
        plotOptions: {
            spline: {
                marker: { enabled: true }
            }
        },
        series: [
            { name: 'Garrafa 1L', data: <?= json_encode(array_values($chartDataGarrafa)) ?>, color: '#3c8dbc' },
            { name: 'Garrafa 500ml', data: <?= json_encode(array_values($chartDataGarrafa500)) ?>, color: '#eff318ff' },
            { name: 'Saquinha 1Kg', data: <?= json_encode(array_values($chartDataSaquinha)) ?>, color: '#20792dff' }
        ],
        exporting: { enabled: true },
        credits: { enabled: false }
    });
  });
</script>
</body>
</html>
