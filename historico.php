<?php
require 'db_connect.php';
$pageTitle = 'Histórico de Movimentações';
include 'includes/header.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$timeline = [];
$veiculo = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM veiculos WHERE id = ?');
    $stmt->execute([$id]);
    $veiculo = $stmt->fetch();
}

if ($veiculo) {
    $stmt = $pdo->prepare('SELECT * FROM status_log WHERE veiculo_id = ? ORDER BY data_hora DESC');
    $stmt->execute([$id]);
    $timeline = $stmt->fetchAll();
} else {
    $stmt = $pdo->query('SELECT sl.*, v.placa, v.processo FROM status_log sl JOIN veiculos v ON v.id = sl.veiculo_id ORDER BY sl.data_hora DESC');
    $timeline = $stmt->fetchAll();
}

function formatDateTime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}
?>
<section class="page-section history-section">
    <div class="panel-card history-card">
        <div class="panel-header">
            <div>
                <h3 class="panel-title"><i class="fas fa-clock-rotate-left"></i> Histórico <?php echo $veiculo ? 'do veículo ' . htmlspecialchars($veiculo['placa'], ENT_QUOTES, 'UTF-8') : ''; ?></h3>
                <p>Registros completos de status com data e hora de todas as movimentações.</p>
            </div>
            <div>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
            </div>
        </div>
        <div class="timeline">
            <?php if (empty($timeline)): ?>
                <div class="empty-state"><i class="fas fa-folder-open"></i> Nenhum registro de histórico encontrado.</div>
            <?php endif; ?>
            <?php foreach ($timeline as $item): ?>
                <article class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <span class="timeline-date"><i class="fas fa-calendar-day"></i> <?php echo formatDateTime($item['data_hora']); ?></span>
                        <h4><?php echo htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8'); ?></h4>
                        <p>Veículo: <strong><?php echo htmlspecialchars($item['placa'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong></p>
                        <?php if (!$veiculo): ?>
                            <p>Processo: <?php echo htmlspecialchars($item['processo'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
