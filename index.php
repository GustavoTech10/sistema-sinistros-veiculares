<?php
require 'db_connect.php';
$pageTitle = 'Veículos Cadastrados';
include 'includes/header.php';

function sanitize($value) {
    return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
}

$registerErrors = [];
$registerSuccess = '';
$editErrors = [];
$editSuccess = '';
$editOld = [
    'id' => '',
    'placa' => '',
    'proprietario' => '',
    'condutor' => '',
    'cidade' => '',
    'processo' => '',
    'tipo_veiculo' => 'Moto',
];
$registerOld = [
    'placa' => '',
    'proprietario' => '',
    'condutor' => '',
    'cidade' => '',
    'processo' => '',
    'data_acionamento' => date('Y-m-d'),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_modal_cadastrar'])) {
    $placa = strtoupper(trim($_POST['placa'] ?? ''));
    $proprietario = trim($_POST['proprietario'] ?? '');
    $condutor = trim($_POST['condutor'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $processo = trim($_POST['processo'] ?? '');
    $dataAcionamento = trim($_POST['data_acionamento'] ?? '');
    $tipoVeiculo = 'Moto';

    $registerOld = [
        'placa' => $placa,
        'proprietario' => $proprietario,
        'condutor' => $condutor,
        'cidade' => $cidade,
        'processo' => $processo,
        'data_acionamento' => $dataAcionamento ?: date('Y-m-d'),
    ];

    if (!preg_match('/^[A-Z]{3}-\d[A-Z]\d{2}$/', $placa)) {
        $registerErrors[] = 'A placa deve seguir o formato AAA-1A11.';
    }
    if (!$proprietario) {
        $registerErrors[] = 'O proprietario e obrigatorio.';
    }
    if (!$condutor) {
        $registerErrors[] = 'O condutor e obrigatorio.';
    }
    if (!$cidade) {
        $registerErrors[] = 'A cidade e obrigatoria.';
    }
    if (!in_array($processo, ['Roubo/Furto', 'Apropriação Indébita', 'ApropriaÃ§Ã£o IndÃ©bita', 'Colisão', 'ColisÃ£o', 'Colisão com terceiro', 'ColisÃ£o com terceiro', 'Em Compras'], true)) {
        $registerErrors[] = 'Processo invalido.';
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataAcionamento)) {
        $registerErrors[] = 'Data de acionamento invalida.';
    }

    $stmt = $pdo->prepare('SELECT id FROM veiculos WHERE placa = ?');
    $stmt->execute([$placa]);
    if ($stmt->fetch()) {
        $registerErrors[] = 'Ja existe um veiculo cadastrado com esta placa.';
    }

    if (empty($registerErrors)) {
        $pdo->beginTransaction();
        try {
            $insert = $pdo->prepare('INSERT INTO veiculos (placa, proprietario, condutor, cidade, processo, data_acionamento, tipo_veiculo, criado_em) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
            $insert->execute([$placa, $proprietario, $condutor, $cidade, $processo, $dataAcionamento, $tipoVeiculo]);
            $veiculoId = $pdo->lastInsertId();
            $statusIns = $pdo->prepare('INSERT INTO status_log (veiculo_id, status, data_hora) VALUES (?, ?, NOW())');
            $statusIns->execute([$veiculoId, 'Comunicado']);
            $pdo->commit();
            $registerSuccess = 'Veiculo cadastrado com sucesso.';
            $registerOld = [
                'placa' => '',
                'proprietario' => '',
                'condutor' => '',
                'cidade' => '',
                'processo' => '',
                'data_acionamento' => date('Y-m-d'),
            ];
        } catch (PDOException $e) {
            $pdo->rollBack();
            $registerErrors[] = 'Erro ao gravar veiculo: ' . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_modal_editar'])) {
    $editId = filter_input(INPUT_POST, 'veiculo_id', FILTER_VALIDATE_INT);
    $placa = strtoupper(trim($_POST['placa'] ?? ''));
    $proprietario = trim($_POST['proprietario'] ?? '');
    $condutor = trim($_POST['condutor'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $processo = trim($_POST['processo'] ?? '');
    $tipoVeiculo = trim($_POST['tipo_veiculo'] ?? '');

    $editOld = [
        'id' => $editId ?: '',
        'placa' => $placa,
        'proprietario' => $proprietario,
        'condutor' => $condutor,
        'cidade' => $cidade,
        'processo' => $processo,
        'tipo_veiculo' => $tipoVeiculo,
    ];

    if (!$editId) {
        $editErrors[] = 'Veiculo invalido para edicao.';
    }
    if (!preg_match('/^[A-Z]{3}-\d[A-Z]\d{2}$/', $placa)) {
        $editErrors[] = 'A placa deve seguir o formato AAA-1A11.';
    }
    if (!$proprietario) {
        $editErrors[] = 'O proprietario e obrigatorio.';
    }
    if (!$condutor) {
        $editErrors[] = 'O condutor e obrigatorio.';
    }
    if (!$cidade) {
        $editErrors[] = 'A cidade e obrigatoria.';
    }
    if (!in_array($processo, ['Roubo/Furto', 'ApropriaÃ§Ã£o IndÃ©bita', 'ApropriaÃƒÂ§ÃƒÂ£o IndÃƒÂ©bita', 'ColisÃ£o', 'ColisÃƒÂ£o', 'ColisÃ£o com terceiro', 'ColisÃƒÂ£o com terceiro', 'Em Compras'], true)) {
        $editErrors[] = 'Processo invalido.';
    }
    if (!in_array($tipoVeiculo, ['Carro', 'Moto'], true)) {
        $editErrors[] = 'Tipo de veiculo invalido.';
    }

    if ($editId) {
        $stmt = $pdo->prepare('SELECT id FROM veiculos WHERE placa = ? AND id != ?');
        $stmt->execute([$placa, $editId]);
        if ($stmt->fetch()) {
            $editErrors[] = 'Ja existe outro veiculo cadastrado com esta placa.';
        }
    }

    if (empty($editErrors)) {
        $update = $pdo->prepare('UPDATE veiculos SET placa = ?, proprietario = ?, condutor = ?, cidade = ?, processo = ?, tipo_veiculo = ? WHERE id = ?');
        $update->execute([$placa, $proprietario, $condutor, $cidade, $processo, $tipoVeiculo, $editId]);
        $editSuccess = 'Veiculo atualizado com sucesso.';
        $editOld = [
            'id' => '',
            'placa' => '',
            'proprietario' => '',
            'condutor' => '',
            'cidade' => '',
            'processo' => '',
            'tipo_veiculo' => 'Moto',
        ];
    }
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

    <?php if ($registerSuccess): ?>
        <div class="registration-feedback success">
            <i class="fas fa-circle-check"></i>
            <span><?php echo sanitize($registerSuccess); ?></span>
        </div>
    <?php endif; ?>
    <?php if ($editSuccess): ?>
        <div class="registration-feedback success">
            <i class="fas fa-circle-check"></i>
            <span><?php echo sanitize($editSuccess); ?></span>
        </div>
    <?php endif; ?>

    <button type="button" class="create-card open-register-modal">
        <span><i class="fas fa-plus"></i></span>
        <div>
            <strong>Cadastrar veículo</strong>
            <p>Adicionar novo veículo ao sistema</p>
        </div>
        <i class="fas fa-chevron-right"></i>
    </button>

    <div class="search-row compact">
        <label class="search-box">
            <i class="fas fa-magnifying-glass"></i>
            <input id="searchInput" type="text" placeholder="Buscar por placa, proprietário ou condutor" />
        </label>
    </div>

    <div class="list-heading">
        <h3><i class="fas fa-car-side"></i> Lista de veículos</h3>
        <span><?php echo count($veiculos); ?> registros</span>
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
                        <button
                            type="button"
                            class="edit-btn"
                            data-id="<?php echo $veiculo['id']; ?>"
                            data-placa="<?php echo sanitize($veiculo['placa']); ?>"
                            data-proprietario="<?php echo sanitize($veiculo['proprietario']); ?>"
                            data-condutor="<?php echo sanitize($veiculo['condutor']); ?>"
                            data-cidade="<?php echo sanitize($veiculo['cidade']); ?>"
                            data-processo="<?php echo sanitize($veiculo['processo']); ?>"
                            data-tipo="<?php echo sanitize($veiculo['tipo_veiculo']); ?>"
                            title="Editar"
                        ><i class="fas fa-pen"></i><span>Editar</span></button>
                        <button type="button" class="status-btn" data-id="<?php echo $veiculo['id']; ?>" data-placa="<?php echo sanitize($veiculo['placa']); ?>" title="Trocar status"><i class="fas fa-right-left"></i><span>Status</span></button>
                        <a href="historico.php?id=<?php echo $veiculo['id']; ?>" title="Histórico"><i class="fas fa-clock-rotate-left"></i><span>Historico</span></a>
                        <?php if ($veiculo['tipo_veiculo'] === 'Moto' && normalizarTexto($veiculo['status_atual']) === 'Em Oficina'): ?>
                            <button type="button" class="budget-btn" data-id="<?php echo $veiculo['id']; ?>" data-placa="<?php echo sanitize($veiculo['placa']); ?>" title="Orçamento"><i class="fas fa-file-invoice-dollar"></i><span>Orcamento</span></button>
                        <?php endif; ?>
                        <button type="button" class="delete-btn danger-action" data-id="<?php echo $veiculo['id']; ?>" data-placa="<?php echo sanitize($veiculo['placa']); ?>" title="Excluir"><i class="fas fa-trash"></i><span>Excluir</span></button>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<div class="modal-overlay<?php echo $registerErrors ? ' modal-open' : ''; ?>" id="cadastroModalOverlay" aria-hidden="<?php echo $registerErrors ? 'false' : 'true'; ?>">
    <div class="modal-window register-modal" role="dialog" aria-modal="true" aria-labelledby="cadastroModalTitle">
        <button type="button" class="modal-close" data-close="cadastroModalOverlay" title="Fechar"><i class="fas fa-times"></i></button>
        <div class="modal-header register-header">
            <span class="modal-badge"><i class="fas fa-plus"></i></span>
            <div>
                <h2 id="cadastroModalTitle">Cadastrar novo veiculo</h2>
                <p>Preencha os dados principais para adicionar o veiculo ao painel.</p>
            </div>
        </div>

        <?php if ($registerErrors): ?>
            <div class="alert alert-danger modal-alert">
                <ul>
                    <?php foreach ($registerErrors as $error): ?>
                        <li><?php echo sanitize($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="index.php" method="post" class="modal-form register-form" novalidate>
            <label>
                <span>Placa</span>
                <input id="placaInput" name="placa" type="text" maxlength="8" placeholder="AAA-1A11" value="<?php echo sanitize($registerOld['placa']); ?>" required />
            </label>
            <label>
                <span>Proprietario</span>
                <input name="proprietario" type="text" maxlength="120" value="<?php echo sanitize($registerOld['proprietario']); ?>" required />
            </label>
            <label>
                <span>Condutor</span>
                <input name="condutor" type="text" maxlength="120" value="<?php echo sanitize($registerOld['condutor']); ?>" required />
            </label>
            <label>
                <span>Cidade</span>
                <input name="cidade" type="text" maxlength="120" value="<?php echo sanitize($registerOld['cidade']); ?>" required />
            </label>
            <label>
                <span>Processo</span>
                <select name="processo" required>
                    <option value="">Selecione...</option>
                    <option value="Roubo/Furto"<?php echo $registerOld['processo'] === 'Roubo/Furto' ? ' selected' : ''; ?>>Roubo/Furto</option>
                    <option value="ApropriaÃ§Ã£o IndÃ©bita"<?php echo $registerOld['processo'] === 'ApropriaÃ§Ã£o IndÃ©bita' ? ' selected' : ''; ?>>Apropriacao Indebita</option>
                    <option value="ColisÃ£o"<?php echo $registerOld['processo'] === 'ColisÃ£o' ? ' selected' : ''; ?>>Colisao</option>
                    <option value="ColisÃ£o com terceiro"<?php echo $registerOld['processo'] === 'ColisÃ£o com terceiro' ? ' selected' : ''; ?>>Colisao com terceiro</option>
                    <option value="Em Compras"<?php echo $registerOld['processo'] === 'Em Compras' ? ' selected' : ''; ?>>Em Compras</option>
                </select>
            </label>
            <label>
                <span>Data de acionamento</span>
                <input name="data_acionamento" type="date" value="<?php echo sanitize($registerOld['data_acionamento']); ?>" required />
            </label>
            <label class="register-full">
                <span>Tipo de veiculo</span>
                <input type="text" value="Moto" disabled />
                <input type="hidden" name="tipo_veiculo" value="Moto" />
            </label>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" data-close="cadastroModalOverlay"><i class="fas fa-xmark"></i> Cancelar</button>
                <button type="submit" name="submit_modal_cadastrar" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Salvar veiculo</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay<?php echo $editErrors ? ' modal-open' : ''; ?>" id="editarModalOverlay" aria-hidden="<?php echo $editErrors ? 'false' : 'true'; ?>">
    <div class="modal-window register-modal" role="dialog" aria-modal="true" aria-labelledby="editarModalTitle">
        <button type="button" class="modal-close" data-close="editarModalOverlay" title="Fechar"><i class="fas fa-times"></i></button>
        <div class="modal-header register-header">
            <span class="modal-badge edit-badge"><i class="fas fa-pen"></i></span>
            <div>
                <h2 id="editarModalTitle">Editar veiculo</h2>
                <p>Atualize os dados do cadastro sem sair da lista principal.</p>
            </div>
        </div>

        <?php if ($editErrors): ?>
            <div class="alert alert-danger modal-alert">
                <ul>
                    <?php foreach ($editErrors as $error): ?>
                        <li><?php echo sanitize($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="index.php" method="post" class="modal-form register-form" novalidate>
            <input type="hidden" name="veiculo_id" id="editVeiculoId" value="<?php echo sanitize($editOld['id']); ?>" />
            <label>
                <span>Placa</span>
                <input id="editPlacaInput" name="placa" type="text" maxlength="8" placeholder="AAA-1A11" value="<?php echo sanitize($editOld['placa']); ?>" required />
            </label>
            <label>
                <span>Proprietario</span>
                <input id="editProprietario" name="proprietario" type="text" maxlength="120" value="<?php echo sanitize($editOld['proprietario']); ?>" required />
            </label>
            <label>
                <span>Condutor</span>
                <input id="editCondutor" name="condutor" type="text" maxlength="120" value="<?php echo sanitize($editOld['condutor']); ?>" required />
            </label>
            <label>
                <span>Cidade</span>
                <input id="editCidade" name="cidade" type="text" maxlength="120" value="<?php echo sanitize($editOld['cidade']); ?>" required />
            </label>
            <label>
                <span>Processo</span>
                <select id="editProcesso" name="processo" required>
                    <option value="">Selecione...</option>
                    <option value="Roubo/Furto"<?php echo $editOld['processo'] === 'Roubo/Furto' ? ' selected' : ''; ?>>Roubo/Furto</option>
                    <option value="ApropriaÃ§Ã£o IndÃ©bita"<?php echo $editOld['processo'] === 'ApropriaÃ§Ã£o IndÃ©bita' ? ' selected' : ''; ?>>Apropriacao Indebita</option>
                    <option value="ColisÃ£o"<?php echo $editOld['processo'] === 'ColisÃ£o' ? ' selected' : ''; ?>>Colisao</option>
                    <option value="ColisÃ£o com terceiro"<?php echo $editOld['processo'] === 'ColisÃ£o com terceiro' ? ' selected' : ''; ?>>Colisao com terceiro</option>
                    <option value="Em Compras"<?php echo $editOld['processo'] === 'Em Compras' ? ' selected' : ''; ?>>Em Compras</option>
                </select>
            </label>
            <label>
                <span>Tipo de veiculo</span>
                <select id="editTipoVeiculo" name="tipo_veiculo" required>
                    <option value="Moto"<?php echo $editOld['tipo_veiculo'] === 'Moto' ? ' selected' : ''; ?>>Moto</option>
                    <option value="Carro"<?php echo $editOld['tipo_veiculo'] === 'Carro' ? ' selected' : ''; ?>>Carro</option>
                </select>
            </label>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" data-close="editarModalOverlay"><i class="fas fa-xmark"></i> Cancelar</button>
                <button type="submit" name="submit_modal_editar" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Atualizar veiculo</button>
            </div>
        </form>
    </div>
</div>

<?php include 'modals/modal_status.php'; ?>
<?php include 'modals/modal_orcamento.php'; ?>

<?php include 'includes/footer.php'; ?>
