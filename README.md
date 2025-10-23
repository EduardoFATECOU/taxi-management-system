# Sistema de Gestão de Táxi

**Versão:** 1.0
**Tecnologias:** PHP, CSS, PDO, MariaDB

## 1. Visão Geral do Projeto

Este projeto consiste em um sistema de gestão de táxi completo, desenvolvido em PHP com foco em segurança (utilizando PDO para acesso ao banco de dados) e organização (utilizando MariaDB para armazenamento). O sistema abrange todas as funcionalidades essenciais para a administração de uma frota de táxis, desde o cadastro de clientes e motoristas até o controle financeiro detalhado e a geração de relatórios.

## 2. Funcionalidades Implementadas

O sistema é dividido em módulos de gestão acessíveis através de um **Painel de Administração** com controle de acesso por nível de usuário.

| Módulo | Funcionalidades |
| :--- | :--- |
| **Painel Administrativo** | Dashboard com visão geral (reservas pendentes, total de clientes, frota, saldo). |
| **Gestão de Usuários** | Cadastro de usuários do sistema (`Administrador`, `Financeiro`, `Operacional`, `Motorista`). |
| **Gestão de Clientes** | Cadastro e gerenciamento de clientes. |
| **Gestão de Motoristas** | Cadastro, gestão de status (Disponível, Em Corrida, etc.) e associação de veículo. |
| **Gestão de Veículos** | Cadastro de táxis, categorias, seguradoras, apólices e status (Disponível, Manutenção). |
| **Reservas e Corridas** | **Reserva de táxi**, **Histórico de reservas**, **Cancelar reserva**, **Gerenciar reserva**, **Status da viagem** (Pendente, Confirmada, Em Andamento, Concluída, Cancelada). |
| **Gestão Financeira** | **Gestão de pagamentos** (Viagens), **Gestão do salários e comissões** dos motoristas, **Entrada e saída de valores da empresa** (Contabilidade e Faturamento), **Gastos com manutenção dos veículos**. |
| **Gestão de Quilometragem** | Registro de **quilometragem** inicial e final por veículo. |
| **Relatórios e Análises** | **Geração completa de relatórios** (Faturamento, Corridas por Motorista, Gastos com Manutenção). |

## 3. Estrutura de Diretórios

A arquitetura do projeto segue um padrão simples e organizado, facilitando a manutenção e a expansão:

```
taxi-management-system/
├── app/                      # Arquivos de aplicação (páginas do painel, login, etc.)
│   ├── dashboard.php
│   ├── login.php
│   ├── reservas.php
│   └── ... (demais módulos)
├── assets/
│   ├── css/
│   │   └── style.css         # Estilos CSS
│   └── js/                   # Scripts Javascript (vazio, mas pronto para uso)
├── includes/                 # Classes e arquivos de configuração
│   ├── config.php            # Configurações do sistema e banco de dados
│   ├── Database.php          # Classe de conexão PDO
│   ├── Auth.php              # Classe de Autenticação e Controle de Acesso
│   ├── Crud.php              # Classe Base para CRUD e Classes de Entidades
│   ├── Financeiro.php        # Classes de gestão financeira
│   ├── Reserva.php           # Classe de gestão de reservas
│   ├── Relatorio.php         # Classe de geração de relatórios
│   └── ... (demais includes)
├── public/
│   └── index.php             # Ponto de entrada (redireciona para login)
└── db_schema.sql             # Arquivo com o script de criação do banco de dados
```

## 4. Instruções de Instalação (XAMPP/WAMP no Windows)

Para instalar e rodar o sistema em seu ambiente local (Windows), siga os passos abaixo:

### Pré-requisitos

1.  **XAMPP** ou **WAMP Server** instalado (inclui Apache, PHP e MariaDB/MySQL).
2.  Um navegador web moderno.

### Passo a Passo

#### 4.1. Configuração do Projeto

1.  **Baixe o Projeto:** Copie o conteúdo da pasta `taxi-management-system` para o diretório de projetos web do seu servidor local:
    *   **XAMPP:** `C:\xampp\htdocs\`
    *   **WAMP:** `C:\wamp64\www\`
    *   O caminho final deve ser, por exemplo, `C:\xampp\htdocs\taxi-management-system`.

2.  **Ajuste a URL Base:** Edite o arquivo `taxi-management-system/includes/config.php` e ajuste a constante `BASE_URL` para o caminho correto:

    ```php
    // Exemplo para XAMPP
    define('BASE_URL', 'http://localhost/taxi-management-system/');
    ```

#### 4.2. Configuração do Banco de Dados (MariaDB/MySQL)

1.  **Acesse o phpMyAdmin:** Abra seu navegador e acesse o painel de administração do banco de dados (geralmente `http://localhost/phpmyadmin`).

2.  **Crie o Banco de Dados:**
    *   Clique na aba **SQL**.
    *   Copie e cole o conteúdo do arquivo `db_schema.sql` na caixa de texto.
    *   **Importante:** O script `db_schema.sql` já contém o comando `CREATE DATABASE IF NOT EXISTS \`taxi_management_system\`` e o comando `USE \`taxi_management_system\``, além da criação de todas as tabelas e a inserção do usuário administrador inicial.
    *   Clique em **Executar**.

3.  **Verifique as Credenciais:** O arquivo `includes/config.php` está configurado com as credenciais padrão do XAMPP/WAMP:

    ```php
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'taxi_management_system');
    define('DB_USER', 'root'); // Usuário padrão
    define('DB_PASS', '');     // Senha padrão (vazia)
    ```
    *   Se você alterou as credenciais do seu MariaDB/MySQL, ajuste o arquivo `config.php` conforme necessário.

#### 4.3. Acesso ao Sistema

1.  **Abra o Navegador:** Acesse a URL do projeto: `http://localhost/taxi-management-system/`.
2.  Você será redirecionado automaticamente para a tela de login.

### Credenciais Iniciais

Use as seguintes credenciais para acessar o Painel de Administração:

| Campo | Valor |
| :--- | :--- |
| **Usuário** | `admin` |
| **Senha** | `admin123` |

**Nota:** A senha `admin123` foi armazenada no banco de dados como um hash seguro (bcrypt). Recomenda-se que o administrador altere a senha imediatamente após o primeiro login.

## 5. Estrutura do Banco de Dados

O banco de dados `taxi_management_system` é composto pelas seguintes tabelas, garantindo a integridade e o relacionamento dos dados:

| Tabela | Descrição | Chave Primária | Relacionamentos (FK) |
| :--- | :--- | :--- | :--- |
| `usuarios` | Usuários do sistema (Admin, Financeiro, etc.) | `id_usuario` | - |
| `clientes` | Cadastro de clientes | `id_cliente` | - |
| `motoristas` | Cadastro e status dos motoristas | `id_motorista` | `id_veiculo_atual` (Veículos) |
| `seguradoras` | Cadastro das seguradoras | `id_seguradora` | - |
| `categorias_veiculos` | Tipos de veículos e tarifas | `id_categoria` | - |
| `veiculos` | Frota de táxis | `id_veiculo` | `id_categoria`, `id_seguradora` (Categorias, Seguradoras) |
| `reservas` | Registro de corridas/viagens | `id_reserva` | `id_cliente`, `id_motorista`, `id_veiculo` (Clientes, Motoristas, Veículos) |
| `pagamentos` | Registro de pagamentos das viagens | `id_pagamento` | `id_reserva` (Reservas) |
| `manutencao_veiculos` | Gastos com manutenção dos veículos | `id_manutencao` | `id_veiculo` (Veículos) |
| `salarios_comissoes` | Pagamentos e comissões dos motoristas | `id_registro` | `id_motorista` (Motoristas) |
| `movimentacao_financeira` | Contabilidade (Entradas e Saídas) | `id_movimentacao` | Referência a pagamentos, salários, manutenção |
| `registro_quilometragem` | Controle de KM dos veículos | `id_registro_km` | `id_veiculo` (Veículos) |

