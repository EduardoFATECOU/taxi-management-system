<?php
$page_title = 'Atualização de Perfil';
require_once '../includes/header.php';
require_once '../includes/Crud.php';

$usuarioCrud = new UsuarioSistema();
$message = '';

$id_usuario = $_SESSION['user_id'];
$usuario_edit = $usuarioCrud->getById($id_usuario);

// Processamento de Ações (Atualizar Perfil)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nome_completo' => trim($_POST['nome_completo']),
        'usuario' => trim($_POST['usuario']),
        'email' => trim($_POST['email']),
    ];
    
    // Altera a senha apenas se o campo não estiver vazio
    if (!empty($_POST['senha'])) {
        $data['senha'] = $_POST['senha'];
    }

    if ($usuarioCrud->update($id_usuario, $data)) {
        // Atualiza as variáveis de sessão
        $_SESSION['username'] = $data['usuario'];
        $_SESSION['nome_completo'] = $data['nome_completo'];
        
        // Recarrega os dados do usuário para o formulário
        $usuario_edit = $usuarioCrud->getById($id_usuario);
        
        $message = '<div class="success-message">Seu perfil foi atualizado com sucesso!</div>';
    } else {
        $message = '<div class="error-message">Erro ao atualizar seu perfil. Verifique se o nome de usuário ou e-mail já existem.</div>';
    }
}
?>

<h2>Atualização de Perfil</h2>

<?php echo $message; ?>

<div class="form-section">
    <h3>Meus Dados</h3>
    <form method="POST" action="perfil.php">
        <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">

        <label for="nome_completo">Nome Completo:</label>
        <input type="text" id="nome_completo" name="nome_completo" value="<?php echo $usuario_edit['nome_completo'] ?? ''; ?>" required>

        <label for="usuario">Nome de Usuário:</label>
        <input type="text" id="usuario" name="usuario" value="<?php echo $usuario_edit['usuario'] ?? ''; ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo $usuario_edit['email'] ?? ''; ?>">

        <label for="nivel_acesso">Nível de Acesso:</label>
        <input type="text" id="nivel_acesso" name="nivel_acesso" value="<?php echo $usuario_edit['nivel_acesso'] ?? ''; ?>" disabled>
        
        <hr>
        
        <h3>Alterar Senha</h3>
        <label for="senha">Nova Senha (Deixe em branco para não alterar):</label>
        <input type="password" id="senha" name="senha" value="" placeholder="********">

        <button type="submit">Salvar Alterações</button>
    </form>
</div>

<?php
require_once '../includes/footer.php';
?>
