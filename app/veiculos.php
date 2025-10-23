<?php
$page_title = 'Gestão de Frota de Táxis';
require_once '../includes/header.php';
require_once '../includes/Crud.php';

$auth->requireAccess(NIVEL_OPERACIONAL);

$veiculoCrud = new Veiculo();
$categoriaCrud = new CategoriaVeiculo();
$seguradoraCrud = new Seguradora();
$message = '';

// Processamento de Ações (Criar, Editar, Deletar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'placa' => strtoupper(trim($_POST['placa'])),
        'marca' => trim($_POST['marca']),
        'modelo' => trim($_POST['modelo']),
        'ano' => trim($_POST['ano']),
        'cor' => trim($_POST['cor']),
        'id_categoria' => trim($_POST['id_categoria']),
        'id_seguradora' => empty($_POST['id_seguradora']) ? null : trim($_POST['id_seguradora']),
        'apolice_numero' => trim($_POST['apolice_numero']),
        'data_vencimento_apolice' => trim($_POST['data_vencimento_apolice']),
        'status_veiculo' => trim($_POST['status_veiculo']),
        'quilometragem_atual' => trim($_POST['quilometragem_atual']),
    ];

    $id_veiculo = $_POST['id_veiculo'] ?? null;

    if ($id_veiculo) {
        // Atualizar
        if ($veiculoCrud->update($id_veiculo, $data)) {
            $message = '<div class="success-message">Veículo atualizado com sucesso!</div>';
        } else {
            $message = '<div class="error-message">Erro ao atualizar veículo.</div>';
        }
    } else {
        // Criar
        if ($veiculoCrud->create($data)) {
            $message = '<div class="success-message">Veículo cadastrado com sucesso!</div>';
        } else {
            $message = '<div class="error-message">Erro ao cadastrar veículo.</div>';
        }
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    // Deletar
    if ($veiculoCrud->delete($_GET['id'])) {
        $message = '<div class="success-message">Veículo excluído com sucesso!</div>';
    } else {
        $message = '<div class="error-message">Erro ao excluir veículo. Verifique se há motoristas ou reservas associadas.</div>';
    }
}

// Carregar dados para edição
$veiculo_edit = null;
if (isset($_GET['id']) && !isset($_GET['action'])) {
    $veiculo_edit = $veiculoCrud->getById($_GET['id']);
}

// Listar dados auxiliares
$categorias = $categoriaCrud->getAll('nome_categoria ASC');
$seguradoras = $seguradoraCrud->getAll('nome_seguradora ASC');

// Listar todos os veículos (com nome da categoria)
$veiculos = $veiculoCrud->getAllWithCategory();

// Definição dos cabeçalhos para a tabela
$headers = [
    'id_veiculo' => 'ID',
    'placa' => 'Placa',
    'marca' => 'Marca',
    'modelo' => 'Modelo',
    'nome_categoria' => 'Categoria',
    'status_veiculo' => 'Status',
    'quilometragem_atual' => 'KM Atual',
];
?>

<h2>Gestão de Frota de Táxis</h2>

<?php echo $message; ?>

<div class="form-section">
    <h3><?php echo $veiculo_edit ? 'Editar Veículo' : 'Novo Veículo'; ?></h3>
    <form method="POST" action="veiculos.php">
        <?php if ($veiculo_edit): ?>
            <input type="hidden" name="id_veiculo" value="<?php echo $veiculo_edit['id_veiculo']; ?>">
        <?php endif; ?>

        <label for="placa">Placa:</label>
        <input type="text" id="placa" name="placa" value="<?php echo $veiculo_edit['placa'] ?? ''; ?>" required>

        <label for="marca">Marca:</label>
        <input type="text" id="marca" name="marca" value="<?php echo $veiculo_edit['marca'] ?? ''; ?>" required>

        <label for="modelo">Modelo:</label>
        <input type="text" id="modelo" name="modelo" value="<?php echo $veiculo_edit['modelo'] ?? ''; ?>" required>

        <label for="ano">Ano:</label>
        <input type="number" id="ano" name="ano" value="<?php echo $veiculo_edit['ano'] ?? date('Y'); ?>" required>

        <label for="cor">Cor:</label>
        <input type="text" id="cor" name="cor" value="<?php echo $veiculo_edit['cor'] ?? ''; ?>">

        <label for="id_categoria">Categoria:</label>
        <select id="id_categoria" name="id_categoria" required>
            <option value="">-- Selecione --</option>
            <?php foreach ($categorias as $categoria): ?>
                <option value="<?php echo $categoria['id_categoria']; ?>" 
                    <?php echo ($veiculo_edit['id_categoria'] ?? '') == $categoria['id_categoria'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($categoria['nome_categoria']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="id_seguradora">Seguradora (Opcional):</label>
        <select id="id_seguradora" name="id_seguradora">
            <option value="">-- Nenhuma --</option>
            <?php foreach ($seguradoras as $seguradora): ?>
                <option value="<?php echo $seguradora['id_seguradora']; ?>" 
                    <?php echo ($veiculo_edit['id_seguradora'] ?? '') == $seguradora['id_seguradora'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($seguradora['nome_seguradora']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <label for="apolice_numero">Número da Apólice:</label>
        <input type="text" id="apolice_numero" name="apolice_numero" value="<?php echo $veiculo_edit['apolice_numero'] ?? ''; ?>">

        <label for="data_vencimento_apolice">Vencimento da Apólice:</label>
        <input type="date" id="data_vencimento_apolice" name="data_vencimento_apolice" value="<?php echo $veiculo_edit['data_vencimento_apolice'] ?? ''; ?>">

        <label for="status_veiculo">Status:</label>
        <select id="status_veiculo" name="status_veiculo" required>
            <option value="Disponível" <?php echo ($veiculo_edit['status_veiculo'] ?? '') == 'Disponível' ? 'selected' : ''; ?>>Disponível</option>
            <option value="Em Corrida" <?php echo ($veiculo_edit['status_veiculo'] ?? '') == 'Em Corrida' ? 'selected' : ''; ?>>Em Corrida</option>
            <option value="Em Manutenção" <?php echo ($veiculo_edit['status_veiculo'] ?? '') == 'Em Manutenção' ? 'selected' : ''; ?>>Em Manutenção</option>
            <option value="Inativo" <?php echo ($veiculo_edit['status_veiculo'] ?? '') == 'Inativo' ? 'selected' : ''; ?>>Inativo</option>
        </select>

        <label for="quilometragem_atual">Quilometragem Atual (KM):</label>
        <input type="number" id="quilometragem_atual" name="quilometragem_atual" value="<?php echo $veiculo_edit['quilometragem_atual'] ?? '0'; ?>" required>

        <button type="submit"><?php echo $veiculo_edit ? 'Salvar Alterações' : 'Cadastrar Veículo'; ?></button>
        <?php if ($veiculo_edit): ?>
            <a href="veiculos.php" class="btn btn-secondary">Cancelar Edição</a>
        <?php endif; ?>
    </form>
</div>

<div class="list-section">
    <h3>Lista de Veículos</h3>
    <?php echo HtmlHelper::createListTable($veiculos, $headers, 'id_veiculo', 'veiculos.php'); ?>
</div>

<?php
require_once '../includes/footer.php';
?>
