<?php
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/utils.php';

requireLogin();

// Fetch Dashboard Stats
$totalDestinado = 0;
$totalGarrafa500 = 0;
$totalGarrafa = 0;
$totalSaquinha = 0;
$totalVendidoGarrafa = 0;
$totalVendidoSaquinha = 0;

$chartLabels = [];
$chartDataGarrafa = [];
$chartDataGarrafa500 = [];
$chartDataSaquinha = [];

try {
    // Totais Gerais
    $stmt = $pdo->query("
        SELECT tipo_envase, 
               SUM(qtd_destino) as total_qtd, 
               SUM(CASE WHEN local_destino = 'Venda Km 18' THEN valor_venda ELSE 0 END) as total_venda 
        FROM destino_prod 
        GROUP BY tipo_envase
    ");
    while ($row = $stmt->fetch()) {
        $totalDestinado += $row['total_qtd'];
        if ($row['tipo_envase'] === 'Garrafa 1L') {
            $totalGarrafa = $row['total_qtd'];
            $totalVendidoGarrafa = $row['total_venda'];
        }
        if ($row['tipo_envase'] === 'Garrafa 500ml') {
            $totalGarrafa500 = $row['total_qtd'];
            $totalVendidoGarrafa500 = $row['total_venda'];
        }
        if ($row['tipo_envase'] === 'Saquinha 1Kg') {
            $totalSaquinha = $row['total_qtd'];
            $totalVendidoSaquinha = $row['total_venda'];
        }
    }

    // Dados para os gráficos por local_destino
    $stmtChart = $pdo->query("
        SELECT local_destino, 
               SUM(CASE WHEN tipo_envase = 'Garrafa 1L' THEN qtd_destino ELSE 0 END) as qtd_garrafa,
               SUM(CASE WHEN tipo_envase = 'Garrafa 500ml' THEN qtd_destino ELSE 0 END) as qtd_garrafa_500,
               SUM(CASE WHEN tipo_envase = 'Saquinha 1Kg' THEN qtd_destino ELSE 0 END) as qtd_saquinha
        FROM destino_prod 
        GROUP BY local_destino
        ORDER BY local_destino ASC
    ");

    while ($row = $stmtChart->fetch()) {
        $local = $row['local_destino'];
        if ((int)$row['qtd_garrafa'] > 0) {
            $chartDataGarrafa[] = ['name' => $local, 'y' => (int)$row['qtd_garrafa']];
        }
        if ((int)$row['qtd_garrafa_500'] > 0) {
            $chartDataGarrafa500[] = ['name' => $local, 'y' => (int)$row['qtd_garrafa_500']];
        }
        if ((int)$row['qtd_saquinha'] > 0) {
            $chartDataSaquinha[] = ['name' => $local, 'y' => (int)$row['qtd_saquinha']];
        }
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
  <title>Dashboard - Destino de Produção</title> 

  <!-- App favicon -->
  <link rel="shortcut icon" href="assets/img/favicon.ico">

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
          <li class="nav-item"><a href="dashboard_destino" class="nav-link active"><i class="nav-icon fas fa-chart-bar"></i><p>Dashboard Destino</p></a></li>
          <li class="nav-item"><a href="dashboard_coleta" class="nav-link"><i class="nav-icon fas fa-chart-line"></i><p>Dashboard Coleta</p></a></li>
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
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Dashboard - Destino de Produção</h1>
          </div>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        
        <!-- Cards -->
        <div class="row">
          <div class="col-lg-12">
            <h5 class="mb-2">Quantidades Destinadas</h5>
          </div>
          <div class="col-lg-4 col-12">
            <div class="small-box bg-info">
              <div class="inner">
                <h3><?= number_format($totalDestinado, 0, ',', '.') ?></h3>
                <p>Total Destinado</p>
              </div>
              <div class="icon"><i class="fas fa-boxes"></i></div>
            </div>
          </div>
          <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3><?= number_format($totalGarrafa, 0, ',', '.') ?></h3>
                <p>Garrafa 1L</p>
              </div>
              <div class="icon"><i class="fas fa-wine-bottle"></i></div>
            </div>
          </div>
          <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3><?= number_format($totalGarrafa500, 0, ',', '.') ?></h3>
                <p>Garrafa 500ml</p>
              </div>
              <div class="icon"><i class="fas fa-wine-bottle"></i></div>
            </div>
          </div>
          <div class="col-lg-4 col-6">
            <div class="small-box bg-warning">
              <div class="inner">
                <h3><?= number_format($totalSaquinha, 0, ',', '.') ?></h3>
                <p>Saquinha 1Kg</p>
              </div>
              <div class="icon"><i class="fas fa-box"></i></div>
            </div>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-lg-12">
            <h5 class="mb-2">Valores Vendidos (Apenas Venda Km 18)</h5>
          </div>
          <div class="col-lg-6 col-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3><sup style="font-size: 20px">R$</sup> <?= number_format($totalVendidoGarrafa, 2, ',', '.') ?></h3>
                <p>Total Vendido - Garrafa 1L</p>
              </div>
              <div class="icon"><i class="fas fa-dollar-sign"></i></div>
            </div>
          </div>
          <div class="col-lg-6 col-6">
            <div class="small-box bg-warning">
              <div class="inner">
                <h3><sup style="font-size: 20px">R$</sup> <?= number_format($totalVendidoSaquinha, 2, ',', '.') ?></h3>
                <p>Total Vendido - Saquinha 1Kg</p>
              </div>
              <div class="icon"><i class="fas fa-dollar-sign"></i></div>
            </div>
          </div>
        </div>

        <!-- Charts -->
        <div class="row mt-3">
          <div class="col-md-12">
            <div class="card card-success">
              <div class="card-header">
                <h3 class="card-title">Quantidade Destinada (Garrafa 1L) por Local</h3>
              </div>
              <div class="card-body">
                <div id="garrafaChart" style="min-height: 250px; height: 250px; margin: 0 auto"></div>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="card card-info">
              <div class="card-header">
                <h3 class="card-title">Quantidade Destinada (Garrafa 500ml) por Local</h3>
              </div>
              <div class="card-body">
                <div id="garrafa500Chart" style="min-height: 250px; height: 250px; margin: 0 auto"></div>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="card card-warning">
              <div class="card-header">
                <h3 class="card-title">Quantidade Destinada (Saquinha 1Kg) por Local</h3>
              </div>
              <div class="card-body">
                <div id="saquinhaChart" style="min-height: 250px; height: 250px; margin: 0 auto"></div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <footer class="main-footer"><strong>Copyright &copy; 2026 UASEI.</strong></footer>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/highcharts/code/highcharts.js"></script>
<script src="assets/highcharts/code/modules/exporting.js"></script>
<script src="assets/highcharts/code/modules/export-data.js"></script>
<script src="assets/highcharts/code/modules/offline-exporting.js"></script>
<script src="assets/highcharts/code/modules/accessibility.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
  $(function () {
    const labels = <?= json_encode($chartLabels) ?>;

    function createChart(ctxId, label, data, color, titleText = '', subtitleText = '') {
        Highcharts.chart(ctxId, {
            chart: { type: 'column' },
            title: { text: titleText },
            subtitle: { text: subtitleText },
            xAxis: {
                type: 'category',
                crosshair: true
            },
            yAxis: {
                min: 0,
                title: { text: null }
            },
            tooltip: {
                headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                    '<td style="padding:0"><b>{point.y}</b></td></tr>',
                footerFormat: '</table>',
                shared: true,
                useHTML: true
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0,
                    dataLabels: { enabled: true }
                }
            },
            series: [{
                name: label,
                data: data,
                color: color
            }],
            exporting: { enabled: true },
            credits: { enabled: false }
        });
    }

    createChart('garrafaChart', 'Qtd. Garrafa 1L', <?= json_encode($chartDataGarrafa) ?>, 'rgba(40, 167, 69, 0.8)', 'Quantidade de garrafas por destino', 'Ano: 2026');
    createChart('garrafa500Chart', 'Qtd. Garrafa 500ml', <?= json_encode($chartDataGarrafa500) ?>, 'rgba(23, 162, 184, 0.8)', 'Quantidade de garrafas 500ml por destino', 'Ano: 2026');
    createChart('saquinhaChart', 'Qtd. Saquinha 1Kg', <?= json_encode($chartDataSaquinha) ?>, 'rgba(255, 193, 7, 0.8)');
  })
</script>
</body>
</html>
