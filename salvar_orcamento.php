<?php
require 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
$id = filter_input(INPUT_POST, 'veiculo_id', FILTER_VALIDATE_INT);
$valorPecas = filter_input(INPUT_POST, 'valor_pecas', FILTER_VALIDATE_FLOAT);
$valorMaoObra = filter_input(INPUT_POST, 'valor_mao_obra', FILTER_VALIDATE_FLOAT);
if (!$id || $valorPecas === false || $valorMaoObra === false) {
    header('Location: index.php');
    exit;
}
$stmt = $pdo->prepare('SELECT tipo_veiculo FROM veiculos WHERE id = ?');
$stmt->execute([$id]);
$veiculo = $stmt->fetch();
if (!$veiculo || $veiculo['tipo_veiculo'] !== 'Moto') {
    header('Location: index.php');
    exit;
}
$total = $valorPecas + $valorMaoObra;
$insert = $pdo->prepare('INSERT INTO orcamentos (veiculo_id, valor_pecas, valor_mao_obra, total, criado_em) VALUES (?, ?, ?, ?, NOW())');
$insert->execute([$id, $valorPecas, $valorMaoObra, $total]);
header('Location: index.php');
exit;
