<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/utils.php';

global $pdo;
if (!isset($pdo) && isset($GLOBALS['pdo'])) {
    $pdo = $GLOBALS['pdo'];
}

requireLogin();

// 1. Definições canônicas das 29 aldeias
$aldeiasDef = [
    'Açaizal' => ['Açaizal', 'Acaizal'],
    'Açaizal-Igarapé' => ['Açaizal-Igarapé', 'Acaizal-Igarape', 'Açaizal Igarapé'],
    'Ahumã' => ['Ahumã', 'Ahuma'],
    'Anawerá' => ['Anawerá', 'Anawera'],
    'Ariramba' => ['Ariramba'],
    'Curipi' => ['Curipi'],
    'Encruzo' => ['Encruzo'],
    'Estrela' => ['Estrela'],
    'Flamã' => ['Flamã', 'Flama'],
    'Galibi' => ['Galibi'],
    'Japiim' => ['Japiim', 'Japim', 'Jápim'],
    'Kamuywa' => ['Kamuywa', 'Kamuiwa'],
    'Kariá' => ['Kariá', 'Karia', 'Cariá'],
    'Karibuen' => ['Karibuen', 'Karibueno'],
    'Kuahí' => ['Kuahí', 'Kuahi'],
    'Kumarumã' => ['Kumarumã', 'Kumarumâ', 'Kumaruma'],
    'Kumenê' => ['Kumenê', 'Kumene'],
    'Kunanã' => ['Kunanã', 'Kunana'],
    'Manga' => ['Manga'],
    'Mangue' => ['Mangue'],
    'Piquiá' => ['Piquiá', 'Piquia'],
    'Samaúma' => ['Samaúma', 'Samauma'],
    'Santa Isabel' => ['Santa Isabel', 'Santa Izabel'],
    'Tukay' => ['Tukay', 'Tucai', 'Tukai'],
    'Tuluhi' => ['Tuluhi'],
    'Uahá' => ['Uahá', 'Uaha'],
    'Ykawakun' => ['Ykawakun', 'Icavacum', 'Ykawakum'],
    'Ywawka' => ['Ywawka', 'Ywaká', 'Ywaka'],
    'Zacarias' => ['Zacarias']
];

$nameNormalize = [];
foreach ($aldeiasDef as $canonical => $aliases) {
    foreach ($aliases as $alias) {
        $nameNormalize[mb_strtolower(trim($alias))] = $canonical;
    }
}

// 2. Parâmetros de Filtro
$filtroAno = isset($_GET['ano']) ? sanitizeInput($_GET['ano']) : 'Todos';
$filtroAldeia = isset($_GET['aldeia']) ? sanitizeInput($_GET['aldeia']) : '';
$filtroLocal = isset($_GET['local_coleta']) ? sanitizeInput($_GET['local_coleta']) : '';
$filtroBusca = isset($_GET['busca']) ? mb_strtolower(sanitizeInput($_GET['busca'])) : '';

$where = "WHERE 1=1";
$params = [];

if (!empty($filtroAno) && $filtroAno !== 'Todos') {
    $where .= " AND data_coleta LIKE ?";
    $params[] = $filtroAno . '%';
}

if (!empty($filtroAldeia) && $filtroAldeia !== 'Todas' && $filtroAldeia !== '') {
    $aliases = [$filtroAldeia];
    foreach ($aldeiasDef as $canName => $als) {
        if (mb_strtolower($canName) === mb_strtolower($filtroAldeia)) {
            $aliases = $als;
            break;
        }
    }
    $placeholders = implode(',', array_fill(0, count($aliases), '?'));
    $where .= " AND aldeia_ficha IN ({$placeholders})";
    foreach ($aliases as $al) {
        $params[] = $al;
    }
}

if (!empty($filtroLocal) && $filtroLocal !== 'Todos' && $filtroLocal !== '') {
    $where .= " AND local_coleta = ?";
    $params[] = $filtroLocal;
}

if (!empty($filtroBusca)) {
    $where .= " AND (aldeia_ficha LIKE ? OR coletor_resposavel LIKE ? OR fora_quem_comprou LIKE ? OR local_comprador LIKE ?)";
    $params[] = "%{$filtroBusca}%";
    $params[] = "%{$filtroBusca}%";
    $params[] = "%{$filtroBusca}%";
    $params[] = "%{$filtroBusca}%";
}

$stmt = $pdo->prepare("
    SELECT 
        id,
        data_coleta,
        aldeia_ficha,
        coletor_resposavel,
        qtd_ajudantes,
        qtd_familias,
        local_coleta,
        fora_qtd_saca,
        fora_preco_saca,
        fora_valor_total,
        fora_quem_comprou,
        local_comprador,
        consumo_qtd_saca,
        consumo_qtd_litros,
        venda_qtd_saca,
        venda_qtd_litros,
        venda_preco_litro,
        venda_valor_total,
        qtd_sacas_total
    FROM ficha_coleta
    {$where}
    ORDER BY data_coleta DESC, id DESC
");
$stmt->execute($params);
$fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Preço médio global para valor de consumo
$stmtAvgPrice = $pdo->query("SELECT AVG(venda_preco_litro) AS media_preco, SUM(venda_valor_total)/NULLIF(SUM(venda_qtd_litros),0) AS ponderado_preco FROM ficha_coleta WHERE venda_preco_litro > 0");
$avgPriceRow = $stmtAvgPrice->fetch(PDO::FETCH_ASSOC);
$globalPrecoMedioLitro = (float)($avgPriceRow['ponderado_preco'] ?? $avgPriceRow['media_preco'] ?? 5.00);
if ($globalPrecoMedioLitro <= 0) $globalPrecoMedioLitro = 5.00;

// Agrupamento por Aldeia para a 1ª Aba
$resumoAldeias = [];
foreach (array_keys($aldeiasDef) as $aldNome) {
    $resumoAldeias[$aldNome] = [
        'fichas' => 0,
        'sacas_total' => 0,
        'fora_sacas' => 0,
        'fora_valor' => 0.0,
        'consumo_sacas' => 0,
        'consumo_litros' => 0,
        'consumo_valor' => 0.0,
        'venda_sacas' => 0,
        'venda_litros' => 0,
        'venda_valor' => 0.0,
        'total_litros' => 0,
        'ajudantes' => 0,
        'familias' => 0,
        'manejo_sacas' => 0,
        'mata_sacas' => 0
    ];
}

foreach ($fichas as $f) {
    $rawAld = trim($f['aldeia_ficha'] ?? '');
    $norm = mb_strtolower($rawAld);
    $can = $nameNormalize[$norm] ?? $rawAld;

    if (!isset($resumoAldeias[$can])) {
        $resumoAldeias[$can] = [
            'fichas' => 0,
            'sacas_total' => 0,
            'fora_sacas' => 0,
            'fora_valor' => 0.0,
            'consumo_sacas' => 0,
            'consumo_litros' => 0,
            'consumo_valor' => 0.0,
            'venda_sacas' => 0,
            'venda_litros' => 0,
            'venda_valor' => 0.0,
            'total_litros' => 0,
            'ajudantes' => 0,
            'familias' => 0,
            'manejo_sacas' => 0,
            'mata_sacas' => 0
        ];
    }

    $st = (int)($f['qtd_sacas_total'] ?? 0);
    $sf = (int)($f['fora_qtd_saca'] ?? 0);
    $vf = (float)($f['fora_valor_total'] ?? 0);
    $sc = (int)($f['consumo_qtd_saca'] ?? 0);
    $lc = (int)($f['consumo_qtd_litros'] ?? 0);
    $sv = (int)($f['venda_qtd_saca'] ?? 0);
    $lv = (int)($f['venda_qtd_litros'] ?? 0);
    $vv = (float)($f['venda_valor_total'] ?? 0);
    $pv = (float)($f['venda_preco_litro'] ?? 0);
    $aj = (int)($f['qtd_ajudantes'] ?? 0);
    $fm = (int)($f['qtd_familias'] ?? 0);
    $loc = trim($f['local_coleta'] ?? '');

    if ($st <= 0) $st = $sf + $sc + $sv;
    $precoLitroCons = $pv > 0 ? $pv : $globalPrecoMedioLitro;
    $vConsEstimado = $lc * $precoLitroCons;

    $r = &$resumoAldeias[$can];
    $r['fichas']++;
    $r['sacas_total'] += $st;
    $r['fora_sacas'] += $sf;
    $r['fora_valor'] += $vf;
    $r['consumo_sacas'] += $sc;
    $r['consumo_litros'] += $lc;
    $r['consumo_valor'] += $vConsEstimado;
    $r['venda_sacas'] += $sv;
    $r['venda_litros'] += $lv;
    $r['venda_valor'] += $vv;
    $r['total_litros'] += ($lc + $lv);
    $r['ajudantes'] += $aj;
    $r['familias'] += $fm;

    if (mb_stripos($loc, 'manejo') !== false) {
        $r['manejo_sacas'] += $st;
    } else {
        $r['mata_sacas'] += $st;
    }
}
unset($r);

function xmlEscape($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

// Montagem do SpreadsheetML XML (.xls)
$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
$xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
$xml .= ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
$xml .= ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
$xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
$xml .= ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";

// Estilos
$xml .= ' <Styles>' . "\n";
$xml .= '  <Style ss:ID="Default" ss:Name="Normal">' . "\n";
$xml .= '   <Alignment ss:Vertical="Center"/>' . "\n";
$xml .= '   <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#000000"/>' . "\n";
$xml .= '  </Style>' . "\n";

$xml .= '  <Style ss:ID="Title">' . "\n";
$xml .= '   <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>' . "\n";
$xml .= '   <Font ss:FontName="Calibri" ss:Size="14" ss:Bold="1" ss:Color="#1B4D3E"/>' . "\n";
$xml .= '  </Style>' . "\n";

$xml .= '  <Style ss:ID="HeaderMain">' . "\n";
$xml .= '   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>' . "\n";
$xml .= '   <Borders>' . "\n";
$xml .= '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D4D4D4"/>' . "\n";
$xml .= '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D4D4D4"/>' . "\n";
$xml .= '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D4D4D4"/>' . "\n";
$xml .= '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D4D4D4"/>' . "\n";
$xml .= '   </Borders>' . "\n";
$xml .= '   <Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#FFFFFF"/>' . "\n";
$xml .= '   <Interior ss:Color="#1E392A" ss:Pattern="Solid"/>' . "\n";
$xml .= '  </Style>' . "\n";

$xml .= '  <Style ss:ID="HeaderSub">' . "\n";
$xml .= '   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>' . "\n";
$xml .= '   <Borders>' . "\n";
$xml .= '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D4D4D4"/>' . "\n";
$xml .= '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D4D4D4"/>' . "\n";
$xml .= '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D4D4D4"/>' . "\n";
$xml .= '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D4D4D4"/>' . "\n";
$xml .= '   </Borders>' . "\n";
$xml .= '   <Font ss:FontName="Calibri" ss:Size="10" ss:Bold="1" ss:Color="#FFFFFF"/>' . "\n";
$xml .= '   <Interior ss:Color="#2E6548" ss:Pattern="Solid"/>' . "\n";
$xml .= '  </Style>' . "\n";

$xml .= '  <Style ss:ID="RowEven">' . "\n";
$xml .= '   <Borders>' . "\n";
$xml .= '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E5E5"/>' . "\n";
$xml .= '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E5E5"/>' . "\n";
$xml .= '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E5E5"/>' . "\n";
$xml .= '   </Borders>' . "\n";
$xml .= '   <Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>' . "\n";
$xml .= '  </Style>' . "\n";

$xml .= '  <Style ss:ID="RowOdd">' . "\n";
$xml .= '   <Borders>' . "\n";
$xml .= '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E5E5"/>' . "\n";
$xml .= '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E5E5"/>' . "\n";
$xml .= '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E5E5"/>' . "\n";
$xml .= '   </Borders>' . "\n";
$xml .= '   <Interior ss:Color="#F4F8F5" ss:Pattern="Solid"/>' . "\n";
$xml .= '  </Style>' . "\n";

$xml .= '  <Style ss:ID="TotalStyle">' . "\n";
$xml .= '   <Borders>' . "\n";
$xml .= '    <Border ss:Position="Top" ss:LineStyle="Double" ss:Weight="3" ss:Color="#1E392A"/>' . "\n";
$xml .= '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#1E392A"/>' . "\n";
$xml .= '   </Borders>' . "\n";
$xml .= '   <Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#000000"/>' . "\n";
$xml .= '   <Interior ss:Color="#D1E7DD" ss:Pattern="Solid"/>' . "\n";
$xml .= '  </Style>' . "\n";

$xml .= '  <Style ss:ID="CurrencyCell">' . "\n";
$xml .= '   <NumberFormat ss:Format="&quot;R$&quot;\ #,##0.00"/>' . "\n";
$xml .= '  </Style>' . "\n";

$xml .= ' </Styles>' . "\n";

// =========================================================================
// 1ª ABA: RESUMO CONSOLIDADO POR ALDEIA
// =========================================================================
$xml .= ' <Worksheet ss:Name="Resumo por Aldeia">' . "\n";
$xml .= '  <Table ss:DefaultRowHeight="19">' . "\n";
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="140"/>' . "\n"; // Aldeia
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="80"/>' . "\n";  // Fichas
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="100"/>' . "\n"; // Total Sacas
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="95"/>' . "\n";  // Fora Sacas
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="110"/>' . "\n"; // Fora R$
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="95"/>' . "\n";  // Consumo Sacas
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="100"/>' . "\n"; // Consumo Litros
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="120"/>' . "\n"; // Consumo Valor Est.
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="95"/>' . "\n";  // Venda Sacas
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="100"/>' . "\n"; // Venda Litros
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="110"/>' . "\n"; // Venda R$
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="125"/>' . "\n"; // Receita Total (R$)
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="85"/>' . "\n";  // Ajudantes
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="85"/>' . "\n";  // Famílias
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="100"/>' . "\n"; // Sacas Manejo
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="100"/>' . "\n"; // Sacas Mata

// Cabeçalho Principal
$xml .= '   <Row ss:Height="26" ss:StyleID="HeaderMain">' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Aldeia</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Fichas</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Total Sacas</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Venda Fora (sc)</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Venda Fora (R$)</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Consumo (sc)</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Consumo (L)</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Val. Consumo Est. (R$)</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Venda Aldeia (sc)</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Venda Aldeia (L)</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Venda Aldeia (R$)</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Receita Total (R$)</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Ajudantes</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Famílias</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Área Manejo (sc)</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Área Mata (sc)</Data></Cell>' . "\n";
$xml .= '   </Row>' . "\n";

// Dados
$totFichas = 0;
$totSacas = 0;
$totForaSacas = 0;
$totForaValor = 0.0;
$totConsSacas = 0;
$totConsLitros = 0;
$totConsValor = 0.0;
$totVendaSacas = 0;
$totVendaLitros = 0;
$totVendaValor = 0.0;
$totReceitaTotal = 0.0;
$totAjud = 0;
$totFam = 0;
$totManejoSacas = 0;
$totMataSacas = 0;

$rowIdx = 0;
ksort($resumoAldeias);
foreach ($resumoAldeias as $aldNome => $r) {
    if ($r['sacas_total'] === 0 && !empty($filtroAldeia) && $filtroAldeia !== 'Todas' && $filtroAldeia !== $aldNome) {
        continue;
    }

    $style = ($rowIdx % 2 === 0) ? 'RowEven' : 'RowOdd';
    $receitaAld = $r['fora_valor'] + $r['venda_valor'];

    $xml .= '   <Row ss:Height="20" ss:StyleID="' . $style . '">' . "\n";
    $xml .= '    <Cell><Data ss:Type="String">' . xmlEscape($aldNome) . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="Number">' . $r['fichas'] . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="Number">' . $r['sacas_total'] . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="Number">' . $r['fora_sacas'] . '</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="CurrencyCell"><Data ss:Type="Number">' . round($r['fora_valor'], 2) . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="Number">' . $r['consumo_sacas'] . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="Number">' . $r['consumo_litros'] . '</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="CurrencyCell"><Data ss:Type="Number">' . round($r['consumo_valor'], 2) . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="Number">' . $r['venda_sacas'] . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="Number">' . $r['venda_litros'] . '</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="CurrencyCell"><Data ss:Type="Number">' . round($r['venda_valor'], 2) . '</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="CurrencyCell"><Data ss:Type="Number">' . round($receitaAld, 2) . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="Number">' . $r['ajudantes'] . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="Number">' . $r['familias'] . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="Number">' . $r['manejo_sacas'] . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="Number">' . $r['mata_sacas'] . '</Data></Cell>' . "\n";
    $xml .= '   </Row>' . "\n";

    $totFichas += $r['fichas'];
    $totSacas += $r['sacas_total'];
    $totForaSacas += $r['fora_sacas'];
    $totForaValor += $r['fora_valor'];
    $totConsSacas += $r['consumo_sacas'];
    $totConsLitros += $r['consumo_litros'];
    $totConsValor += $r['consumo_valor'];
    $totVendaSacas += $r['venda_sacas'];
    $totVendaLitros += $r['venda_litros'];
    $totVendaValor += $r['venda_valor'];
    $totReceitaTotal += $receitaAld;
    $totAjud += $r['ajudantes'];
    $totFam += $r['familias'];
    $totManejoSacas += $r['manejo_sacas'];
    $totMataSacas += $r['mata_sacas'];
    $rowIdx++;
}

// Linha de Total Geral
$xml .= '   <Row ss:Height="23" ss:StyleID="TotalStyle">' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Total Geral</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="Number">' . $totFichas . '</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="Number">' . $totSacas . '</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="Number">' . $totForaSacas . '</Data></Cell>' . "\n";
$xml .= '    <Cell ss:StyleID="CurrencyCell"><Data ss:Type="Number">' . round($totForaValor, 2) . '</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="Number">' . $totConsSacas . '</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="Number">' . $totConsLitros . '</Data></Cell>' . "\n";
$xml .= '    <Cell ss:StyleID="CurrencyCell"><Data ss:Type="Number">' . round($totConsValor, 2) . '</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="Number">' . $totVendaSacas . '</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="Number">' . $totVendaLitros . '</Data></Cell>' . "\n";
$xml .= '    <Cell ss:StyleID="CurrencyCell"><Data ss:Type="Number">' . round($totVendaValor, 2) . '</Data></Cell>' . "\n";
$xml .= '    <Cell ss:StyleID="CurrencyCell"><Data ss:Type="Number">' . round($totReceitaTotal, 2) . '</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="Number">' . $totAjud . '</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="Number">' . $totFam . '</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="Number">' . $totManejoSacas . '</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="Number">' . $totMataSacas . '</Data></Cell>' . "\n";
$xml .= '   </Row>' . "\n";

$xml .= '  </Table>' . "\n";
$xml .= ' </Worksheet>' . "\n";

// =========================================================================
// 2ª ABA: FICHAS DE COLETA DETALHADAS (REGISTRO A REGISTRO)
// =========================================================================
$xml .= ' <Worksheet ss:Name="Fichas Detalhadas">' . "\n";
$xml .= '  <Table ss:DefaultRowHeight="19">' . "\n";
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="50"/>' . "\n";  // ID
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="90"/>' . "\n";  // Data
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="130"/>' . "\n"; // Aldeia
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="160"/>' . "\n"; // Coletor
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="120"/>' . "\n"; // Local Coleta
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="80"/>' . "\n";  // Ajudantes
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="80"/>' . "\n";  // Famílias
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="90"/>' . "\n";  // Total Sacas
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="90"/>' . "\n";  // Fora Sacas
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="95"/>' . "\n";  // Fora Preço/Sc
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="110"/>' . "\n"; // Fora Valor Total
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="140"/>' . "\n"; // Quem Comprou
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="130"/>' . "\n"; // Local Comprador
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="90"/>' . "\n";  // Consumo Sacas
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="95"/>' . "\n";  // Consumo Litros
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="90"/>' . "\n";  // Venda Sacas
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="95"/>' . "\n";  // Venda Litros
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="95"/>' . "\n";  // Venda Preço/L
$xml .= '   <Column ss:AutoFitWidth="1" ss:Width="110"/>' . "\n"; // Venda Valor Total

$xml .= '   <Row ss:Height="24" ss:StyleID="HeaderMain">' . "\n";
$xml .= '    <Cell><Data ss:Type="String">ID</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Data Coleta</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Aldeia</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Coletor Responsável</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Local Coleta</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Ajudantes</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Famílias</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Total Sacas</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Fora Sacas</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Preço Saca (R$)</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Total Fora (R$)</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Quem Comprou</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Local Comprador</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Consumo Sacas</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Consumo Litros</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Venda Sacas</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Venda Litros</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Preço Litro (R$)</Data></Cell>' . "\n";
$xml .= '    <Cell><Data ss:Type="String">Total Venda (R$)</Data></Cell>' . "\n";
$xml .= '   </Row>' . "\n";

$rIdx = 0;
foreach ($fichas as $fc) {
    $style = ($rIdx % 2 === 0) ? 'RowEven' : 'RowOdd';
    $dataFmt = !empty($fc['data_coleta']) ? date('d/m/Y', strtotime($fc['data_coleta'])) : '';

    $xml .= '   <Row ss:Height="19" ss:StyleID="' . $style . '">' . "\n";
    $xml .= '    <Cell><Data ss:Type="Number">' . $fc['id'] . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="String">' . xmlEscape($dataFmt) . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="String">' . xmlEscape($fc['aldeia_ficha']) . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="String">' . xmlEscape($fc['coletor_resposavel']) . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="String">' . xmlEscape($fc['local_coleta']) . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="Number">' . (int)$fc['qtd_ajudantes'] . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="Number">' . (int)$fc['qtd_familias'] . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="Number">' . (int)$fc['qtd_sacas_total'] . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="Number">' . (int)$fc['fora_qtd_saca'] . '</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="CurrencyCell"><Data ss:Type="Number">' . round((float)$fc['fora_preco_saca'], 2) . '</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="CurrencyCell"><Data ss:Type="Number">' . round((float)$fc['fora_valor_total'], 2) . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="String">' . xmlEscape($fc['fora_quem_comprou']) . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="String">' . xmlEscape($fc['local_comprador']) . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="Number">' . (int)$fc['consumo_qtd_saca'] . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="Number">' . (int)$fc['consumo_qtd_litros'] . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="Number">' . (int)$fc['venda_qtd_saca'] . '</Data></Cell>' . "\n";
    $xml .= '    <Cell><Data ss:Type="Number">' . (int)$fc['venda_qtd_litros'] . '</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="CurrencyCell"><Data ss:Type="Number">' . round((float)$fc['venda_preco_litro'], 2) . '</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="CurrencyCell"><Data ss:Type="Number">' . round((float)$fc['venda_valor_total'], 2) . '</Data></Cell>' . "\n";
    $xml .= '   </Row>' . "\n";
    $rIdx++;
}

$xml .= '  </Table>' . "\n";
$xml .= ' </Worksheet>' . "\n";

$xml .= '</Workbook>' . "\n";

// Nome do arquivo
$sufixoAno = (!empty($filtroAno) && $filtroAno !== 'Todos') ? $filtroAno : 'TodosAnos';
$sufixoAldeia = (!empty($filtroAldeia) && $filtroAldeia !== 'Todas') ? preg_replace('/[^a-zA-Z0-9_-]/', '', $filtroAldeia) : 'TodasAldeias';
$filename = "Relatorio_Fichas_Coleta_UASEI_{$sufixoAldeia}_{$sufixoAno}_" . date('Ymd') . ".xls";

header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

echo $xml;
exit;
