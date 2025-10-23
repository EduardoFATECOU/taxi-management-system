<?php
$page_title = 'Gestão de Categorias de Veículos';
require_once '../includes/header.php';
require_once '../includes/Crud.php';

$auth->requireAccess(NIVEL_OPERACIONAL);

$categoriaCrud = new CategoriaVeiculo();
$message = '';

// Processamento de Ações (Criar, Editar, Deletar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nome_categoria' => trim($_POST['nome_categoria']),
        'descricao' => trim($_POST['descricao']),
        'tarifa_base' => floatval($_POST['tarifa_base']),
        'tarifa_km' => floatval($_POST['tarifa_km']),
    ];

    $id_categoria = $_POST['id_categoria'] ?? null;

    if ($id_categoria) {
        // Atualizar
        if ($categoriaCrud->update($id_categoria, $data)) {
            $message = '<div class="success-message">Categoria atualizada com sucesso!</div>';
        } else {
            $message = '<div class="error-message">Erro ao atualizar categoria.</div>';
        }
    } else {
        // Criar
        if ($categoriaCrud->create($data)) {
            $message = '<div class="success-message">Categoria cadastrada com sucesso!</div>';
        } else {
            $message = '<div class="error-message">Erro ao cadastrar categoria.</div>';
        }
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    // Deletar
    if ($categoriaCrud->delete($_GET['id'])) {
        $message = '<div class="success-message">Categoria excluída com sucesso!</div>';
    } else {
        $message = '<div class="error-message">Erro ao excluir categoria. Verifique se há veículos associados.</div>';
    }
}

// Carregar dados para edição
$categoria_edit = null;
if (isset($_GET['id']) && !isset($_GET['action'])) {
    $categoria_edit = $categoriaCrud->getById($_GET['id']);
}

// Listar todas as categorias
$categorias = $categoriaCrud->getAll('nome_categoria ASC');

// Definição dos cabeçalhos para a tabela
$headers = [
    'id_categoria' => 'ID',
    'nome_categoria' => 'Nome',
    'tarifa_base' => 'Tarifa Base (R$)',
    'tarifa_km' => 'Tarifa/Km (R$)',
    'descricao' => 'Descrição',
];
?>

<h2>Gestão de Categorias de Veículos</h2>

<?php echo $message; ?>

<div class="form-section">
    <h3><?php echo $categoria_edit ? 'Editar Categoria' : 'Nova Categoria'; ?></h3>
    <form method="POST" action="categorias.php">
        <?php if ($categoria_edit): ?>
            <input type="hidden" name="id_categoria" value="<?php echo $categoria_edit['id_categoria']; ?>">
        <?php endif; ?>

        <label for="nome_categoria">Nome da Categoria:</label>
        <input type="text" id="nome_categoria" name="nome_categoria" value="<?php echo $categoria_edit['nome_categoria'] ?? ''; ?>" required>

        <label for="tarifa_base">Tarifa Base (R$):</label>
        <input type="number" step="0.01" id="tarifa_base" name="tarifa_base" value="<?php echo $categoria_edit['tarifa_base'] ?? '0.00'; ?>" required>

        <label for="tarifa_km">Tarifa por Km (R$):</label>
        <input type="number" step="0.01" id="tarifa_km" name="tarifa_km" value="<?php echo $categoria_edit['tarifa_km'] ?? '0.00'; ?>" required>

        <label for="descricao">Descrição:</label>
        <textarea id="descricao" name="descricao"><?php echo $categoria_edit['descricao'] ?? ''; ?></textarea>

        <button type="submit"><?php echo $categoria_edit ? 'Salvar Alterações' : 'Cadastrar Categoria'; ?></button>
        <?php if ($categoria_edit): ?>
            <a href="categorias.php" class="btn btn-secondary">Cancelar Edição</a>
        <?php endif; ?>
    </form>
</div>

<div class="list-section">
    <h3>Lista de Categorias</h3>
    <?php 
    // Formatação dos valores monetários para exibição
    $categorias_formatadas = array_map(function($c) {
        $c['tarifa_base'] = number_format($c['tarifa_base'], 2, ',', '.');
        $c['tarifa_km'] = number_format($c['tarifa_km'], 2, ',', '.');
        return $c;
    }, $categorias);
    
    echo HtmlHelper::createListTable($categorias_formatadas, $headers, 'id_categoria', 'categorias.php'); 
    ?>
</div>

<?php
require_once '../includes/footer.php';
?>
