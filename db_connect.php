<?php
// db_connect.php
// Conex�o PDO segura para WampServer com charset utf8mb4
$host = 'localhost';
$dbName = 'sinistros_db';
$user = 'root';
$password = '';
$dsn = "mysql:host=$host;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $password, $options);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbName`");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `veiculos` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `placa` VARCHAR(8) NOT NULL UNIQUE,
        `proprietario` VARCHAR(120) NOT NULL,
        `condutor` VARCHAR(120) NOT NULL,
        `cidade` VARCHAR(120) NOT NULL DEFAULT '',
        `processo` VARCHAR(50) NOT NULL,
        `data_acionamento` DATE NOT NULL,
        `tipo_veiculo` VARCHAR(16) NOT NULL,
        `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $columnCheck = $pdo->query("SHOW COLUMNS FROM `veiculos` LIKE 'cidade'")->fetch();
    if (!$columnCheck) {
        $pdo->exec("ALTER TABLE `veiculos` ADD COLUMN `cidade` VARCHAR(120) NOT NULL DEFAULT '' AFTER `condutor`");
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS `status_log` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `veiculo_id` INT UNSIGNED NOT NULL,
        `status` VARCHAR(64) NOT NULL,
        `data_hora` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `orcamentos` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `veiculo_id` INT UNSIGNED NOT NULL,
        `valor_pecas` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        `valor_mao_obra` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        `total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // O banco de dados é criado automaticamente quando o sistema inicia.
    // Nenhum registro de veículos é inserido automaticamente para manter o sistema limpo.
} catch (PDOException $e) {
    echo '<h2>Erro de conexão com o banco de dados</h2>';
    echo '<p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    exit;
}
