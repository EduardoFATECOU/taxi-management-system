<?php
require_once '../includes/config.php';
require_once '../includes/Auth.php';

$auth = new Auth();
$auth->logout();
// O método logout() já redireciona para a página de login.
?>
