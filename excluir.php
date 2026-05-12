<?php
require 'db_connect.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: index.php');
    exit;
}

try {
    $pdo->beginTransaction();
    $pdo->prepare('DELETE FROM orcamentos WHERE veiculo_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM status_log WHERE veiculo_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM veiculos WHERE id = ?')->execute([$id]);
    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
}
header('Location: index.php');
exit;
