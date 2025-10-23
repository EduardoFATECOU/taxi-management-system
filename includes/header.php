<?php
// Requerimentos básicos para todas as páginas do painel
require_once '../includes/config.php';
require_once '../includes/Auth.php';

$auth = new Auth();
$auth->requireLogin(); // Garante que o usuário esteja logado

// Variáveis de sessão
$user_name = $_SESSION['nome_completo'] ?? $_SESSION['username'];
$user_level = $_SESSION['nivel_acesso'] ?? 'Desconhecido';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_TITLE; ?> - <?php echo $page_title ?? 'Dashboard'; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Será criado na fase 11 -->
    <style>
        /* Estilos básicos para o layout do painel */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            position: fixed;
            height: 100%;
            padding-top: 20px;
        }
        .sidebar h3 {
            text-align: center;
            margin-bottom: 30px;
        }
        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }
        .sidebar ul li a {
            display: block;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
        }
        .sidebar ul li a:hover {
            background-color: #34495e;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .topbar {
            background-color: #ecf0f1;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #bdc3c7;
        }
        .user-info a {
            text-decoration: none;
            color: #333;
            margin-left: 15px;
        }
        .user-info span {
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h3><?php echo SITE_TITLE; ?></h3>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <?php if ($auth->hasAccess(NIVEL_OPERACIONAL)): ?>
            <li><a href="reservas.php">Reservas/Corridas</a></li>
            <li><a href="clientes.php">Clientes</a></li>
            <li><a href="motoristas.php">Motoristas</a></li>
            <li><a href="veiculos.php">Frota de Táxis</a></li>
            <li><a href="categorias.php">Categorias de Veículos</a></li>
            <li><a href="seguradoras.php">Seguradoras</a></li>
        <?php endif; ?>
        <?php if ($auth->hasAccess(NIVEL_FINANCEIRO)): ?>
            <li><a href="pagamentos.php">Pagamentos</a></li>
            <li><a href="financeiro.php">Contabilidade/Faturamento</a></li>
            <li><a href="salarios.php">Salários/Comissões</a></li>
            <li><a href="manutencao.php">Manutenção/Gastos</a></li>
            <li><a href="quilometragem.php">Quilometragem</a></li>
        <?php endif; ?>
        <?php if ($auth->hasAccess(NIVEL_ADMIN)): ?>
            <li><a href="usuarios.php">Usuários do Sistema</a></li>
        <?php endif; ?>
        <?php if ($auth->hasAccess(NIVEL_OPERACIONAL)): ?>
            <li><a href="relatorios.php">Relatórios</a></li>
        <?php endif; ?>
        <li><a href="perfil.php">Meu Perfil</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="topbar">
        <h1><?php echo $page_title ?? 'Dashboard'; ?></h1>
        <div class="user-info">
            <span>Olá, <?php echo htmlspecialchars($user_name); ?> (<?php echo htmlspecialchars($user_level); ?>)</span>
            <a href="logout.php">Sair</a>
        </div>
    </div>
    <div class="content">
        <!-- Conteúdo específico da página será inserido aqui -->

