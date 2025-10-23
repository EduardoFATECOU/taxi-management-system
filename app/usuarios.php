<?php
$page_title = 'Gestão de Usuários do Sistema';
require_once '../includes/header.php';
require_once '../includes/Crud.php';

$auth->requireAccess(NIVEL_ADMIN); // Apenas Administrador pode gerenciar usuários

$usuarioCrud = new UsuarioSistema();
$message = '';

// Processamento de Ações (Criar, Editar, Deletar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nome_completo' => trim($_POST['nome_completo']),
        'usuario' => trim($_POST['usuario']),
        'email' => trim($_POST['email']),
        'nivel_acesso' => trim($_POST['nivel_acesso']),
        'status_usuario' => trim($_POST['status_usuario']),
    ];
    
    // Senha é opcional na edição, mas obrigatória na criação
    if (!empty($_POST['senha'])) {
        $data['senha'] = $_POST['senha'];
    }

    $id_usuario = $_POST['id_usuario'] ?? null;

    if ($id_usuario) {
        // Atualizar
        if ($usuarioCrud->update($id_usuario, $data)) {
            $message = '<div class="success-message">Usuário atualizado com sucesso!</div>';
        } else {
            $message = '<div class="error-message">Erro ao atualizar usuário.</div>';
        }
    } else {
        // Criar
        if (empty($data['senha'])) {
             $message = '<div class="error-message">A senha é obrigatória para o novo usuário.</div>';
        } elseif ($usuarioCrud->create($data)) {
            $message = '<div class="success-message">Usuário cadastrado com sucesso!</div>';
        } else {
            $message = '<div class="error-message">Erro ao cadastrar usuário. Verifique se o nome de usuário ou e-mail já existem.</div>';
        }
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    // Deletar
    if ($_GET['id'] == $_SESSION['user_id']) {
        $message = '<div class="error-message">Você não pode excluir seu próprio usuário.</div>';
    } elseif ($usuarioCrud->delete($_GET['id'])) {
        $message = '<div class="success-message">Usuário excluído com sucesso!</div>';
    } else {
        $message = '<div class="error-message">Erro ao excluir usuário.</div>';
    }
}

// Carregar dados para edição
$usuario_edit = null;
if (isset($_GET['id']) && !isset($_GET['action'])) {
    $usuario_edit = $usuarioCrud->getById($_GET['id']);
}

// Listar todos os usuários
$usuarios = $usuarioCrud->getAll('nome_completo ASC');

// Definição dos cabeçalhos para a tabela
$headers = [
    'id_usuario' => 'ID',
    'nome_completo' => 'Nome',
    'usuario' => 'Usuário',
    'email' => 'Email',
    'nivel_acesso' => 'Nível',
    'status_usuario' => 'Status',
];
?>

<h2>Gestão de Usuários do Sistema</h2>

<?php echo $message; ?>

<div class="form-section">
    <h3><?php echo $usuario_edit ? 'Editar Usuário' : 'Novo Usuário'; ?></h3>
    <form method="POST" action="usuarios.php">
        <?php if ($usuario_edit): ?>
            <input type="hidden" name="id_usuario" value="<?php echo $usuario_edit['id_usuario']; ?>">
        <?php endif; ?>

        <label for="nome_completo">Nome Completo:</label>
        <input type="text" id="nome_completo" name="nome_completo" value="<?php echo $usuario_edit['nome_completo'] ?? ''; ?>" required>

        <label for="usuario">Nome de Usuário:</label>
        <input type="text" id="usuario" name="usuario" value="<?php echo $usuario_edit['usuario'] ?? ''; ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo $usuario_edit['email'] ?? ''; ?>">

        <label for="senha">Senha (<?php echo $usuario_edit ? 'Deixe em branco para não alterar' : 'Obrigatória'; ?>):</label>
        <input type="password" id="senha" name="senha" value="">

        <label for="nivel_acesso">Nível de Acesso:</label>
        <select id="nivel_acesso" name="nivel_acesso" required>
            <option value="<?php echo NIVEL_ADMIN; ?>" <?php echo ($usuario_edit['nivel_acesso'] ?? '') == NIVEL_ADMIN ? 'selected' : ''; ?>>Administrador</option>
            <option value="<?php echo NIVEL_FINANCEIRO; ?>" <?php echo ($usuario_edit['nivel_acesso'] ?? '') == NIVEL_FINANCEIRO ? 'selected' : ''; ?>>Financeiro</option>
            <option value="<?php echo NIVEL_OPERACIONAL; ?>" <?php echo ($usuario_edit['nivel_acesso'] ?? '') == NIVEL_OPERACIONAL ? 'selected' : ''; ?>>Operacional</option>
            <option value="<?php echo NIVEL_MOTORISTA; ?>" <?php echo ($usuario_edit['nivel_acesso'] ?? '') == NIVEL_MOTORISTA ? 'selected' : ''; ?>>Motorista</option>
        </select>

        <label for="status_usuario">Status:</label>
        <select id="status_usuario" name="status_usuario" required>
            <option value="Ativo" <?php echo ($usuario_edit['status_usuario'] ?? '') == 'Ativo' ? 'selected' : ''; ?>>Ativo</option>
            <option value="Inativo" <?php echo ($usuario_edit['status_usuario'] ?? '') == 'Inativo' ? 'selected' : ''; ?>>Inativo</option>
        </select>

        <button type="submit"><?php echo $usuario_edit ? 'Salvar Alterações' : 'Cadastrar Usuário'; ?></button>
        <?php if ($usuario_edit): ?>
            <a href="usuarios.php" class="btn btn-secondary">Cancelar Edição</a>
        <?php endif; ?>
    </form>
</div>

<div class="list-section">
    <h3>Lista de Usuários</h3>
    <?php echo HtmlHelper::createListTable($usuarios, $headers, 'id_usuario', 'usuarios.php'); ?>
</div>

<?php
require_once '../includes/footer.php';
?>
