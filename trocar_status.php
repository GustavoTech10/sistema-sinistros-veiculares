<?php
require 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
$id = filter_input(INPUT_POST, 'veiculo_id', FILTER_VALIDATE_INT);
$status = trim($_POST['status'] ?? '');
$validStatus = ['Comunicado', 'Regulagem', 'Em Oficina', 'Sindicância', 'Validação de Sindicância', 'Em Perícia', 'Entregue'];
if (!$id || !in_array($status, $validStatus, true)) {
    header('Location: index.php');
    exit;
}
$stmt = $pdo->prepare('SELECT id FROM veiculos WHERE id = ?');
$stmt->execute([$id]);
$veiculo = $stmt->fetch();
if (!$veiculo) {
    header('Location: index.php');
    exit;
}
$insert = $pdo->prepare('INSERT INTO status_log (veiculo_id, status, data_hora) VALUES (?, ?, NOW())');
$insert->execute([$id, $status]);
header('Location: index.php');
exit;
