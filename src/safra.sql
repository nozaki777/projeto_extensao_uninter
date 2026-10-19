-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 19-Out-2026 às 13:56
-- Versão do servidor: 5.7.40
-- versão do PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `safra`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `chegada_fruto`
--

DROP TABLE IF EXISTS `chegada_fruto`;
CREATE TABLE IF NOT EXISTS `chegada_fruto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_fruto` date NOT NULL,
  `lote_fruto` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fornecedor` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `origem` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `temp_transporte` time NOT NULL,
  `data_colheita` date NOT NULL,
  `tipo_embalagem` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qtd_saca` decimal(10,2) NOT NULL,
  `maturacao` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `odor_fruto` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cor_fruto` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `situacao` enum('Selecione','Aprovado','Reprovado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Selecione',
  `valor_real` decimal(10,2) NOT NULL DEFAULT '0.00',
  `valor_frete` decimal(10,2) NOT NULL DEFAULT '0.00',
  `nome_motorista` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gasolina_valor` decimal(10,2) NOT NULL DEFAULT '0.00',
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_chegada_data` (`data_fruto`),
  KEY `idx_chegada_origem` (`origem`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `controle_interno`
--

DROP TABLE IF EXISTS `controle_interno`;
CREATE TABLE IF NOT EXISTS `controle_interno` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_chegada` date NOT NULL,
  `lote` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qtd_produzido` int(11) NOT NULL,
  `tipo_envase` enum('Garrafa 1L','Garrafa 500ml','Saquinha 1Kg') COLLATE utf8mb4_unicode_ci NOT NULL,
  `rendimento` decimal(10,2) DEFAULT NULL,
  `obs_cont_interno` text COLLATE utf8mb4_unicode_ci,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_controle_data` (`data_chegada`),
  KEY `idx_controle_tipo` (`tipo_envase`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `destino_prod`
--

DROP TABLE IF EXISTS `destino_prod`;
CREATE TABLE IF NOT EXISTS `destino_prod` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_destino` date NOT NULL,
  `lote_destino` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `local_destino` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_envase` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qtd_destino` int(11) NOT NULL,
  `valor_venda` decimal(10,2) DEFAULT '0.00',
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `ficha_coleta`
--

DROP TABLE IF EXISTS `ficha_coleta`;
CREATE TABLE IF NOT EXISTS `ficha_coleta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_coleta` date NOT NULL,
  `aldeia_ficha` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `coletor_resposavel` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qtd_ajudantes` int(11) DEFAULT '0',
  `qtd_familias` int(11) DEFAULT '0',
  `local_coleta` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fora_qtd_saca` int(11) DEFAULT '0',
  `fora_preco_saca` decimal(10,2) DEFAULT '0.00',
  `fora_valor_total` decimal(10,2) DEFAULT '0.00',
  `fora_quem_comprou` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `local_comprador` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `consumo_qtd_saca` int(11) DEFAULT '0',
  `consumo_qtd_litros` int(11) DEFAULT '0',
  `venda_qtd_saca` int(11) DEFAULT '0',
  `venda_qtd_litros` int(11) DEFAULT '0',
  `venda_preco_litro` decimal(10,2) DEFAULT '0.00',
  `venda_valor_total` decimal(10,2) DEFAULT '0.00',
  `qtd_sacas_total` int(11) DEFAULT '0',
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contato` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `senha_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','supervisor','colaborador') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'colaborador',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_user_email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `users`
--

INSERT INTO `users` (`id`, `nome`, `email`, `contato`, `senha_hash`, `role`, `created_at`) VALUES
(1, 'Administrador', 'brunopnozaki@gmail.com', '', '$2y$10$enSqQQAJ2P0Aeb/TRTalXOemopGKikblpP360rY1bc81mDMF96VNW', 'admin', '2026-05-02 23:16:37'),
(2, 'Teste Colaborador', 'colaborador@bancodedados.org', '', '$2y$10$sxSEy3hgofh3Qs7sm8qbMeNnSRpSbGSsu6/7mMquTyieNEZ8uMsf6', 'colaborador', '2026-05-02 23:18:36'),
(3, 'Teste Supervisor', 'supervisor@bancodedados.org', '', '$2y$10$qENFNVwdEbrDcm20wVfMbO9UNK1l50KymTxXKl/LpT0hJYDmYm/BO', 'supervisor', '2026-05-05 00:10:43');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
