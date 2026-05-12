<?php
require 'db_connect.php';
$pageTitle = 'Dashboard Administrativo';
include 'includes/header.php';

function sanitize($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function matchText($value, array $needles) {
    $value = strtolower((string)$value);
    foreach ($needles as $needle) {
        if (strpos($value, strtolower($needle)) !== false) {
            return true;
        }
    }
    return false;
}

function formatDateTime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}

$statusCounts = [];
$totalsQuery = 'SELECT status, COUNT(*) AS total FROM ('
    . 'SELECT veiculo_id, status FROM status_log '
    . 'WHERE (veiculo_id, data_hora) IN (SELECT veiculo_id, MAX(data_hora) FROM status_log GROUP BY veiculo_id)
) last_status GROUP BY status';
$stmt = $pdo->query($totalsQuery);
foreach ($stmt->fetchAll() as $row) {
    $statusCounts[$row['status']] = (int)$row['total'];
}

$veiculoQuery = 'SELECT v.*, s.status AS status_atual, s.data_hora AS status_data FROM veiculos v '
    . 'JOIN (SELECT veiculo_id, status, data_hora FROM status_log WHERE (veiculo_id, data_hora) IN '
    . '(SELECT veiculo_id, MAX(data_hora) FROM status_log GROUP BY veiculo_id)) s ON v.id = s.veiculo_id';
$stmt = $pdo->query($veiculoQuery);
$veiculos = $stmt->fetchAll();

$totalVeiculos = count($veiculos);
$totalAtrasados = 0;
$totalEmAndamento = 0;
$totalEntregues = 0;
$processSummary = [
    'Colisão' => 0,
    'Colisão com terceiro' => 0,
    'Em Compras' => 0,
    'Roubo/Furto' => 0,
    'Apropriação Indébita' => 0,
];
$atrasadosList = [];
$dashboardGroups = [
    'colisao' => [
        'label' => 'Colisao',
        'note' => 'Processos de colisao',
        'icon' => 'fas fa-car-burst',
        'class' => 'blue-glow',
        'items' => [],
    ],
    'roubo_furto' => [
        'label' => 'Roubo/Furto',
        'note' => 'Roubo e furto juntos',
        'icon' => 'fas fa-shield-halved',
        'class' => 'red-glow',
        'items' => [],
    ],
    'apropriacao' => [
        'label' => 'Apropriacao Indebita',
        'note' => 'Apropriacao separada',
        'icon' => 'fas fa-user-lock',
        'class' => 'purple-glow',
        'items' => [],
    ],
    'oficina' => [
        'label' => 'Em Oficina',
        'note' => 'Status atual em oficina',
        'icon' => 'fas fa-screwdriver-wrench',
        'class' => 'orange-glow',
        'items' => [],
    ],
    'entregues' => [
        'label' => 'Entregue/Finalizada',
        'note' => 'Veiculos finalizados',
        'icon' => 'fas fa-circle-check',
        'class' => 'green-glow',
        'items' => [],
    ],
];

foreach ($veiculos as $veiculo) {
    $processoText = $veiculo['processo'];
    $statusText = $veiculo['status_atual'];
    $dataAcionamento = new DateTime($veiculo['data_acionamento']);
    $hoje = new DateTime();
    $diasDecorridos = max(0, $hoje->diff($dataAcionamento)->days);
    $prazo = in_array($veiculo['processo'], ['Roubo/Furto', 'Apropriação Indébita']) ? 90 : 45;
    $diasRestantes = $prazo - $diasDecorridos;
    if ($diasRestantes < 0) {
        $totalAtrasados++;
        $atrasadosList[] = ['placa' => $veiculo['placa'], 'processo' => $veiculo['processo'], 'dias' => abs($diasRestantes), 'status' => $veiculo['status_atual']];
    }
    if ($veiculo['status_atual'] !== 'Entregue') {
        $totalEmAndamento++;
    } else {
        $totalEntregues++;
    }
    if (isset($processSummary[$veiculo['processo']])) {
        $processSummary[$veiculo['processo']]++;
    }
    if (matchText($processoText, ['colis'])) {
        $dashboardGroups['colisao']['items'][] = $veiculo;
    }
    if (matchText($processoText, ['roubo', 'furto'])) {
        $dashboardGroups['roubo_furto']['items'][] = $veiculo;
    }
    if (matchText($processoText, ['apropria'])) {
        $dashboardGroups['apropriacao']['items'][] = $veiculo;
    }
    if (matchText($statusText, ['oficina'])) {
        $dashboardGroups['oficina']['items'][] = $veiculo;
    }
    if (matchText($statusText, ['entregue', 'finalizada', 'finalizado', 'recuperada'])) {
        $dashboardGroups['entregues']['items'][] = $veiculo;
    }
}

usort($atrasadosList, function ($a, $b) {
    return $b['dias'] <=> $a['dias'];
});

$percentualEntregues = $totalVeiculos > 0 ? round(($totalEntregues / $totalVeiculos) * 100) : 0;
$percentualAtrasados = $totalVeiculos > 0 ? round(($totalAtrasados / $totalVeiculos) * 100) : 0;
?>
<section class="page-section">
    <div class="dashboard-hero">
        <div>
            <h2><i class="fas fa-gauge-high"></i> Visão geral dos sinistros</h2>
            <p>Acompanhe prazos, gargalos e entregas em uma tela mais limpa e objetiva.</p>
        </div>
    </div>

    <div class="dashboard-grid">
        <?php foreach ($dashboardGroups as $groupKey => $group): ?>
            <button type="button" class="stat-card dashboard-list-btn <?php echo $group['class']; ?>" data-target="dashboardList-<?php echo $groupKey; ?>">
                <div>
                    <p><?php echo sanitize($group['label']); ?></p>
                    <h2><?php echo count($group['items']); ?></h2>
                    <span class="stat-note"><?php echo sanitize($group['note']); ?></span>
                </div>
                <div class="stat-icon"><i class="<?php echo sanitize($group['icon']); ?>"></i></div>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="dashboard-panel">
        <div class="panel-card chart-card">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title"><i class="fas fa-chart-pie"></i> Resumo por Status</h3>
                    <p>Distribuição dos veículos pelo último status registrado.</p>
                </div>
                <span class="badge info"><i class="fas fa-rotate"></i> Atualizado</span>
            </div>
            <div class="chart-shell">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <div class="panel-card list-card">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title"><i class="fas fa-clock"></i> Veículos Atrasados</h3>
                    <p>Itens que precisam de atenção primeiro.</p>
                </div>
                <span class="badge danger"><i class="fas fa-fire"></i> Críticos</span>
            </div>
            <ul class="delay-list">
                <?php if (empty($atrasadosList)): ?>
                    <li class="empty-state"><i class="fas fa-circle-check"></i> Nenhum veículo em atraso no momento.</li>
                <?php endif; ?>
                <?php foreach (array_slice($atrasadosList, 0, 5) as $item): ?>
                    <li>
                        <div>
                            <strong><?php echo sanitize($item['placa']); ?></strong>
                            <span><?php echo sanitize($item['processo']); ?> | <?php echo sanitize($item['status']); ?></span>
                        </div>
                        <span class="badge danger"><i class="fas fa-hourglass-end"></i> -<?php echo $item['dias']; ?> dias</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="dashboard-panel">
        <div class="panel-card summary-card">
            <div class="panel-header">
                <h3 class="panel-title"><i class="fas fa-folder-open"></i> Resumo por Processo</h3>
            </div>
            <div class="process-summary">
                <?php foreach ($processSummary as $processo => $valor): ?>
                    <div class="process-item">
                        <span><i class="fas fa-file-shield"></i> <?php echo sanitize($processo); ?></span>
                        <strong><?php echo $valor; ?> veículos</strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="panel-card numbers-card">
            <div class="panel-header">
                <h3 class="panel-title"><i class="fas fa-list-check"></i> Status atuais</h3>
            </div>
            <div class="numbers-grid">
                <?php foreach ($statusCounts as $status => $count): ?>
                    <div class="number-block">
                        <span><i class="fas fa-circle-dot"></i> <?php echo sanitize($status); ?></span>
                        <strong><?php echo $count; ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<div class="modal-overlay" id="dashboardListModal" aria-hidden="true">
    <div class="modal-window dashboard-list-modal" role="dialog" aria-modal="true" aria-labelledby="dashboardListTitle">
        <button type="button" class="modal-close" data-close="dashboardListModal" title="Fechar"><i class="fas fa-times"></i></button>
        <div class="modal-header register-header">
            <span class="modal-badge"><i class="fas fa-list"></i></span>
            <div>
                <h2 id="dashboardListTitle">Lista do dashboard</h2>
                <p>Veiculos encontrados para o indicador selecionado.</p>
            </div>
        </div>

        <?php foreach ($dashboardGroups as $groupKey => $group): ?>
            <div class="dashboard-list-content" id="dashboardList-<?php echo $groupKey; ?>" data-title="<?php echo sanitize($group['label']); ?>" hidden>
                <?php if (empty($group['items'])): ?>
                    <div class="empty-state">Nenhum veiculo encontrado neste indicador.</div>
                <?php else: ?>
                    <div class="dashboard-vehicle-list">
                        <?php foreach ($group['items'] as $item): ?>
                            <article class="dashboard-vehicle-item">
                                <div>
                                    <strong><?php echo sanitize($item['placa']); ?></strong>
                                    <span><?php echo sanitize($item['proprietario']); ?></span>
                                </div>
                                <div>
                                    <span><?php echo sanitize($item['processo']); ?></span>
                                    <small><?php echo sanitize($item['status_atual']); ?> | <?php echo formatDateTime($item['status_data']); ?></small>
                                </div>
                                <a href="historico.php?id=<?php echo (int)$item['id']; ?>" class="icon-btn history"><i class="fas fa-clock-rotate-left"></i> Historico</a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
const statusChartData = {
    labels: <?php echo json_encode(array_keys($statusCounts), JSON_UNESCAPED_UNICODE); ?>,
    datasets: [{
        data: <?php echo json_encode(array_values($statusCounts)); ?>,
        backgroundColor: ['#2563eb', '#0f766e', '#16a34a', '#7c3aed', '#0891b2', '#dc2626', '#f59e0b'],
        borderColor: '#ffffff',
        borderWidth: 3,
    }]
};
window.addEventListener('DOMContentLoaded', () => {
    const chartElement = document.getElementById('statusChart');
    if (!chartElement || typeof Chart === 'undefined') {
        return;
    }
    const ctx = chartElement.getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: statusChartData,
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#334155', boxWidth: 14, padding: 16 }
                }
            },
            cutout: '68%',
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
