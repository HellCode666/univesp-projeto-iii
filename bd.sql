-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 24/05/2025 às 19:00
-- Versão do servidor: (Corrigido para compatibilidade)
-- Versão do PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `projeto_sistema`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `agendamentos`
--

DROP TABLE IF EXISTS `agendamentos`;
CREATE TABLE IF NOT EXISTS `agendamentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_cliente` int NOT NULL,
  `id_servico` int NOT NULL,
  `id_funcionario_agendou` int NOT NULL COMMENT 'Funcionário que registrou o agendamento',
  `data_hora_inicio` datetime NOT NULL COMMENT 'Data e hora de início do agendamento',
  `data_hora_fim` datetime NOT NULL COMMENT 'Data e hora de término do agendamento (calculado)',
  `status_agendamento` enum('agendado','concluido','cancelado','nao_compareceu') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'agendado',
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_servico` (`id_servico`),
  KEY `id_funcionario_agendou` (`id_funcionario_agendou`),
  KEY `idx_data_hora_inicio` (`data_hora_inicio`),
  KEY `idx_data_hora_fim` (`data_hora_fim`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `agendamentos`
--

INSERT INTO `agendamentos` (`id`, `id_cliente`, `id_servico`, `id_funcionario_agendou`, `data_hora_inicio`, `data_hora_fim`, `status_agendamento`, `observacoes`, `data_criacao`) VALUES
(5, 3, 3, 2, '2025-05-24 15:40:00', '2025-05-24 16:40:00', 'nao_compareceu', NULL, '2025-05-24 18:35:38'),
(6, 2, 2, 2, '2025-05-24 17:40:00', '2025-05-24 18:10:00', 'concluido', NULL, '2025-05-24 18:35:53'),
(7, 2, 4, 1, '2025-05-24 17:30:00', '2025-05-24 19:10:00', 'agendado', NULL, '2025-05-24 18:42:40');

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

DROP TABLE IF EXISTS `clientes`;
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome_completo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cpf` varchar(14) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone_celular` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefone_fixo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `endereco_rua` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco_numero` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco_complemento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco_bairro` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco_cidade` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco_estado` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco_cep` varchar(9) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `data_cadastro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `id_funcionario_cadastro` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpf` (`cpf`),
  KEY `id_funcionario_cadastro` (`id_funcionario_cadastro`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id`, `nome_completo`, `cpf`, `telefone_celular`, `telefone_fixo`, `email`, `data_nascimento`, `endereco_rua`, `endereco_numero`, `endereco_complemento`, `endereco_bairro`, `endereco_cidade`, `endereco_estado`, `endereco_cep`, `observacoes`, `data_cadastro`, `id_funcionario_cadastro`) VALUES
(2, 'Json Mamoa', NULL, '11955665566', NULL, 'clienteBrabo@hotmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-24 18:34:08', 2),
(3, 'Adilson Cruz', NULL, '11965335353', NULL, 'add157@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-24 18:34:45', 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `funcionarios`
--

DROP TABLE IF EXISTS `funcionarios`;
CREATE TABLE IF NOT EXISTS `funcionarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome_completo` varchar(255) NOT NULL,
  `email` varchar(191) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `cargo` enum('admin','atendente') NOT NULL DEFAULT 'atendente',
  `data_cadastro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- CORRIGIDO AQUI

--
-- Despejando dados para a tabela `funcionarios`
--

INSERT INTO `funcionarios` (`id`, `nome_completo`, `email`, `senha`, `cargo`, `data_cadastro`) VALUES
(1, 'giovanny', 'gio@teste.com', '$2y$10$/0zDeLC5Cm6qfqMihTftvuCEUDliOtwMPBwG8jWsq6ilHhtEieiyu', 'atendente', '2025-05-24 15:41:22'),
(2, 'Admin', 'admin@admin.com', '$2y$10$arPJ5eeUpxUVG.Lecb.lYu8ZpO1XZy6IVz/1B7z5D83wDjKMKRq/W', 'admin', '2025-05-24 17:10:42');

-- --------------------------------------------------------

--
-- Estrutura para tabela `servicos`
--

DROP TABLE IF EXISTS `servicos`;
CREATE TABLE IF NOT EXISTS `servicos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome_servico` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `preco` decimal(10,2) NOT NULL,
  `duracao_estimada_minutos` int DEFAULT NULL COMMENT 'Duração do serviço em minutos, para agendamento',
  `status` enum('ativo','inativo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ativo',
  `data_cadastro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `id_funcionario_cadastro` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_funcionario_cadastro` (`id_funcionario_cadastro`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `servicos`
--

INSERT INTO `servicos` (`id`, `nome_servico`, `descricao`, `preco`, `duracao_estimada_minutos`, `status`, `data_cadastro`, `id_funcionario_cadastro`) VALUES
(2, 'Corte Cabelo Basico Macho', NULL, 30.00, 30, 'ativo', '2025-05-24 18:32:42', 2),
(3, 'Corte Com Alisamento', NULL, 70.00, 60, 'ativo', '2025-05-24 18:33:06', 2),
(4, 'Barba e Cabelo', NULL, 80.00, 100, 'ativo', '2025-05-24 18:33:32', 2);

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD CONSTRAINT `agendamentos_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `agendamentos_ibfk_2` FOREIGN KEY (`id_servico`) REFERENCES `servicos` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `agendamentos_ibfk_3` FOREIGN KEY (`id_funcionario_agendou`) REFERENCES `funcionarios` (`id`) ON DELETE RESTRICT;

--
-- Restrições para tabelas `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `clientes_ibfk_1` FOREIGN KEY (`id_funcionario_cadastro`) REFERENCES `funcionarios` (`id`) ON DELETE RESTRICT;

--
-- Restrições para tabelas `servicos`
--
ALTER TABLE `servicos`
  ADD CONSTRAINT `servicos_ibfk_1` FOREIGN KEY (`id_funcionario_cadastro`) REFERENCES `funcionarios` (`id`) ON DELETE RESTRICT;
COMMIT;
