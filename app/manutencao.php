<?php
$page_title = 'Gestão de Manutenção de Veículos';
require_once '../includes/header.php';
require_once '../includes/Crud.php';
require_once '../includes/Financeiro.php';

$auth->requireAccess(NIVEL_FINANCEIRO);

$manutencaoCrud = new ManutencaoVeiculo();
$veiculoCrud = new Veiculo();
$movimentacaoCrud = new MovimentacaoFinanceira();
$message = '';

// Processamento de Ações (Criar, Editar, Deletar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'id_veiculo' => trim($_POST['id_veiculo']),
        'data_manutencao' => trim($_POST['data_manutencao']),
        'descricao' => trim($_POST['descricao']),
        'custo' => floatval($_POST['custo']),
        'tipo_manutencao' => trim($_POST['tipo_manutencao']),
        'quilometragem_manutencao' => trim($_POST['quilometragem_manutencao']),
    ];

    $id_manutencao = $_POST['id_manutencao'] ?? null;
    $custo = $data['custo'];
    $data_movimentacao = $data['data_manutencao'];
    $placa = $veiculoCrud->getById($data['id_veiculo'])['placa'] ?? 'N/A';

    if ($id_manutencao) {
        // Atualizar
        if ($manutencaoCrud->update($id_manutencao, $data)) {
            $message = '<div class="success-message">Registro de manutenção atualizado com sucesso!</div>';
            
            // Atualiza a movimentação financeira correspondente
            $movimentacaoCrud->db->execute("DELETE FROM movimentacao_financeira WHERE categoria = 'Manutenção' AND id_referencia = :id", ['id' => $id_manutencao]);
            
            $movimentacaoCrud->create([
                'tipo_movimentacao' => 'Saída',
                'categoria' => 'Manutenção',
                'descricao' => 'Manutenção Veículo ' . $placa . ': ' . $data['descricao'],
                'valor' => $custo,
                'data_movimentacao' => $data_movimentacao,
                'id_referencia' => $id_manutencao,
            ]);
        } else {
            $message = '<div class="error-message">Erro ao atualizar registro de manutenção.</div>';
        }
    } else {
        // Criar
        $id_manutencao = $manutencaoCrud->create($data);
        if ($id_manutencao) {
            $message = '<div class="success-message">Registro de manutenção cadastrado com sucesso!</div>';
            
            // Registra a saída no financeiro
            $movimentacaoCrud->create([
                'tipo_movimentacao' => 'Saída',
                'categoria' => 'Manutenção',
                'descricao' => 'Manutenção Veículo ' . $placa . ': ' . $data['descricao'],
                'valor' => $custo,
                'data_movimentacao' => $data_movimentacao,
                'id_referencia' => $id_manutencao,
            ]);
        } else {
            $message = '<div class="error-message">Erro ao cadastrar registro de manutenção.</div>';
        }
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    // Deletar
    $id_manutencao = $_GET['id'];
    if ($manutencaoCrud->delete($id_manutencao)) {
        // Remove a movimentação financeira
        $movimentacaoCrud->db->execute("DELETE FROM movimentacao_financeira WHERE categoria = 'Manutenção' AND id_referencia = :id", ['id' => $id_manutencao]);
        $message = '<div class="success-message">Registro de manutenção excluído com sucesso!</div>';
    } else {
        $message = '<div class="error-message">Erro ao excluir registro de manutenção.</div>';
    }
}

// Carregar dados para edição
$manutencao_edit = null;
if (isset($_GET['id']) && !isset($_GET['action'])) {
    $manutencao_edit = $manutencaoCrud->getById($_GET['id']);
}

// Listar dados auxiliares
$veiculos = $veiculoCrud->getAll('placa ASC');

// Listar todos os registros de manutenção
$manutencoes = $manutencaoCrud->getAllManutencoes();

// Definição dos cabeçalhos para a tabela
$headers = [
    'id_manutencao' => 'ID',
    'placa' => 'Veículo',
    'data_manutencao' => 'Data',
    'tipo_manutencao' => 'Tipo',
    'quilometragem_manutencao' => 'KM',
    'descricao' => 'Descrição',
    'custo' => 'Custo (R$)',
];
?>

<h2>Gestão de Manutenção de Veículos</h2>

<?php echo $message; ?>

<div class="form-section">
    <h3><?php echo $manutencao_edit ? 'Editar Registro' : 'Novo Registro de Manutenção'; ?></h3>
    <form method="POST" action="manutencao.php">
        <?php if ($manutencao_edit): ?>
            <input type="hidden" name="id_manutencao" value="<?php echo $manutencao_edit['id_manutencao']; ?>">
        <?php endif; ?>

        <label for="id_veiculo">Veículo:</label>
        <select id="id_veiculo" name="id_veiculo" required>
            <option value="">-- Selecione --</option>
            <?php foreach ($veiculos as $veiculo): ?>
                <option value="<?php echo $veiculo['id_veiculo']; ?>" 
                    <?php echo ($manutencao_edit['id_veiculo'] ?? '') == $veiculo['id_veiculo'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($veiculo['placa'] . ' - ' . $veiculo['modelo']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="data_manutencao">Data da Manutenção:</label>
        <input type="date" id="data_manutencao" name="data_manutencao" value="<?php echo $manutencao_edit['data_manutencao'] ?? date('Y-m-d'); ?>" required>

        <label for="tipo_manutencao">Tipo de Manutenção:</label>
        <select id="tipo_manutencao" name="tipo_manutencao" required>
            <option value="Preventiva" <?php echo ($manutencao_edit['tipo_manutencao'] ?? '') == 'Preventiva' ? 'selected' : ''; ?>>Preventiva</option>
            <option value="Corretiva" <?php echo ($manutencao_edit['tipo_manutencao'] ?? '') == 'Corretiva' ? 'selected' : ''; ?>>Corretiva</option>
            <option value="Revisão" <?php echo ($manutencao_edit['tipo_manutencao'] ?? '') == 'Revisão' ? 'selected' : ''; ?>>Revisão</option>
        </select>

        <label for="quilometragem_manutencao">Quilometragem (KM):</label>
        <input type="number" id="quilometragem_manutencao" name="quilometragem_manutencao" value="<?php echo $manutencao_edit['quilometragem_manutencao'] ?? ''; ?>">

        <label for="custo">Custo (R$):</label>
        <input type="number" step="0.01" id="custo" name="custo" value="<?php echo $manutencao_edit['custo'] ?? '0.00'; ?>" required>

        <label for="descricao">Descrição:</label>
        <textarea id="descricao" name="descricao" required><?php echo $manutencao_edit['descricao'] ?? ''; ?></textarea>

        <button type="submit"><?php echo $manutencao_edit ? 'Salvar Alterações' : 'Registrar Manutenção'; ?></button>
        <?php if ($manutencao_edit): ?>
            <a href="manutencao.php" class="btn btn-secondary">Cancelar Edição</a>
        <?php endif; ?>
    </form>
</div>

<div class="list-section">
    <h3>Histórico de Manutenções</h3>
    <?php 
    // Formatação dos valores monetários e datas para exibição
    $manutencoes_formatadas = array_map(function($m) {
        $m['placa'] = $m['placa'] . ' (' . $m['modelo'] . ')';
        $m['data_manutencao'] = date('d/m/Y', strtotime($m['data_manutencao']));
        $m['custo'] = number_format($m['custo'], 2, ',', '.');
        return $m;
    }, $manutencoes);
    
    echo HtmlHelper::createListTable($manutencoes_formatadas, $headers, 'id_manutencao', 'manutencao.php'); 
    ?>
</div>

<?php
require_once '../includes/footer.php';
?>
