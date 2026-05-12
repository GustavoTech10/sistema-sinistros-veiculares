-- script.sql
-- Banco de dados para o sistema de controle de sinistros veiculares
CREATE DATABASE IF NOT EXISTS `sinistros_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sinistros_db`;

CREATE TABLE IF NOT EXISTS `veiculos` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `placa` VARCHAR(8) NOT NULL UNIQUE,
  `proprietario` VARCHAR(120) NOT NULL,
  `condutor` VARCHAR(120) NOT NULL,
  `cidade` VARCHAR(120) NOT NULL,
  `processo` VARCHAR(50) NOT NULL,
  `data_acionamento` DATE NOT NULL,
  `tipo_veiculo` VARCHAR(16) NOT NULL,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `status_log` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `veiculo_id` INT UNSIGNED NOT NULL,
  `status` VARCHAR(64) NOT NULL,
  `data_hora` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `orcamentos` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `veiculo_id` INT UNSIGNED NOT NULL,
  `valor_pecas` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `valor_mao_obra` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nenhum registro inicial é inserido automaticamente.
-- O sistema é entregue com o banco criado, mas vazio para iniciar do zero.
