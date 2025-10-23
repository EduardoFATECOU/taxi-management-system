<?php
$page_title = 'Gestão de Clientes';
require_once '../includes/header.php';
require_once '../includes/Crud.php';

$auth->requireAccess(NIVEL_OPERACIONAL);

$clienteCrud = new Cliente();
$message = '';

// Processamento de Ações (Criar, Editar, Deletar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nome' => trim($_POST['nome']),
        'telefone' => trim($_POST['telefone']),
        'email' => trim($_POST['email']),
        'cpf' => trim($_POST['cpf']),
        'endereco' => trim($_POST['endereco']),
    ];

    if (isset($_POST['id_cliente']) && !empty($_POST['id_cliente'])) {
        // Atualizar
        if ($clienteCrud->update($_POST['id_cliente'], $data)) {
            $message = '<div class="success-message">Cliente atualizado com sucesso!</div>';
        } else {
            $message = '<div class="error-message">Erro ao atualizar cliente.</div>';
        }
    } else {
        // Criar
        if ($clienteCrud->create($data)) {
            $message = '<div class="success-message">Cliente cadastrado com sucesso!</div>';
        } else {
            $message = '<div class="error-message">Erro ao cadastrar cliente.</div>';
        }
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    // Deletar
    if ($clienteCrud->delete($_GET['id'])) {
        $message = '<div class="success-message">Cliente excluído com sucesso!</div>';
    } else {
        $message = '<div class="error-message">Erro ao excluir cliente. Verifique se há reservas associadas.</div>';
    }
}

// Carregar dados para edição
$cliente_edit = null;
if (isset($_GET['id']) && !isset($_GET['action'])) {
    $cliente_edit = $clienteCrud->getById($_GET['id']);
}

// Listar todos os clientes
$clientes = $clienteCrud->getAll('nome ASC');

// Definição dos cabeçalhos para a tabela
$headers = [
    'id_cliente' => 'ID',
    'nome' => 'Nome',
    'telefone' => 'Telefone',
    'email' => 'Email',
    'cpf' => 'CPF',
    'endereco' => 'Endereço',
];
?>

<h2>Gestão de Clientes</h2>

<?php echo $message; ?>

<div class="form-section">
    <h3><?php echo $cliente_edit ? 'Editar Cliente' : 'Novo Cliente'; ?></h3>
    <form method="POST" action="clientes.php">
        <?php if ($cliente_edit): ?>
            <input type="hidden" name="id_cliente" value="<?php echo $cliente_edit['id_cliente']; ?>">
        <?php endif; ?>

        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" value="<?php echo $cliente_edit['nome'] ?? ''; ?>" required>

        <label for="telefone">Telefone:</label>
        <input type="text" id="telefone" name="telefone" value="<?php echo $cliente_edit['telefone'] ?? ''; ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo $cliente_edit['email'] ?? ''; ?>">

        <label for="cpf">CPF:</label>
        <input type="text" id="cpf" name="cpf" value="<?php echo $cliente_edit['cpf'] ?? ''; ?>">

        <label for="endereco">Endereço:</label>
        <input type="text" id="endereco" name="endereco" value="<?php echo $cliente_edit['endereco'] ?? ''; ?>">

        <button type="submit"><?php echo $cliente_edit ? 'Salvar Alterações' : 'Cadastrar Cliente'; ?></button>
        <?php if ($cliente_edit): ?>
            <a href="clientes.php" class="btn btn-secondary">Cancelar Edição</a>
        <?php endif; ?>
    </form>
</div>

<div class="list-section">
    <h3>Lista de Clientes</h3>
    <?php echo HtmlHelper::createListTable($clientes, $headers, 'id_cliente', 'clientes.php'); ?>
</div>

<?php
require_once '../includes/footer.php';
?>
