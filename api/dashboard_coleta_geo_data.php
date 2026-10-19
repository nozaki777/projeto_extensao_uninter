<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/utils.php';

global $pdo;
if (!isset($pdo) && isset($GLOBALS['pdo'])) {
    $pdo = $GLOBALS['pdo'];
}

requireLoginApi();

header('Content-Type: application/json; charset=utf-8');

try {
    // 1. Definição canônica das 29 Aldeias de Oiapoque / AP com georreferenciamento (SIRGAS 2000 / IBGE)
    $aldeiasDef = [
        [
            'num' => 1,
            'nome' => 'Açaizal',
            'aliases' => ['Açaizal', 'Acaizal'],
            'ti' => 'TI Uaçá',
            'regiao' => 'Rio Curipi',
            'lat' => 3.8375,
            'lng' => -51.4097,
            'color' => '#6f42c1'
        ],
        [
            'num' => 2,
            'nome' => 'Açaizal-Igarapé',
            'aliases' => ['Açaizal-Igarapé', 'Acaizal-Igarape', 'Açaizal Igarapé'],
            'ti' => 'TI Uaçá',
            'regiao' => 'Rio Curipi',
            'lat' => 3.8306,
            'lng' => -51.4167,
            'color' => '#6f42c1'
        ],
        [
            'num' => 3,
            'nome' => 'Ahumã',
            'aliases' => ['Ahumã', 'Ahuma'],
            'ti' => 'TI Juminá',
            'regiao' => 'Rodovia BR-156',
            'lat' => 3.6120,
            'lng' => -51.6540,
            'color' => '#17a2b8'
        ],
        [
            'num' => 4,
            'nome' => 'Anawerá',
            'aliases' => ['Anawerá', 'Anawera'],
            'ti' => 'TI Uaçá',
            'regiao' => 'Rodovia BR-156 Sul',
            'lat' => 2.9278,
            'lng' => -51.2806,
            'color' => '#28a745'
        ],
        [
            'num' => 5,
            'nome' => 'Ariramba',
            'aliases' => ['Ariramba'],
            'ti' => 'TI Galibi',
            'regiao' => 'Baixo Rio Oiapoque',
            'lat' => 3.9625,
            'lng' => -51.7222,
            'color' => '#fd7e14'
        ],
        [
            'num' => 6,
            'nome' => 'Curipi',
            'aliases' => ['Curipi'],
            'ti' => 'TI Uaçá',
            'regiao' => 'Rio Curipi',
            'lat' => 3.5222,
            'lng' => -51.6625,
            'color' => '#6f42c1'
        ],
        [
            'num' => 7,
            'nome' => 'Encruzo',
            'aliases' => ['Encruzo'],
            'ti' => 'TI Uaçá',
            'regiao' => 'Rio Curipi / BR-156',
            'lat' => 3.5650,
            'lng' => -51.6420,
            'color' => '#6f42c1'
        ],
        [
            'num' => 8,
            'nome' => 'Estrela',
            'aliases' => ['Estrela'],
            'ti' => 'TI Uaçá',
            'regiao' => 'Rodovia BR-156',
            'lat' => 3.3986,
            'lng' => -51.5444,
            'color' => '#17a2b8'
        ],
        [
            'num' => 9,
            'nome' => 'Flamã',
            'aliases' => ['Flamã', 'Flama'],
            'ti' => 'TI Uaçá',
            'regiao' => 'Rio Uaçá',
            'lat' => 3.1750,
            'lng' => -51.1778,
            'color' => '#20c997'
        ],
        [
            'num' => 10,
            'nome' => 'Galibi',
            'aliases' => ['Galibi'],
            'ti' => 'TI Galibi',
            'regiao' => 'Rio Oiapoque',
            'lat' => 3.9028,
            'lng' => -51.7875,
            'color' => '#fd7e14'
        ],
        [
            'num' => 11,
            'nome' => 'Japiim',
            'aliases' => ['Japiim', 'Japim', 'Jápim'],
            'ti' => 'TI Uaçá',
            'regiao' => 'Rio Curipi',
            'lat' => 3.6306,
            'lng' => -51.5792,
            'color' => '#6f42c1'
        ],
        [
            'num' => 12,
            'nome' => 'Kamuywa',
            'aliases' => ['Kamuywa', 'Kamuiwa'],
            'ti' => 'TI Uaçá',
            'regiao' => 'Rio Urucawá',
            'lat' => 3.4833,
            'lng' => -51.5083,
            'color' => '#e83e8c'
        ],
        [
            'num' => 13,
            'nome' => 'Kariá',
            'aliases' => ['Kariá', 'Karia', 'Cariá'],
            'ti' => 'TI Juminá',
            'regiao' => 'Rodovia BR-156',
            'lat' => 3.4625,
            'lng' => -51.6194,
            'color' => '#17a2b8'
        ],
        [
            'num' => 14,
            'nome' => 'Karibuen',
            'aliases' => ['Karibuen', 'Karibueno'],
            'ti' => 'TI Uaçá',
            'regiao' => 'Rio Uaçá',
            'lat' => 3.3278,
            'lng' => -51.2222,
            'color' => '#20c997'
        ],
        [
            'num' => 15,
            'nome' => 'Kuahí',
            'aliases' => ['Kuahí', 'Kuahi'],
            'ti' => 'TI Uaçá',
            'regiao' => 'Rio Urucawá',
            'lat' => 3.4333,
            'lng' => -51.5200,
            'color' => '#e83e8c'
        ],
        [
            'num' => 16,
            'nome' => 'Kumarumã',
            'aliases' => ['Kumarumã', 'Kumarumâ', 'Kumaruma'],
            'ti' => 'TI Uaçá',
            'regiao' => 'Rio Uaçá',
            'lat' => 3.3786,
            'lng' => -51.2972,
            'color' => '#20c997'
        ],
        [
            'num' => 17,
            'nome' => 'Kumenê',
            'aliases' => ['Kumenê', 'Kumene'],
            'ti' => 'TI Uaçá',
            'regiao' => 'Rio Urucawá',
            'lat' => 3.4875,
            'lng' => -51.4858,
            'color' => '#e83e8c'
        ],
        [
            'num' => 18,
            'nome' => 'Kunanã',
            'aliases' => ['Kunanã', 'Kunana'],
            'ti' => 'TI Juminá',
            'regiao' => 'Baixo Rio Oiapoque',
            'lat' => 4.0189,
            'lng' => -51.6178,
            'color' => '#fd7e14'
        ],
        [
            'num' => 19,
            'nome' => 'Manga',
            'aliases' => ['Manga'],
            'ti' => 'TI Uaçá',
            'regiao' => 'Rio Curipi',
            'lat' => 3.7296163792243213,
            'lng' => -51.71975626215097,
            'color' => '#6f42c1'
        ],
        [
            'num' => 20,
            'nome' => 'Mangue',
            'aliases' => ['Mangue'],
            'ti' => 'TI Uaçá',
            'regiao' => 'Rio Curipi',
            'lat' => 3.4892,
            'lng' => -51.4719,
            'color' => '#6f42c1'
        ],
        [
            'num' => 21,
            'nome' => 'Piquiá',
            'aliases' => ['Piquiá', 'Piquia'],
            'ti' => 'TI Uaçá',
            'regiao' => 'Rio Curipi',
            'lat' => 3.6042,
            'lng' => -51.6756,
            'color' => '#6f42c1'
        ],
        [
            'num' => 22,
            'nome' => 'Samaúma',
            'aliases' => ['Samaúma', 'Samauma'],
            'ti' => 'TI Uaçá',
            'regiao' => 'Rodovia BR-156',
            'lat' => 3.2889,
            'lng' => -51.4528,
            'color' => '#17a2b8'
        ],
        [
            'num' => 23,
            'nome' => 'Santa Isabel',
            'aliases' => ['Santa Isabel', 'Santa Izabel'],
            'ti' => 'TI Uaçá',
            'regiao' => 'Rio Curipi',
            'lat' => 3.7556,
            'lng' => -51.4583,
            'color' => '#6f42c1'
        ],
        [
            'num' => 24,
            'nome' => 'Tukay',
            'aliases' => ['Tukay', 'Tucai', 'Tukai'],
            'ti' => 'TI Uaçá',
            'regiao' => 'Rodovia BR-156',
            'lat' => 3.2178,
            'lng' => -51.5531,
            'color' => '#17a2b8'
        ],
        [
            'num' => 25,
            'nome' => 'Tuluhi',
            'aliases' => ['Tuluhi'],
            'ti' => 'TI Uaçá',
            'regiao' => 'Rio Urucawá',
            'lat' => 3.4167,
            'lng' => -51.5000,
            'color' => '#e83e8c'
        ],
        [
            'num' => 26,
            'nome' => 'Uahá',
            'aliases' => ['Uahá', 'Uaha'],
            'ti' => 'TI Galibi',
            'regiao' => 'Rio Oiapoque',
            'lat' => 3.9750,
            'lng' => -51.6972,
            'color' => '#fd7e14'
        ],
        [
            'num' => 27,
            'nome' => 'Ykawakun',
            'aliases' => ['Ykawakun', 'Icavacum', 'Ykawakum'],
            'ti' => 'TI Uaçá',
            'regiao' => 'Rio Urucawá',
            'lat' => 3.3694,
            'lng' => -51.4889,
            'color' => '#e83e8c'
        ],
        [
            'num' => 28,
            'nome' => 'Ywawka',
            'aliases' => ['Ywawka', 'Ywaká', 'Ywaka'],
            'ti' => 'TI Uaçá',
            'regiao' => 'Rodovia BR-156',
            'lat' => 3.3194,
            'lng' => -51.4583,
            'color' => '#17a2b8'
        ],
        [
            'num' => 29,
            'nome' => 'Zacarias',
            'aliases' => ['Zacarias'],
            'ti' => 'TI Galibi',
            'regiao' => 'Rio Oiapoque',
            'lat' => 3.9250,
            'lng' => -51.7500,
            'color' => '#fd7e14'
        ]
    ];

    // Mapeamento de normalização de nomes
    $nameNormalize = [];
    foreach ($aldeiasDef as $a) {
        foreach ($a['aliases'] as $alias) {
            $nameNormalize[mb_strtolower(trim($alias))] = $a['nome'];
        }
    }

    // 2. Parâmetros de Filtro
    $anoAtual = date('Y');
    $filtroAno = isset($_GET['ano']) ? sanitizeInput($_GET['ano']) : 'Todos';
    $filtroAldeia = isset($_GET['aldeia']) ? sanitizeInput($_GET['aldeia']) : '';
    $filtroLocal = isset($_GET['local_coleta']) ? sanitizeInput($_GET['local_coleta']) : '';
    $filtroBusca = isset($_GET['busca']) ? mb_strtolower(sanitizeInput($_GET['busca'])) : '';

    // Anos disponíveis na base para o select
    $stmtAnos = $pdo->query("SELECT DISTINCT SUBSTRING(data_coleta, 1, 4) AS ano FROM ficha_coleta WHERE data_coleta IS NOT NULL AND data_coleta != '' ORDER BY ano DESC");
    $availableYears = [];
    while ($rowA = $stmtAnos->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($rowA['ano'])) {
            $availableYears[] = $rowA['ano'];
        }
    }
    if (!in_array($anoAtual, $availableYears)) {
        array_unshift($availableYears, $anoAtual);
    }

    // Preço médio global de venda na aldeia (fallback para cálculo do valor de consumo)
    $stmtAvgPrice = $pdo->query("SELECT AVG(venda_preco_litro) AS media_preco, SUM(venda_valor_total)/NULLIF(SUM(venda_qtd_litros),0) AS ponderado_preco FROM ficha_coleta WHERE venda_preco_litro > 0");
    $avgPriceRow = $stmtAvgPrice->fetch(PDO::FETCH_ASSOC);
    $globalPrecoMedioLitro = (float)($avgPriceRow['ponderado_preco'] ?? $avgPriceRow['media_preco'] ?? 5.00);
    if ($globalPrecoMedioLitro <= 0) $globalPrecoMedioLitro = 5.00; // Valor de referência padrão

    // 3. Montar consulta principal de fichas de coleta com filtros
    $where = "WHERE 1=1";
    $params = [];

    if (!empty($filtroAno) && $filtroAno !== 'Todos') {
        $where .= " AND data_coleta LIKE ?";
        $params[] = $filtroAno . '%';
    }

    if (!empty($filtroAldeia) && $filtroAldeia !== 'Todas' && $filtroAldeia !== '') {
        // Encontrar aliases da aldeia
        $aliases = [$filtroAldeia];
        foreach ($aldeiasDef as $aldItem) {
            if (mb_strtolower($aldItem['nome']) === mb_strtolower($filtroAldeia)) {
                $aliases = $aldItem['aliases'];
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

    $sql = "
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
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Estruturação dos dados por Aldeia
    $aldeiasMap = [];
    foreach ($aldeiasDef as $ald) {
        $aldeiasMap[$ald['nome']] = [
            'num' => $ald['num'],
            'nome' => $ald['nome'],
            'ti' => $ald['ti'],
            'regiao' => $ald['regiao'],
            'lat' => $ald['lat'],
            'lng' => $ald['lng'],
            'color' => $ald['color'],
            'fichas_count' => 0,
            'total_sacas' => 0,
            'fora_sacas' => 0,
            'fora_valor' => 0.0,
            'consumo_sacas' => 0,
            'consumo_litros' => 0,
            'consumo_valor_estimado' => 0.0,
            'venda_sacas' => 0,
            'venda_litros' => 0,
            'venda_valor' => 0.0,
            'total_litros' => 0,
            'total_receita_bruta' => 0.0, // Fora + Venda Aldeia
            'total_valor_economico' => 0.0, // Fora + Venda Aldeia + Consumo Estimado
            'ajudantes' => 0,
            'familias' => 0,
            'local_manejo_sacas' => 0,
            'local_mata_sacas' => 0,
            'local_manejo_fichas' => 0,
            'local_mata_fichas' => 0,
            'precos_litro_venda' => [],
            'coletores' => [],
            'compradores' => []
        ];
    }

    // Totais Consolidados Gerais (Stats)
    $stats = [
        'total_fichas' => count($fichas),
        'total_sacas' => 0,
        'fora_sacas' => 0,
        'fora_valor' => 0.0,
        'consumo_sacas' => 0,
        'consumo_litros' => 0,
        'consumo_valor_estimado' => 0.0,
        'venda_sacas' => 0,
        'venda_litros' => 0,
        'venda_valor' => 0.0,
        'total_litros' => 0,
        'total_receita_bruta' => 0.0,
        'total_valor_economico' => 0.0,
        'ajudantes' => 0,
        'familias' => 0,
        'local_manejo_sacas' => 0,
        'local_mata_sacas' => 0,
        'local_manejo_fichas' => 0,
        'local_mata_fichas' => 0,
        'aldeias_ativas' => 0,
        'total_aldeias' => count($aldeiasDef),
        'preco_medio_litro' => $globalPrecoMedioLitro
    ];

    // Processamento de cada ficha de coleta
    foreach ($fichas as $f) {
        $rawAldeia = trim($f['aldeia_ficha'] ?? '');
        $normKey = mb_strtolower($rawAldeia);
        $canonicalName = $nameNormalize[$normKey] ?? $rawAldeia;

        if (!isset($aldeiasMap[$canonicalName])) {
            // Se for uma aldeia fora da lista predefinida, criar estrutura dinâmica
            $aldeiasMap[$canonicalName] = [
                'num' => count($aldeiasMap) + 1,
                'nome' => $canonicalName,
                'ti' => 'TI Oiapoque',
                'regiao' => 'Oiapoque / AP',
                'lat' => 3.8430,
                'lng' => -51.8340,
                'color' => '#6c757d',
                'fichas_count' => 0,
                'total_sacas' => 0,
                'fora_sacas' => 0,
                'fora_valor' => 0.0,
                'consumo_sacas' => 0,
                'consumo_litros' => 0,
                'consumo_valor_estimado' => 0.0,
                'venda_sacas' => 0,
                'venda_litros' => 0,
                'venda_valor' => 0.0,
                'total_litros' => 0,
                'total_receita_bruta' => 0.0,
                'total_valor_economico' => 0.0,
                'ajudantes' => 0,
                'familias' => 0,
                'local_manejo_sacas' => 0,
                'local_mata_sacas' => 0,
                'local_manejo_fichas' => 0,
                'local_mata_fichas' => 0,
                'precos_litro_venda' => [],
                'coletores' => [],
                'compradores' => []
            ];
        }

        $sTotal = (int)($f['qtd_sacas_total'] ?? 0);
        $sFora = (int)($f['fora_qtd_saca'] ?? 0);
        $vFora = (float)($f['fora_valor_total'] ?? 0);
        $sCons = (int)($f['consumo_qtd_saca'] ?? 0);
        $lCons = (int)($f['consumo_qtd_litros'] ?? 0);
        $sVenda = (int)($f['venda_qtd_saca'] ?? 0);
        $lVenda = (int)($f['venda_qtd_litros'] ?? 0);
        $vVenda = (float)($f['venda_valor_total'] ?? 0);
        $pLitroVenda = (float)($f['venda_preco_litro'] ?? 0);
        $ajud = (int)($f['qtd_ajudantes'] ?? 0);
        $fam = (int)($f['qtd_familias'] ?? 0);
        $local = trim($f['local_coleta'] ?? '');
        $coletor = trim($f['coletor_resposavel'] ?? '');
        $comprador = trim($f['fora_quem_comprou'] ?? '');

        // Recalcular sacas total se vier 0 ou divergente
        if ($sTotal <= 0 && ($sFora > 0 || $sCons > 0 || $sVenda > 0)) {
            $sTotal = $sFora + $sCons + $sVenda;
        }

        // Preço de venda a ser considerado para valor de consumo
        $precoLitroAplicavel = $pLitroVenda > 0 ? $pLitroVenda : $globalPrecoMedioLitro;
        $valorConsumoEstimado = $lCons * $precoLitroAplicavel;

        // Acumular na aldeia
        $a = &$aldeiasMap[$canonicalName];
        $a['fichas_count']++;
        $a['total_sacas'] += $sTotal;
        $a['fora_sacas'] += $sFora;
        $a['fora_valor'] += $vFora;
        $a['consumo_sacas'] += $sCons;
        $a['consumo_litros'] += $lCons;
        $a['consumo_valor_estimado'] += $valorConsumoEstimado;
        $a['venda_sacas'] += $sVenda;
        $a['venda_litros'] += $lVenda;
        $a['venda_valor'] += $vVenda;
        $a['total_litros'] += ($lCons + $lVenda);
        $a['total_receita_bruta'] += ($vFora + $vVenda);
        $a['total_valor_economico'] += ($vFora + $vVenda + $valorConsumoEstimado);
        $a['ajudantes'] += $ajud;
        $a['familias'] += $fam;

        if ($pLitroVenda > 0) {
            $a['precos_litro_venda'][] = $pLitroVenda;
        }

        if (mb_stripos($local, 'manejo') !== false) {
            $a['local_manejo_sacas'] += $sTotal;
            $a['local_manejo_fichas']++;
            $stats['local_manejo_sacas'] += $sTotal;
            $stats['local_manejo_fichas']++;
        } else {
            $a['local_mata_sacas'] += $sTotal;
            $a['local_mata_fichas']++;
            $stats['local_mata_sacas'] += $sTotal;
            $stats['local_mata_fichas']++;
        }

        if (!empty($coletor)) {
            $a['coletores'][$coletor] = ($a['coletores'][$coletor] ?? 0) + $sTotal;
        }
        if (!empty($comprador)) {
            $a['compradores'][$comprador] = ($a['compradores'][$comprador] ?? 0) + $sFora;
        }

        // Acumular nos Stats Globais
        $stats['total_sacas'] += $sTotal;
        $stats['fora_sacas'] += $sFora;
        $stats['fora_valor'] += $vFora;
        $stats['consumo_sacas'] += $sCons;
        $stats['consumo_litros'] += $lCons;
        $stats['consumo_valor_estimado'] += $valorConsumoEstimado;
        $stats['venda_sacas'] += $sVenda;
        $stats['venda_litros'] += $lVenda;
        $stats['venda_valor'] += $vVenda;
        $stats['total_litros'] += ($lCons + $lVenda);
        $stats['total_receita_bruta'] += ($vFora + $vVenda);
        $stats['total_valor_economico'] += ($vFora + $vVenda + $valorConsumoEstimado);
        $stats['ajudantes'] += $ajud;
        $stats['familias'] += $fam;
    }
    unset($a);

    // Contagem de aldeias com dados
    $aldeiasListFinal = [];
    $ativasCount = 0;
    foreach ($aldeiasMap as $aldData) {
        if ($aldData['total_sacas'] > 0 || $aldData['fichas_count'] > 0) {
            $ativasCount++;
        }

        // Ordenar coletores
        arsort($aldData['coletores']);
        arsort($aldData['compradores']);

        // Calcular preço médio específico da aldeia
        $pMedioAld = count($aldData['precos_litro_venda']) > 0
            ? (array_sum($aldData['precos_litro_venda']) / count($aldData['precos_litro_venda']))
            : $globalPrecoMedioLitro;
        $aldData['preco_medio_litro'] = round($pMedioAld, 2);

        $aldeiasListFinal[] = $aldData;
    }
    $stats['aldeias_ativas'] = $ativasCount;

    // 5. Preparação dos dados para os Gráficos Highcharts
    // Ordenar aldeias alfabeticamente para os gráficos
    usort($aldeiasListFinal, function($x, $y) {
        return strcmp($x['nome'], $y['nome']);
    });

    $chartAldeiasNames = [];
    $chartSacasFora = [];
    $chartSacasConsumo = [];
    $chartSacasVenda = [];
    $chartSacasTotal = [];
    $chartLitrosTotal = [];
    $chartValorReceita = [];
    $chartValorConsumo = [];
    $chartAjudantes = [];
    $chartFamilias = [];

    foreach ($aldeiasListFinal as $ald) {
        // Incluir apenas se houver registros ou se for filtro de aldeia específica
        if ($ald['total_sacas'] > 0 || !empty($filtroAldeia)) {
            $chartAldeiasNames[] = $ald['nome'];
            $chartSacasFora[] = ['name' => $ald['nome'], 'y' => $ald['fora_sacas']];
            $chartSacasConsumo[] = ['name' => $ald['nome'], 'y' => $ald['consumo_sacas']];
            $chartSacasVenda[] = ['name' => $ald['nome'], 'y' => $ald['venda_sacas']];
            $chartSacasTotal[] = ['name' => $ald['nome'], 'y' => $ald['total_sacas']];
            $chartLitrosTotal[] = ['name' => $ald['nome'], 'y' => $ald['total_litros']];
            $chartValorReceita[] = ['name' => $ald['nome'], 'y' => round($ald['total_receita_bruta'], 2)];
            $chartValorConsumo[] = ['name' => $ald['nome'], 'y' => round($ald['consumo_valor_estimado'], 2)];
            $chartAjudantes[] = ['name' => $ald['nome'], 'y' => $ald['ajudantes']];
            $chartFamilias[] = ['name' => $ald['nome'], 'y' => $ald['familias']];
        }
    }

    $destinacaoTotalPie = [
        ['name' => 'Venda Fora da TI', 'y' => $stats['fora_sacas'], 'color' => '#17a2b8'],
        ['name' => 'Consumo na Aldeia', 'y' => $stats['consumo_sacas'], 'color' => '#ffc107'],
        ['name' => 'Venda na Aldeia', 'y' => $stats['venda_sacas'], 'color' => '#dc3545']
    ];

    $locaisColetaPie = [
        ['name' => 'Área de Manejo', 'y' => $stats['local_manejo_sacas'], 'color' => '#28a745'],
        ['name' => 'Área de Mata', 'y' => $stats['local_mata_sacas'], 'color' => '#6c757d']
    ];

    $rankings = [
        'chart_aldeias_names' => $chartAldeiasNames,
        'chart_sacas_fora' => $chartSacasFora,
        'chart_sacas_consumo' => $chartSacasConsumo,
        'chart_sacas_venda' => $chartSacasVenda,
        'chart_sacas_total' => $chartSacasTotal,
        'chart_litros_total' => $chartLitrosTotal,
        'chart_valor_receita' => $chartValorReceita,
        'chart_valor_consumo' => $chartValorConsumo,
        'chart_ajudantes' => $chartAjudantes,
        'chart_familias' => $chartFamilias,
        'destinacao_total_pie' => $destinacaoTotalPie,
        'locais_coleta_pie' => $locaisColetaPie
    ];

    echo json_encode([
        'success' => true,
        'message' => 'Dados de coleta carregados com sucesso.',
        'data' => [
            'stats' => $stats,
            'aldeias' => $aldeiasListFinal,
            'rankings' => $rankings,
            'available_years' => $availableYears,
            'filter_ano' => $filtroAno,
            'filter_aldeia' => $filtroAldeia,
            'filter_local' => $filtroLocal,
            'filter_busca' => $filtroBusca
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    exit;

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar dados geográficos da coleta: ' . $e->getMessage()
    ]);
    exit;
}
