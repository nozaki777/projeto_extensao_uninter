<?php
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/utils.php';

requireLogin();

// Fetch Dashboard Stats
$sumSaca = 0;
$sumReal = 0;
$sumGasolina = 0;
$sumFrete = 0;

$chartLabels = [];
$chartDataSaca = [];
$chartDataReal = [];
$chartDataGasolina = [];
$chartDataFrete = [];

try {
    // Totais gerais
    $stmt = $pdo->query("SELECT SUM(qtd_saca) as s_saca, SUM(valor_real) as s_real, SUM(gasolina_valor) as s_gasolina, SUM(valor_frete) as s_frete FROM chegada_fruto");
    if ($row = $stmt->fetch()) {
        $sumSaca = $row['s_saca'] ?? 0;
        $sumReal = $row['s_real'] ?? 0;
        $sumGasolina = $row['s_gasolina'] ?? 0;
        $sumFrete = $row['s_frete'] ?? 0;
    }

    // Dados para os gráficos por origem
    $stmtChart = $pdo->query("
        SELECT origem, SUM(qtd_saca) as t_saca, SUM(valor_real) as t_real, SUM(gasolina_valor) as t_gasolina, SUM(valor_frete) as t_frete 
        FROM chegada_fruto 
        GROUP BY origem
        ORDER BY origem ASC
    ");

    while ($row = $stmtChart->fetch()) {
        $origem = $row['origem'];
        if ((float)$row['t_saca'] > 0) $chartDataSaca[] = ['name' => $origem, 'y' => (float)$row['t_saca']];
        if ((float)$row['t_real'] > 0) $chartDataReal[] = ['name' => $origem, 'y' => (float)$row['t_real']];
        if ((float)$row['t_gasolina'] > 0) $chartDataGasolina[] = ['name' => $origem, 'y' => (float)$row['t_gasolina']];
        if ((float)$row['t_frete'] > 0) $chartDataFrete[] = ['name' => $origem, 'y' => (float)$row['t_frete']];
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
  <title>Dashboard - Chegada de Frutos</title> 

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
          <li class="nav-item"><a href="dashboard_chegada" class="nav-link active"><i class="nav-icon fas fa-chart-pie"></i><p>Dashboard Chegada</p></a></li>
          <li class="nav-item"><a href="dashboard_destino" class="nav-link"><i class="nav-icon fas fa-chart-bar"></i><p>Dashboard Destino</p></a></li>
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
            <h1 class="m-0">Dashboard - Controle de Chegada de Frutos</h1>
          </div>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        
        <!-- Cards -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h3><?= number_format($sumSaca, $sumSaca == (int)$sumSaca ? 0 : 2, ',', '.') ?></h3>
                <p>Sacas Compradas</p>
              </div>
              <div class="icon"><i class="fas fa-boxes"></i></div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3><sup style="font-size: 20px">R$</sup> <?= number_format($sumReal, 2, ',', '.') ?></h3>
                <p>Total em Compras</p>
              </div>
              <div class="icon"><i class="fas fa-dollar-sign"></i></div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
              <div class="inner">
                <h3><sup style="font-size: 20px">R$</sup> <?= number_format($sumGasolina, 2, ',', '.') ?></h3>
                <p>Total em Gasolina</p>
              </div>
              <div class="icon"><i class="fas fa-gas-pump"></i></div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
              <div class="inner">
                <h3><sup style="font-size: 20px">R$</sup> <?= number_format($sumFrete, 2, ',', '.') ?></h3>
                <p>Total em Frete</p>
              </div>
              <div class="icon"><i class="fas fa-truck"></i></div>
            </div>
          </div>
        </div>

        <!-- Charts -->
        <div class="row">
          <div class="col-md-12">
            <div class="card card-info">
              <div class="card-header">
                <h3 class="card-title">Sacas por Origem</h3>
              </div>
              <div class="card-body">
                <div id="sacaChart" style="min-height: 250px; height: 250px; margin: 0 auto"></div>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="card card-success">
              <div class="card-header">
                <h3 class="card-title">Compras (R$) por Origem</h3>
              </div>
              <div class="card-body">
                <div id="realChart" style="min-height: 250px; height: 250px; margin: 0 auto"></div>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="card card-warning">
              <div class="card-header">
                <h3 class="card-title">Gasolina (R$) por Origem</h3>
              </div>
              <div class="card-body">
                <div id="gasolinaChart" style="min-height: 250px; height: 250px; margin: 0 auto"></div>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="card card-danger">
              <div class="card-header">
                <h3 class="card-title">Frete (R$) por Origem</h3>
              </div>
              <div class="card-body">
                <div id="freteChart" style="min-height: 250px; height: 250px; margin: 0 auto"></div>
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
    const baseOptions = { responsive: true, maintainAspectRatio: false };

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

    createChart('sacaChart', 'Qtd. Sacas', <?= json_encode($chartDataSaca) ?>, 'rgba(23, 162, 184, 0.8)', 'Total de sacas de açaí por aldeia', 'Ano: 2026');
    createChart('realChart', 'Compras (R$)', <?= json_encode($chartDataReal) ?>, 'rgba(40, 167, 69, 0.8)', 'Total de compras de açaí por aldeia', 'Ano: 2026');
    createChart('gasolinaChart', 'Gasolina (R$)', <?= json_encode($chartDataGasolina) ?>, 'rgba(255, 193, 7, 0.8)', 'Total de combustível por aldeia', 'Ano: 2026');
    createChart('freteChart', 'Frete (R$)', <?= json_encode($chartDataFrete) ?>, 'rgba(220, 53, 69, 0.8)', 'Total gasto com frete por aldeia', 'Ano: 2026');
  })
</script>
</body>
</html>
