<?php
$page_title = 'Gestão de Motoristas';
require_once '../includes/header.php';
require_once '../includes/Crud.php';

$auth->requireAccess(NIVEL_OPERACIONAL);

$motoristaCrud = new Motorista();
$veiculoCrud = new Veiculo();
$message = '';

// Processamento de Ações (Criar, Editar, Deletar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nome' => trim($_POST['nome']),
        'cpf' => trim($_POST['cpf']),
        'cnh' => trim($_POST['cnh']),
        'telefone' => trim($_POST['telefone']),
        'email' => trim($_POST['email']),
        'data_nascimento' => trim($_POST['data_nascimento']),
        'id_veiculo_atual' => empty($_POST['id_veiculo_atual']) ? null : $_POST['id_veiculo_atual'],
        'status_motorista' => trim($_POST['status_motorista']),
        'data_contratacao' => trim($_POST['data_contratacao']),
    ];

    $id_motorista = $_POST['id_motorista'] ?? null;

    if ($id_motorista) {
        // Atualizar
        if ($motoristaCrud->update($id_motorista, $data)) {
            $message = '<div class="success-message">Motorista atualizado com sucesso!</div>';
        } else {
            $message = '<div class="error-message">Erro ao atualizar motorista.</div>';
        }
    } else {
        // Criar
        if ($motoristaCrud->create($data)) {
            $message = '<div class="success-message">Motorista cadastrado com sucesso!</div>';
        } else {
            $message = '<div class="error-message">Erro ao cadastrar motorista.</div>';
        }
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    // Deletar
    if ($motoristaCrud->delete($_GET['id'])) {
        $message = '<div class="success-message">Motorista excluído com sucesso!</div>';
    } else {
        $message = '<div class="error-message">Erro ao excluir motorista. Verifique se há reservas ou salários associados.</div>';
    }
}

// Carregar dados para edição
$motorista_edit = null;
if (isset($_GET['id']) && !isset($_GET['action'])) {
    $motorista_edit = $motoristaCrud->getById($_GET['id']);
}

// Listar todos os motoristas (com nome do veículo associado, se houver)
$sql_list = "SELECT m.*, v.placa, v.modelo 
             FROM motoristas m
             LEFT JOIN veiculos v ON m.id_veiculo_atual = v.id_veiculo
             ORDER BY m.nome ASC";
$motoristas = $motoristaCrud->db->fetchAll($sql_list);

// Obter lista de veículos disponíveis para o select
$veiculos_disponiveis = $veiculoCrud->getAll(); // Pega todos para permitir desassociação

// Definição dos cabeçalhos para a tabela
$headers = [
    'id_motorista' => 'ID',
    'nome' => 'Nome',
    'cnh' => 'CNH',
    'telefone' => 'Telefone',
    'status_motorista' => 'Status',
    'placa' => 'Veículo Atual',
    'data_contratacao' => 'Contratação',
];
?>

<h2>Gestão de Motoristas</h2>

<?php echo $message; ?>

<div class="form-section">
    <h3><?php echo $motorista_edit ? 'Editar Motorista' : 'Novo Motorista'; ?></h3>
    <form method="POST" action="motoristas.php">
        <?php if ($motorista_edit): ?>
            <input type="hidden" name="id_motorista" value="<?php echo $motorista_edit['id_motorista']; ?>">
        <?php endif; ?>

        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" value="<?php echo $motorista_edit['nome'] ?? ''; ?>" required>

        <label for="cpf">CPF:</label>
        <input type="text" id="cpf" name="cpf" value="<?php echo $motorista_edit['cpf'] ?? ''; ?>" required>

        <label for="cnh">CNH:</label>
        <input type="text" id="cnh" name="cnh" value="<?php echo $motorista_edit['cnh'] ?? ''; ?>" required>

        <label for="telefone">Telefone:</label>
        <input type="text" id="telefone" name="telefone" value="<?php echo $motorista_edit['telefone'] ?? ''; ?>">

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo $motorista_edit['email'] ?? ''; ?>">

        <label for="data_nascimento">Data de Nascimento:</label>
        <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo $motorista_edit['data_nascimento'] ?? ''; ?>">
        
        <label for="data_contratacao">Data de Contratação:</label>
        <input type="date" id="data_contratacao" name="data_contratacao" value="<?php echo $motorista_edit['data_contratacao'] ?? date('Y-m-d'); ?>" required>

        <label for="status_motorista">Status:</label>
        <select id="status_motorista" name="status_motorista" required>
            <option value="Disponível" <?php echo ($motorista_edit['status_motorista'] ?? '') == 'Disponível' ? 'selected' : ''; ?>>Disponível</option>
            <option value="Em Corrida" <?php echo ($motorista_edit['status_motorista'] ?? '') == 'Em Corrida' ? 'selected' : ''; ?>>Em Corrida</option>
            <option value="Em Descanso" <?php echo ($motorista_edit['status_motorista'] ?? '') == 'Em Descanso' ? 'selected' : ''; ?>>Em Descanso</option>
            <option value="Férias" <?php echo ($motorista_edit['status_motorista'] ?? '') == 'Férias' ? 'selected' : ''; ?>>Férias</option>
        </select>

        <label for="id_veiculo_atual">Veículo Associado (Opcional):</label>
        <select id="id_veiculo_atual" name="id_veiculo_atual">
            <option value="">-- Nenhum --</option>
            <?php foreach ($veiculos_disponiveis as $veiculo): ?>
                <option value="<?php echo $veiculo['id_veiculo']; ?>" 
                    <?php echo ($motorista_edit['id_veiculo_atual'] ?? '') == $veiculo['id_veiculo'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($veiculo['placa'] . ' - ' . $veiculo['modelo']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit"><?php echo $motorista_edit ? 'Salvar Alterações' : 'Cadastrar Motorista'; ?></button>
        <?php if ($motorista_edit): ?>
            <a href="motoristas.php" class="btn btn-secondary">Cancelar Edição</a>
        <?php endif; ?>
    </form>
</div>

<div class="list-section">
    <h3>Lista de Motoristas</h3>
    <?php 
    // Adaptação para exibir a placa/modelo do veículo
    $motoristas_formatados = array_map(function($m) {
        $m['placa'] = $m['placa'] ? $m['placa'] . ' (' . $m['modelo'] . ')' : 'Nenhum';
        return $m;
    }, $motoristas);
    
    echo HtmlHelper::createListTable($motoristas_formatados, $headers, 'id_motorista', 'motoristas.php'); 
    ?>
</div>

<?php
require_once '../includes/footer.php';
?>
