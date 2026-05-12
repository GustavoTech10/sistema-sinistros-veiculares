<?php
require 'db_connect.php';
$pageTitle = 'Veículos Cadastrados';
include 'includes/header.php';

function sanitize($value) {
    return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
}

$query = "SELECT v.*, s.status AS status_atual, s.data_hora AS status_data
          FROM veiculos v
          JOIN (
              SELECT veiculo_id, status, data_hora
              FROM status_log
              WHERE (veiculo_id, data_hora) IN (
                  SELECT veiculo_id, MAX(data_hora) FROM status_log GROUP BY veiculo_id
              )
          ) s ON v.id = s.veiculo_id
          ORDER BY s.data_hora DESC, v.id DESC";
$stmt = $pdo->query($query);
$veiculos = $stmt->fetchAll();

function prazoTotal($processo) {
    return in_array($processo, ['Roubo/Furto', 'Apropriação Indébita', 'ApropriaÃ§Ã£o IndÃ©bita']) ? 90 : 45;
}

function formatarData($data) {
    return date('d/m/Y', strtotime($data));
}

function formatarDataHora($data) {
    return date('d/m/Y \a\s H:i', strtotime($data));
}

function normalizarTexto($texto) {
    $map = [
        'Ã§' => 'ç', 'Ã£' => 'ã', 'Ã©' => 'é', 'Ã­' => 'í', 'Ã¡' => 'á',
        'Ã³' => 'ó', 'Ãº' => 'ú', 'Ã¢' => 'â', 'Ãª' => 'ê', 'Ãµ' => 'õ',
    ];
    return strtr((string)$texto, $map);
}

function statusBadge($status) {
    $status = normalizarTexto($status);
    if ($status === 'Entregue' || $status === 'Recuperada') return 'recovered';
    if ($status === 'Em Oficina' || $status === 'Em Perícia') return 'warning';
    if ($status === 'Sindicância' || $status === 'Validação de Sindicância') return 'purple';
    return 'danger';
}

$resumo = [
    'roubo' => 0,
    'furto' => 0,
    'apropriacao' => 0,
    'recuperadas' => 0,
];

foreach ($veiculos as $veiculo) {
    $processo = normalizarTexto($veiculo['processo']);
    $status = normalizarTexto($veiculo['status_atual']);
    if ($processo === 'Roubo/Furto') {
        $resumo['roubo']++;
    }
    if (stripos($processo, 'furto') !== false) {
        $resumo['furto']++;
    }
    if (stripos($processo, 'Apropria') !== false) {
        $resumo['apropriacao']++;
    }
    if ($status === 'Entregue' || $status === 'Recuperada') {
        $resumo['recuperadas']++;
    }
}
?>
<section class="home-screen">
    <div class="hello-row">
        <div>
            <h2>Olá, Gustavo! <span>👋</span></h2>
            <p>Aqui está o resumo de hoje</p>
        </div>
        <button class="date-chip" type="button">
            <i class="fas fa-calendar-day"></i>
            <strong><?php echo date('d/m/Y'); ?></strong>
            <i class="fas fa-chevron-down"></i>
        </button>
    </div>

    <div class="summary-grid">
        <article class="summary-card danger">
            <span class="summary-icon"><i class="fas fa-shield-halved"></i></span>
            <strong><?php echo $resumo['roubo']; ?></strong>
            <p>Roubadas</p>
            <em></em>
        </article>
        <article class="summary-card orange">
            <span class="summary-icon"><i class="fas fa-car-burst"></i></span>
            <strong><?php echo $resumo['furto']; ?></strong>
            <p>Furtos</p>
            <em></em>
        </article>
        <article class="summary-card purple">
            <span class="summary-icon"><i class="fas fa-user"></i></span>
            <strong><?php echo $resumo['apropriacao']; ?></strong>
            <p>Apropriação</p>
            <em></em>
        </article>
        <article class="summary-card green">
            <span class="summary-icon"><i class="fas fa-check"></i></span>
            <strong><?php echo $resumo['recuperadas']; ?></strong>
            <p>Recuperadas</p>
            <em></em>
        </article>
    </div>

    <a href="cadastrar.php" class="create-card">
        <span><i class="fas fa-plus"></i></span>
        <div>
            <strong>Cadastrar veículo</strong>
            <p>Adicionar novo veículo ao sistema</p>
        </div>
        <i class="fas fa-chevron-right"></i>
    </a>

    <div class="search-row">
        <label class="search-box">
            <i class="fas fa-magnifying-glass"></i>
            <input id="searchInput" type="text" placeholder="Buscar por placa, proprietário ou condutor" />
        </label>
        <button class="filter-button" type="button" aria-label="Filtrar">
            <i class="fas fa-filter"></i>
        </button>
    </div>

    <div class="list-heading">
        <h3><i class="fas fa-car-side"></i> Lista de veículos</h3>
        <button type="button"><i class="fas fa-arrow-down-wide-short"></i> Ordenar</button>
    </div>

    <div class="vehicle-list" id="vehiclesList">
        <?php if (empty($veiculos)): ?>
            <div class="empty-state">Nenhum veículo cadastrado no momento.</div>
        <?php endif; ?>

        <?php foreach ($veiculos as $veiculo):
            $dataAcionamento = new DateTime($veiculo['data_acionamento']);
            $hoje = new DateTime();
            $diasDecorridos = max(0, $hoje->diff($dataAcionamento)->days);
            $totalPrazo = prazoTotal($veiculo['processo']);
            $diasRestantes = $totalPrazo - $diasDecorridos;
            $atrasado = $diasRestantes < 0;
            $statusClass = statusBadge($veiculo['status_atual']);
            $searchText = implode(' ', [
                $veiculo['placa'],
                $veiculo['proprietario'],
                $veiculo['condutor'],
                $veiculo['cidade'],
                $veiculo['processo'],
                $veiculo['status_atual'],
            ]);
        ?>
            <article class="vehicle-card<?php echo $atrasado ? ' delayed' : ''; ?>" data-search="<?php echo sanitize($searchText); ?>">
                <div class="plate-icon"><i class="fas fa-rectangle-list"></i></div>
                <div class="vehicle-main">
                    <div class="vehicle-topline">
                        <strong><?php echo sanitize($veiculo['placa']); ?></strong>
                        <span class="status-pill <?php echo $statusClass; ?>"><?php echo sanitize(normalizarTexto($veiculo['status_atual'])); ?></span>
                    </div>
                    <p>Proprietário: <?php echo sanitize($veiculo['proprietario']); ?></p>
                    <small><i class="fas fa-location-dot"></i> <?php echo sanitize($veiculo['cidade']); ?></small>
                    <small><i class="fas fa-calendar-day"></i> <?php echo formatarDataHora($veiculo['status_data']); ?></small>
                    <div class="vehicle-actions">
                        <a href="editar.php?id=<?php echo $veiculo['id']; ?>" title="Editar"><i class="fas fa-pen"></i></a>
                        <button type="button" class="status-btn" data-id="<?php echo $veiculo['id']; ?>" data-placa="<?php echo sanitize($veiculo['placa']); ?>" title="Trocar status"><i class="fas fa-right-left"></i></button>
                        <a href="historico.php?id=<?php echo $veiculo['id']; ?>" title="Histórico"><i class="fas fa-clock-rotate-left"></i></a>
                        <?php if ($veiculo['tipo_veiculo'] === 'Moto' && normalizarTexto($veiculo['status_atual']) === 'Em Oficina'): ?>
                            <button type="button" class="budget-btn" data-id="<?php echo $veiculo['id']; ?>" data-placa="<?php echo sanitize($veiculo['placa']); ?>" title="Orçamento"><i class="fas fa-file-invoice-dollar"></i></button>
                        <?php endif; ?>
                        <button type="button" class="delete-btn danger-action" data-id="<?php echo $veiculo['id']; ?>" data-placa="<?php echo sanitize($veiculo['placa']); ?>" title="Excluir"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
                <a class="card-chevron" href="editar.php?id=<?php echo $veiculo['id']; ?>" aria-label="Editar veículo">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php include 'modals/modal_status.php'; ?>
<?php include 'modals/modal_orcamento.php'; ?>

<?php include 'includes/footer.php'; ?>
