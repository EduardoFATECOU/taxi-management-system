<?php
$page_title = 'Gestão de Seguradoras';
require_once '../includes/header.php';
require_once '../includes/Crud.php';

$auth->requireAccess(NIVEL_OPERACIONAL);

$seguradoraCrud = new Seguradora();
$message = '';

// Processamento de Ações (Criar, Editar, Deletar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nome_seguradora' => trim($_POST['nome_seguradora']),
        'telefone' => trim($_POST['telefone']),
        'email' => trim($_POST['email']),
        'cnpj' => trim($_POST['cnpj']),
    ];

    $id_seguradora = $_POST['id_seguradora'] ?? null;

    if ($id_seguradora) {
        // Atualizar
        if ($seguradoraCrud->update($id_seguradora, $data)) {
            $message = '<div class="success-message">Seguradora atualizada com sucesso!</div>';
        } else {
            $message = '<div class="error-message">Erro ao atualizar seguradora.</div>';
        }
    } else {
        // Criar
        if ($seguradoraCrud->create($data)) {
            $message = '<div class="success-message">Seguradora cadastrada com sucesso!</div>';
        } else {
            $message = '<div class="error-message">Erro ao cadastrar seguradora.</div>';
        }
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    // Deletar
    if ($seguradoraCrud->delete($_GET['id'])) {
        $message = '<div class="success-message">Seguradora excluída com sucesso!</div>';
    } else {
        $message = '<div class="error-message">Erro ao excluir seguradora. Verifique se há veículos associados.</div>';
    }
}

// Carregar dados para edição
$seguradora_edit = null;
if (isset($_GET['id']) && !isset($_GET['action'])) {
    $seguradora_edit = $seguradoraCrud->getById($_GET['id']);
}

// Listar todas as seguradoras
$seguradoras = $seguradoraCrud->getAll('nome_seguradora ASC');

// Definição dos cabeçalhos para a tabela
$headers = [
    'id_seguradora' => 'ID',
    'nome_seguradora' => 'Nome',
    'cnpj' => 'CNPJ',
    'telefone' => 'Telefone',
    'email' => 'Email',
];
?>

<h2>Gestão de Seguradoras</h2>

<?php echo $message; ?>

<div class="form-section">
    <h3><?php echo $seguradora_edit ? 'Editar Seguradora' : 'Nova Seguradora'; ?></h3>
    <form method="POST" action="seguradoras.php">
        <?php if ($seguradora_edit): ?>
            <input type="hidden" name="id_seguradora" value="<?php echo $seguradora_edit['id_seguradora']; ?>">
        <?php endif; ?>

        <label for="nome_seguradora">Nome da Seguradora:</label>
        <input type="text" id="nome_seguradora" name="nome_seguradora" value="<?php echo $seguradora_edit['nome_seguradora'] ?? ''; ?>" required>

        <label for="cnpj">CNPJ:</label>
        <input type="text" id="cnpj" name="cnpj" value="<?php echo $seguradora_edit['cnpj'] ?? ''; ?>">

        <label for="telefone">Telefone:</label>
        <input type="text" id="telefone" name="telefone" value="<?php echo $seguradora_edit['telefone'] ?? ''; ?>">

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo $seguradora_edit['email'] ?? ''; ?>">

        <button type="submit"><?php echo $seguradora_edit ? 'Salvar Alterações' : 'Cadastrar Seguradora'; ?></button>
        <?php if ($seguradora_edit): ?>
            <a href="seguradoras.php" class="btn btn-secondary">Cancelar Edição</a>
        <?php endif; ?>
    </form>
</div>

<div class="list-section">
    <h3>Lista de Seguradoras</h3>
    <?php echo HtmlHelper::createListTable($seguradoras, $headers, 'id_seguradora', 'seguradoras.php'); ?>
</div>

<?php
require_once '../includes/footer.php';
?>
