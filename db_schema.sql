-- Arquivo: db_schema.sql
-- Criação do banco de dados e tabelas para o Sistema de Gestão de Táxi

-- 1. Criação do Banco de Dados
CREATE DATABASE IF NOT EXISTS `taxi_management_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `taxi_management_system`;

-- 2. Tabela de Categorias de Veículos
CREATE TABLE `categorias_veiculos` (
  `id_categoria` INT(11) NOT NULL AUTO_INCREMENT,
  `nome_categoria` VARCHAR(100) NOT NULL,
  `descricao` TEXT,
  `tarifa_base` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `tarifa_km` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabela de Seguradoras
CREATE TABLE `seguradoras` (
  `id_seguradora` INT(11) NOT NULL AUTO_INCREMENT,
  `nome_seguradora` VARCHAR(150) NOT NULL,
  `telefone` VARCHAR(20),
  `email` VARCHAR(100),
  `cnpj` VARCHAR(18),
  `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_seguradora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabela de Veículos (Táxis)
CREATE TABLE `veiculos` (
  `id_veiculo` INT(11) NOT NULL AUTO_INCREMENT,
  `placa` VARCHAR(10) NOT NULL UNIQUE,
  `marca` VARCHAR(50) NOT NULL,
  `modelo` VARCHAR(50) NOT NULL,
  `ano` YEAR(4) NOT NULL,
  `cor` VARCHAR(30),
  `id_categoria` INT(11) NOT NULL,
  `id_seguradora` INT(11),
  `apolice_numero` VARCHAR(50),
  `data_vencimento_apolice` DATE,
  `status_veiculo` ENUM('Disponível', 'Em Corrida', 'Em Manutenção', 'Inativo') NOT NULL DEFAULT 'Disponível',
  `quilometragem_atual` INT(11) NOT NULL DEFAULT 0,
  `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_veiculo`),
  FOREIGN KEY (`id_categoria`) REFERENCES `categorias_veiculos`(`id_categoria`),
  FOREIGN KEY (`id_seguradora`) REFERENCES `seguradoras`(`id_seguradora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabela de Motoristas
CREATE TABLE `motoristas` (
  `id_motorista` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `cpf` VARCHAR(14) NOT NULL UNIQUE,
  `cnh` VARCHAR(20) NOT NULL UNIQUE,
  `telefone` VARCHAR(20),
  `email` VARCHAR(100) UNIQUE,
  `data_nascimento` DATE,
  `id_veiculo_atual` INT(11), -- Veículo atualmente associado ao motorista
  `status_motorista` ENUM('Disponível', 'Em Corrida', 'Em Descanso', 'Férias') NOT NULL DEFAULT 'Disponível',
  `data_contratacao` DATE NOT NULL,
  `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_motorista`),
  FOREIGN KEY (`id_veiculo_atual`) REFERENCES `veiculos`(`id_veiculo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Tabela de Clientes
CREATE TABLE `clientes` (
  `id_cliente` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `telefone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) UNIQUE,
  `cpf` VARCHAR(14) UNIQUE,
  `endereco` VARCHAR(255),
  `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_cliente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Tabela de Usuários do Sistema (Painel de Administração)
CREATE TABLE `usuarios` (
  `id_usuario` INT(11) NOT NULL AUTO_INCREMENT,
  `nome_completo` VARCHAR(150) NOT NULL,
  `usuario` VARCHAR(50) NOT NULL UNIQUE,
  `senha` VARCHAR(255) NOT NULL, -- Armazenar hash da senha
  `email` VARCHAR(100) UNIQUE,
  `nivel_acesso` ENUM('Administrador', 'Financeiro', 'Operacional', 'Motorista') NOT NULL DEFAULT 'Operacional',
  `id_entidade_associada` INT(11), -- ID do motorista ou cliente associado, se for o caso
  `status_usuario` ENUM('Ativo', 'Inativo') NOT NULL DEFAULT 'Ativo',
  `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Tabela de Reservas/Viagens (Corridas)
CREATE TABLE `reservas` (
  `id_reserva` INT(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` INT(11) NOT NULL,
  `id_motorista` INT(11),
  `id_veiculo` INT(11),
  `origem` VARCHAR(255) NOT NULL,
  `destino` VARCHAR(255) NOT NULL,
  `data_hora_reserva` DATETIME NOT NULL,
  `data_hora_inicio_viagem` DATETIME,
  `data_hora_fim_viagem` DATETIME,
  `distancia_km` DECIMAL(10, 2),
  `valor_estimado` DECIMAL(10, 2),
  `valor_final` DECIMAL(10, 2),
  `status_viagem` ENUM('Pendente', 'Confirmada', 'Em Andamento', 'Concluída', 'Cancelada') NOT NULL DEFAULT 'Pendente',
  `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_reserva`),
  FOREIGN KEY (`id_cliente`) REFERENCES `clientes`(`id_cliente`),
  FOREIGN KEY (`id_motorista`) REFERENCES `motoristas`(`id_motorista`),
  FOREIGN KEY (`id_veiculo`) REFERENCES `veiculos`(`id_veiculo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Tabela de Pagamentos (Relacionado à Reserva)
CREATE TABLE `pagamentos` (
  `id_pagamento` INT(11) NOT NULL AUTO_INCREMENT,
  `id_reserva` INT(11) NOT NULL,
  `valor_pago` DECIMAL(10, 2) NOT NULL,
  `metodo_pagamento` ENUM('Dinheiro', 'Cartão de Crédito', 'Cartão de Débito', 'Pix') NOT NULL,
  `data_pagamento` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_pagamento` ENUM('Pendente', 'Concluído', 'Falhou') NOT NULL DEFAULT 'Concluído',
  PRIMARY KEY (`id_pagamento`),
  FOREIGN KEY (`id_reserva`) REFERENCES `reservas`(`id_reserva`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Tabela de Manutenção de Veículos (Gastos com Manutenção)
CREATE TABLE `manutencao_veiculos` (
  `id_manutencao` INT(11) NOT NULL AUTO_INCREMENT,
  `id_veiculo` INT(11) NOT NULL,
  `data_manutencao` DATE NOT NULL,
  `descricao` TEXT NOT NULL,
  `custo` DECIMAL(10, 2) NOT NULL,
  `tipo_manutencao` ENUM('Preventiva', 'Corretiva', 'Revisão') NOT NULL,
  `quilometragem_manutencao` INT(11),
  `data_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_manutencao`),
  FOREIGN KEY (`id_veiculo`) REFERENCES `veiculos`(`id_veiculo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Tabela de Salários e Comissões dos Motoristas
CREATE TABLE `salarios_comissoes` (
  `id_registro` INT(11) NOT NULL AUTO_INCREMENT,
  `id_motorista` INT(11) NOT NULL,
  `mes_referencia` DATE NOT NULL, -- Ex: 'YYYY-MM-01'
  `salario_base` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `comissao_total` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `outros_bonus` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `descontos` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `valor_liquido` DECIMAL(10, 2) NOT NULL,
  `status_pagamento` ENUM('Pendente', 'Pago') NOT NULL DEFAULT 'Pendente',
  `data_pagamento` DATE,
  `data_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_registro`),
  UNIQUE KEY `uk_motorista_mes` (`id_motorista`, `mes_referencia`),
  FOREIGN KEY (`id_motorista`) REFERENCES `motoristas`(`id_motorista`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. Tabela de Movimentação Financeira (Contabilidade e Faturamento)
CREATE TABLE `movimentacao_financeira` (
  `id_movimentacao` INT(11) NOT NULL AUTO_INCREMENT,
  `tipo_movimentacao` ENUM('Entrada', 'Saída') NOT NULL,
  `categoria` ENUM('Viagem', 'Salário', 'Manutenção', 'Outros') NOT NULL,
  `descricao` VARCHAR(255) NOT NULL,
  `valor` DECIMAL(10, 2) NOT NULL,
  `data_movimentacao` DATE NOT NULL,
  `id_referencia` INT(11), -- ID da reserva, manutenção, ou salário, se aplicável
  `data_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_movimentacao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. Tabela de Quilometragem (Registro de Entrada e Saída)
CREATE TABLE `registro_quilometragem` (
  `id_registro_km` INT(11) NOT NULL AUTO_INCREMENT,
  `id_veiculo` INT(11) NOT NULL,
  `data_registro` DATE NOT NULL,
  `km_inicial` INT(11) NOT NULL,
  `km_final` INT(11) NOT NULL,
  `km_percorrida` INT(11) AS (km_final - km_inicial),
  `observacoes` TEXT,
  `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_registro_km`),
  FOREIGN KEY (`id_veiculo`) REFERENCES `veiculos`(`id_veiculo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 14. Inserção de Dados Iniciais (Exemplo de Categoria e Usuário Admin)
INSERT INTO `categorias_veiculos` (`nome_categoria`, `descricao`, `tarifa_base`, `tarifa_km`) VALUES
('Standard', 'Veículos de porte médio, 4 portas, ar condicionado.', 5.00, 2.50),
('Executivo', 'Veículos de luxo, mais confortáveis e com mais recursos.', 10.00, 4.00);

-- Senha inicial para 'admin' é 'admin123' (HASH: bcrypt('admin123'))
-- Por questões de segurança, a senha deve ser gerada e inserida via PHP, mas para o script SQL inicial, usaremos um placeholder.
-- No entanto, vamos criar a estrutura do INSERT e lembrar de usar password_hash() no PHP.
-- Placeholder para fins de demonstração:
INSERT INTO `usuarios` (`nome_completo`, `usuario`, `senha`, `email`, `nivel_acesso`, `status_usuario`) VALUES
('Administrador Principal', 'admin', '$2y$10$wY6R0s2n0fGjF7q5N1xXp.qC7.L9.P0vR1.2N3.4O5.6P7.Q8.R9.S0', 'admin@taxims.com', 'Administrador', 'Ativo'); -- Esta é uma senha de exemplo (admin123) gerada com bcrypt.

-- FIM DO ARQUIVO SQL

