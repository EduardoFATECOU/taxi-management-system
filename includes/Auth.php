<?php
require_once 'Database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Tenta logar um usuário no sistema.
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function login($username, $password) {
        $sql = "SELECT * FROM usuarios WHERE usuario = :usuario AND status_usuario = 'Ativo'";
        $user = $this->db->fetchOne($sql, ['usuario' => $username]);

        if ($user && password_verify($password, $user['senha'])) {
            // Login bem-sucedido
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['username'] = $user['usuario'];
            $_SESSION['nome_completo'] = $user['nome_completo'];
            $_SESSION['nivel_acesso'] = $user['nivel_acesso'];
            $_SESSION['logged_in'] = true;
            return true;
        }

        return false;
    }

    /**
     * Verifica se o usuário está logado.
     * @return bool
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Verifica se o usuário logado tem um nível de acesso específico.
     * @param string $required_level
     * @return bool
     */
    public function hasAccess($required_level) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        $current_level = $_SESSION['nivel_acesso'];
        
        // Lógica de hierarquia simples: Administrador > Financeiro > Operacional > Motorista
        $levels = [
            NIVEL_ADMIN => 4,
            NIVEL_FINANCEIRO => 3,
            NIVEL_OPERACIONAL => 2,
            NIVEL_MOTORISTA => 1
        ];

        return isset($levels[$current_level]) && isset($levels[$required_level]) && $levels[$current_level] >= $levels[$required_level];
    }

    /**
     * Redireciona o usuário para a página de login se não estiver logado.
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: ' . BASE_URL . 'app/login.php');
            exit;
        }
    }

    /**
     * Redireciona o usuário para uma página de acesso negado se não tiver o nível mínimo.
     * @param string $required_level
     */
    public function requireAccess($required_level) {
        if (!$this->hasAccess($required_level)) {
            header('Location: ' . BASE_URL . 'app/acesso_negado.php');
            exit;
        }
    }

    /**
     * Desloga o usuário.
     */
    public function logout() {
        session_unset();
        session_destroy();
        // Redireciona para a página de login
        header('Location: ' . BASE_URL . 'app/login.php');
        exit;
    }
}
?>
