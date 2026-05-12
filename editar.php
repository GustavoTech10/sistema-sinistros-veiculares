<?php
require 'db_connect.php';
$pageTitle = 'Editar Veículo';
include 'includes/header.php';

$errors = [];
$success = '';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM veiculos WHERE id = ?');
$stmt->execute([$id]);
$veiculo = $stmt->fetch();
if (!$veiculo) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $placa = strtoupper(trim($_POST['placa'] ?? ''));
    $proprietario = trim($_POST['proprietario'] ?? '');
    $condutor = trim($_POST['condutor'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $processo = trim($_POST['processo'] ?? '');
    $tipoVeiculo = trim($_POST['tipo_veiculo'] ?? '');

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
    if (!in_array($tipoVeiculo, ['Carro', 'Moto'], true)) {
        $errors[] = 'Tipo de veículo inválido.';
    }

    $stmt = $pdo->prepare('SELECT id FROM veiculos WHERE placa = ? AND id != ?');
    $stmt->execute([$placa, $id]);
    if ($stmt->fetch()) {
        $errors[] = 'Já existe outro veículo com esta placa.';
    }

    if (empty($errors)) {
        $update = $pdo->prepare('UPDATE veiculos SET placa = ?, proprietario = ?, condutor = ?, cidade = ?, processo = ?, tipo_veiculo = ? WHERE id = ?');
        $update->execute([$placa, $proprietario, $condutor, $cidade, $processo, $tipoVeiculo, $id]);
        $success = 'Dados atualizados com sucesso.';
        $veiculo = array_merge($veiculo, ['placa' => $placa, 'proprietario' => $proprietario, 'condutor' => $condutor, 'cidade' => $cidade, 'processo' => $processo, 'tipo_veiculo' => $tipoVeiculo]);
    }
}

function old($field, $default) {
    return htmlspecialchars($_POST[$field] ?? $default, ENT_QUOTES, 'UTF-8');
}
?>
<section class="page-section form-section">
    <div class="panel-card form-card">
        <div class="panel-header">
            <h3 class="panel-title"><i class="fas fa-pen-to-square"></i> Atualizar registro</h3>
            <span class="badge info"><i class="fas fa-lock"></i> Data de acionamento bloqueada</span>
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
            <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form action="editar.php?id=<?php echo $id; ?>" method="post" class="form-grid" novalidate>
            <label>
                <span>Placa</span>
                <input id="placaInput" name="placa" type="text" maxlength="8" value="<?php echo old('placa', $veiculo['placa']); ?>" required />
            </label>
            <label>
                <span>Proprietário</span>
                <input name="proprietario" type="text" maxlength="120" value="<?php echo old('proprietario', $veiculo['proprietario']); ?>" required />
            </label>
            <label>
                <span>Condutor</span>
                <input name="condutor" type="text" maxlength="120" value="<?php echo old('condutor', $veiculo['condutor']); ?>" required />
            </label>
            <label>
                <span>Cidade</span>
                <input name="cidade" type="text" maxlength="120" value="<?php echo old('cidade', $veiculo['cidade'] ?? ''); ?>" required />
            </label>
            <label>
                <span>Processo</span>
                <select name="processo" required>
                    <option value="">Selecione...</option>
                    <option value="Roubo/Furto"<?php echo old('processo', $veiculo['processo'])==='Roubo/Furto' ? ' selected' : ''; ?>>Roubo/Furto</option>
                    <option value="Apropriação Indébita"<?php echo old('processo', $veiculo['processo'])==='Apropriação Indébita' ? ' selected' : ''; ?>>Apropriação Indébita</option>
                    <option value="Colisão"<?php echo old('processo', $veiculo['processo'])==='Colisão' ? ' selected' : ''; ?>>Colisão</option>
                    <option value="Colisão com terceiro"<?php echo old('processo', $veiculo['processo'])==='Colisão com terceiro' ? ' selected' : ''; ?>>Colisão com terceiro</option>
                    <option value="Em Compras"<?php echo old('processo', $veiculo['processo'])==='Em Compras' ? ' selected' : ''; ?>>Em Compras</option>
                </select>
            </label>
            <label>
                <span>Data de Acionamento</span>
                <input type="date" value="<?php echo htmlspecialchars($veiculo['data_acionamento'], ENT_QUOTES, 'UTF-8'); ?>" disabled />
            </label>
            <label>
                <span>Tipo de Veículo</span>
                <select name="tipo_veiculo" required>
                    <option value="">Selecione...</option>
                    <option value="Carro"<?php echo old('tipo_veiculo', $veiculo['tipo_veiculo'])==='Carro' ? ' selected' : ''; ?>>Carro</option>
                    <option value="Moto"<?php echo old('tipo_veiculo', $veiculo['tipo_veiculo'])==='Moto' ? ' selected' : ''; ?>>Moto</option>
                </select>
            </label>
            <div class="form-actions">
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
                <button type="submit" name="submit_editar" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Atualizar veículo</button>
            </div>
        </form>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
