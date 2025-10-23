<?php
$page_title = 'Contabilidade e Faturamento';
require_once '../includes/header.php';
require_once '../includes/Financeiro.php';

$auth->requireAccess(NIVEL_FINANCEIRO);

$movimentacaoCrud = new MovimentacaoFinanceira();
$message = '';

// Processamento de Ações (Criar, Editar, Deletar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'tipo_movimentacao' => trim($_POST['tipo_movimentacao']),
        'categoria' => trim($_POST['categoria']),
        'descricao' => trim($_POST['descricao']),
        'valor' => floatval($_POST['valor']),
        'data_movimentacao' => trim($_POST['data_movimentacao']),
        // id_referencia é preenchido automaticamente pelos módulos de Pagamentos, Salários e Manutenção
        'id_referencia' => null, 
    ];

    $id_movimentacao = $_POST['id_movimentacao'] ?? null;

    // Apenas permite criar/editar movimentações que não são automáticas (Viagem, Salário, Manutenção)
    if ($data['categoria'] !== 'Viagem' && $data['categoria'] !== 'Salário' && $data['categoria'] !== 'Manutenção') {
        if ($id_movimentacao) {
            // Atualizar
            if ($movimentacaoCrud->update($id_movimentacao, $data)) {
                $message = '<div class="success-message">Movimentação atualizada com sucesso!</div>';
            } else {
                $message = '<div class="error-message">Erro ao atualizar movimentação.</div>';
            }
        } else {
            // Criar
            if ($movimentacaoCrud->create($data)) {
                $message = '<div class="success-message">Movimentação registrada com sucesso!</div>';
            } else {
                $message = '<div class="error-message">Erro ao registrar movimentação.</div>';
            }
        }
    } else {
        $message = '<div class="error-message">Não é permitido criar ou editar movimentações automáticas (Viagem, Salário, Manutenção) por aqui. Use os módulos específicos.</div>';
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    // Deletar
    $movimentacao_delete = $movimentacaoCrud->getById($_GET['id']);
    if ($movimentacao_delete && $movimentacao_delete['categoria'] !== 'Viagem' && $movimentacao_delete['categoria'] !== 'Salário' && $movimentacao_delete['categoria'] !== 'Manutenção') {
        if ($movimentacaoCrud->delete($_GET['id'])) {
            $message = '<div class="success-message">Movimentação excluída com sucesso!</div>';
        } else {
            $message = '<div class="error-message">Erro ao excluir movimentação.</div>';
        }
    } else {
        $message = '<div class="error-message">Não é permitido excluir movimentações automáticas.</div>';
    }
}

// Carregar dados para edição
$movimentacao_edit = null;
if (isset($_GET['id']) && !isset($_GET['action'])) {
    $movimentacao_edit = $movimentacaoCrud->getById($_GET['id']);
}

// Listar todos os registros de movimentação
$movimentacoes = $movimentacaoCrud->getAll('data_movimentacao DESC, data_registro DESC');

// Obter resumos financeiros
$saldo_total = $movimentacaoCrud->getSaldoTotal();
$total_entradas = $movimentacaoCrud->getTotalEntradas();
$total_saidas = $movimentacaoCrud->getTotalSaidas();

// Definição dos cabeçalhos para a tabela
$headers = [
    'id_movimentacao' => 'ID',
    'data_movimentacao' => 'Data',
    'tipo_movimentacao' => 'Tipo',
    'categoria' => 'Categoria',
    'descricao' => 'Descrição',
    'valor' => 'Valor (R$)',
];
?>

<h2>Contabilidade e Faturamento</h2>

<?php echo $message; ?>

<div class="dashboard-cards">
    <div class="card" style="background-color: #e8f5e9;">
        <h4>Total de Entradas</h4>
        <p>R$ <?php echo number_format($total_entradas, 2, ',', '.'); ?></p>
    </div>
    <div class="card" style="background-color: #ffebee;">
        <h4>Total de Saídas</h4>
        <p>R$ <?php echo number_format($total_saidas, 2, ',', '.'); ?></p>
    </div>
    <div class="card" style="background-color: #e3f2fd;">
        <h4>Saldo Atual</h4>
        <p>R$ <?php echo number_format($saldo_total, 2, ',', '.'); ?></p>
    </div>
</div>

<div class="form-section">
    <h3><?php echo $movimentacao_edit ? 'Editar Movimentação (Outros)' : 'Nova Movimentação (Outros)'; ?></h3>
    <p>Use este formulário apenas para entradas e saídas que não são geradas automaticamente (Viagens, Salários, Manutenção).</p>
    <form method="POST" action="financeiro.php">
        <?php if ($movimentacao_edit): ?>
            <input type="hidden" name="id_movimentacao" value="<?php echo $movimentacao_edit['id_movimentacao']; ?>">
        <?php endif; ?>

        <label for="tipo_movimentacao">Tipo:</label>
        <select id="tipo_movimentacao" name="tipo_movimentacao" required>
            <option value="Entrada" <?php echo ($movimentacao_edit['tipo_movimentacao'] ?? '') == 'Entrada' ? 'selected' : ''; ?>>Entrada</option>
            <option value="Saída" <?php echo ($movimentacao_edit['tipo_movimentacao'] ?? '') == 'Saída' ? 'selected' : ''; ?>>Saída</option>
        </select>

        <label for="categoria">Categoria:</label>
        <select id="categoria" name="categoria" required>
            <option value="Outros" <?php echo ($movimentacao_edit['categoria'] ?? '') == 'Outros' ? 'selected' : ''; ?>>Outros</option>
            <option value="Viagem" disabled>Viagem (Automático)</option>
            <option value="Salário" disabled>Salário (Automático)</option>
            <option value="Manutenção" disabled>Manutenção (Automático)</option>
        </select>

        <label for="descricao">Descrição:</label>
        <input type="text" id="descricao" name="descricao" value="<?php echo $movimentacao_edit['descricao'] ?? ''; ?>" required>

        <label for="valor">Valor (R$):</label>
        <input type="number" step="0.01" id="valor" name="valor" value="<?php echo $movimentacao_edit['valor'] ?? '0.00'; ?>" required>
        
        <label for="data_movimentacao">Data da Movimentação:</label>
        <input type="date" id="data_movimentacao" name="data_movimentacao" value="<?php echo $movimentacao_edit['data_movimentacao'] ?? date('Y-m-d'); ?>" required>

        <button type="submit"><?php echo $movimentacao_edit ? 'Salvar Alterações' : 'Registrar Movimentação'; ?></button>
        <?php if ($movimentacao_edit): ?>
            <a href="financeiro.php" class="btn btn-secondary">Cancelar Edição</a>
        <?php endif; ?>
    </form>
</div>

<div class="list-section">
    <h3>Histórico de Movimentações Financeiras</h3>
    <?php 
    // Formatação dos valores monetários e datas para exibição
    $movimentacoes_formatadas = array_map(function($m) {
        $m['data_movimentacao'] = date('d/m/Y', strtotime($m['data_movimentacao']));
        $m['valor'] = number_format($m['valor'], 2, ',', '.');
        return $m;
    }, $movimentacoes);
    
    // Cria a tabela, mas desabilita a ação de deletar para as movimentações automáticas
    if ($movimentacoes_formatadas) {
        $html = '<table class="data-table">';
        $html .= '<thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '<th>Ações</th>';
        $html .= '</tr></thead>';
        $html .= '<tbody>';

        foreach ($movimentacoes_formatadas as $row) {
            $original_row = $movimentacaoCrud->getById($row['id_movimentacao']);
            $is_automatic = in_array($original_row['categoria'], ['Viagem', 'Salário', 'Manutenção']);
            
            $html .= '<tr>';
            foreach (array_keys($headers) as $key) {
                $html .= '<td>' . htmlspecialchars($row[$key] ?? '') . '</td>';
            }
            $html .= '<td>';
            if (!$is_automatic) {
                $html .= '<a href="financeiro.php?id=' . $row['id_movimentacao'] . '" class="btn btn-edit">Editar</a>';
                $html .= ' <a href="financeiro.php?action=delete&id=' . $row['id_movimentacao'] . '" class="btn btn-delete" onclick="return confirm(\'Tem certeza que deseja excluir este registro?\')">Excluir</a>';
            } else {
                $html .= 'Registro Automático';
            }
            $html .= '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        echo $html;
    } else {
        echo "<p>Nenhuma movimentação financeira encontrada.</p>";
    }
    ?>
</div>

<?php
require_once '../includes/footer.php';
?>
