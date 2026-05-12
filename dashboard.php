<?php
require 'db_connect.php';
$pageTitle = 'Dashboard Administrativo';
include 'includes/header.php';

function sanitize($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
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

$veiculoQuery = 'SELECT v.*, s.status AS status_atual FROM veiculos v '
    . 'JOIN (SELECT veiculo_id, status FROM status_log WHERE (veiculo_id, data_hora) IN '
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

foreach ($veiculos as $veiculo) {
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
        <a href="cadastrar.php" class="btn"><i class="fas fa-plus"></i> Novo veículo</a>
    </div>

    <div class="dashboard-grid">
        <article class="stat-card purple-glow">
            <div>
                <p>Total de Veículos</p>
                <h2><?php echo $totalVeiculos; ?></h2>
                <span class="stat-note">Registros cadastrados</span>
            </div>
            <div class="stat-icon"><i class="fas fa-car-side"></i></div>
        </article>
        <article class="stat-card red-glow">
            <div>
                <p>Atrasados</p>
                <h2><?php echo $totalAtrasados; ?></h2>
                <span class="stat-note"><?php echo $percentualAtrasados; ?>% da carteira</span>
            </div>
            <div class="stat-icon"><i class="fas fa-triangle-exclamation"></i></div>
        </article>
        <article class="stat-card blue-glow">
            <div>
                <p>Em Andamento</p>
                <h2><?php echo $totalEmAndamento; ?></h2>
                <span class="stat-note">Ainda não entregues</span>
            </div>
            <div class="stat-icon"><i class="fas fa-rotate"></i></div>
        </article>
        <article class="stat-card green-glow">
            <div>
                <p>Entregues</p>
                <h2><?php echo $totalEntregues; ?></h2>
                <span class="stat-note"><?php echo $percentualEntregues; ?>% concluídos</span>
            </div>
            <div class="stat-icon"><i class="fas fa-circle-check"></i></div>
        </article>
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
