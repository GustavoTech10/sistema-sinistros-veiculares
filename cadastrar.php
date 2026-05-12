<?php
require 'db_connect.php';
$pageTitle = 'Cadastrar Veículo';
include 'includes/header.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $placa = strtoupper(trim($_POST['placa'] ?? ''));
    $proprietario = trim($_POST['proprietario'] ?? '');
    $condutor = trim($_POST['condutor'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $processo = trim($_POST['processo'] ?? '');
    $dataAcionamento = trim($_POST['data_acionamento'] ?? '');
    $tipoVeiculo = 'Moto';

    if (!preg_match('/^[A-Z]{3}-\d[A-Z]\d{2}$/', $placa)) {
        $errors[] = 'A placa deve seguir o formato AAA-1A11.';
    }
    if (!$proprietario) {
        $errors[] = 'O proprietário é obrigatório.';
    }
    if (!$condutor) {
        $errors[] = 'O condutor é obrigatório.';
    }
    if (!$cidade) {
        $errors[] = 'A cidade é obrigatória.';
    }
    if (!in_array($processo, ['Roubo/Furto', 'Apropriação Indébita', 'Colisão', 'Colisão com terceiro', 'Em Compras'], true)) {
        $errors[] = 'Processo inválido.';
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataAcionamento)) {
        $errors[] = 'Data de acionamento inválida.';
    }
    $stmt = $pdo->prepare('SELECT id FROM veiculos WHERE placa = ?');
    $stmt->execute([$placa]);
    if ($stmt->fetch()) {
        $errors[] = 'Já existe um veículo cadastrado com esta placa.';
    }

    if (empty($errors)) {
        $pdo->beginTransaction();
        try {
            $insert = $pdo->prepare('INSERT INTO veiculos (placa, proprietario, condutor, cidade, processo, data_acionamento, tipo_veiculo, criado_em) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
            $insert->execute([$placa, $proprietario, $condutor, $cidade, $processo, $dataAcionamento, $tipoVeiculo]);
            $veiculoId = $pdo->lastInsertId();
            $statusIns = $pdo->prepare('INSERT INTO status_log (veiculo_id, status, data_hora) VALUES (?, ?, NOW())');
            $statusIns->execute([$veiculoId, 'Comunicado']);
            $pdo->commit();
            $success = 'Veículo cadastrado com sucesso.';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Erro ao gravar veículo: ' . $e->getMessage();
        }
    }
}

function old($field) {
    return htmlspecialchars($_POST[$field] ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<section class="page-section form-section">
    <div class="panel-card form-card">
        <div class="panel-header">
            <h3 class="panel-title"><i class="fas fa-plus-circle"></i> Novo registro de sinistro</h3>
            <span class="badge info"><i class="fas fa-clipboard-list"></i> Preencha os dados do veículo</span>
        </div>
        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?> <a href="index.php">Voltar à lista</a></div>
        <?php endif; ?>

        <form action="cadastrar.php" method="post" class="form-grid" novalidate>
            <label>
                <span>Placa</span>
                <input id="placaInput" name="placa" type="text" maxlength="8" placeholder="AAA-1A11" value="<?php echo old('placa'); ?>" required />
            </label>
            <label>
                <span>Proprietário</span>
                <input name="proprietario" type="text" maxlength="120" value="<?php echo old('proprietario'); ?>" required />
            </label>
            <label>
                <span>Condutor</span>
                <input name="condutor" type="text" maxlength="120" value="<?php echo old('condutor'); ?>" required />
            </label>
            <label>
                <span>Cidade</span>
                <input name="cidade" type="text" maxlength="120" value="<?php echo old('cidade'); ?>" required />
            </label>
            <label>
                <span>Processo</span>
                <select name="processo" required>
                    <option value="">Selecione...</option>
                    <option value="Roubo/Furto"<?php echo old('processo')==='Roubo/Furto' ? ' selected' : ''; ?>>Roubo/Furto</option>
                    <option value="Apropriação Indébita"<?php echo old('processo')==='Apropriação Indébita' ? ' selected' : ''; ?>>Apropriação Indébita</option>
                    <option value="Colisão"<?php echo old('processo')==='Colisão' ? ' selected' : ''; ?>>Colisão</option>
                    <option value="Colisão com terceiro"<?php echo old('processo')==='Colisão com terceiro' ? ' selected' : ''; ?>>Colisão com terceiro</option>
                    <option value="Em Compras"<?php echo old('processo')==='Em Compras' ? ' selected' : ''; ?>>Em Compras</option>
                </select>
            </label>
            <label>
                <span>Data de Acionamento</span>
                <input name="data_acionamento" type="date" value="<?php echo old('data_acionamento'); ?>" required />
            </label>
            <label>
                <span>Tipo de Veículo</span>
                <input type="text" value="Moto" disabled />
                <input type="hidden" name="tipo_veiculo" value="Moto" />
            </label>
            <div class="form-actions">
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
                <button type="submit" name="submit_cadastrar" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Salvar veículo</button>
            </div>
        </form>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
