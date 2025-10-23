<?php
// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'taxi_management_system');
define('DB_USER', 'root'); // Alterar em ambiente de produção
define('DB_PASS', '');     // Alterar em ambiente de produção

// Configurações do Sistema
define('SITE_TITLE', 'Sistema de Gestão de Táxi');
define('BASE_URL', 'http://localhost/taxi-management-system/'); // Ajustar conforme a instalação

// Níveis de Acesso
define('NIVEL_ADMIN', 'Administrador');
define('NIVEL_FINANCEIRO', 'Financeiro');
define('NIVEL_OPERACIONAL', 'Operacional');
define('NIVEL_MOTORISTA', 'Motorista');

// Outras configurações
date_default_timezone_set('America/Sao_Paulo'); // Fuso horário
session_start();
?>
