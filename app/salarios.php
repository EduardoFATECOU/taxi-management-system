<?php
$page_title = 'Gestão de Salários e Comissões';
require_once '../includes/header.php';
require_once '../includes/Crud.php';
require_once '../includes/Financeiro.php';

$auth->requireAccess(NIVEL_FINANCEIRO);

$salarioCrud = new SalarioComissao();
$motoristaCrud = new Motorista();
$movimentacaoCrud = new MovimentacaoFinanceira();
$message = '';

// Processamento de Ações (Criar, Editar, Deletar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'id_motorista' => trim($_POST['id_motorista']),
        'mes_referencia' => trim($_POST['mes_referencia']) . '-01', // Garante que seja o primeiro dia do mês
        'salario_base' => floatval($_POST['salario_base']),
        'comissao_total' => floatval($_POST['comissao_total']),
        'outros_bonus' => floatval($_POST['outros_bonus']),
        'descontos' => floatval($_POST['descontos']),
        'status_pagamento' => trim($_POST['status_pagamento']),
        'data_pagamento' => empty($_POST['data_pagamento']) ? null : trim($_POST['data_pagamento']),
    ];
    
    // Cálculo do valor líquido
    $data['valor_liquido'] = $data['salario_base'] + $data['comissao_total'] + $data['outros_bonus'] - $data['descontos'];

    $id_registro = $_POST['id_registro'] ?? null;
    $valor = $data['valor_liquido'];
    $mes_ref = date('m/Y', strtotime($data['mes_referencia']));

    if ($id_registro) {
        // Atualizar
        if ($salarioCrud->update($id_registro, $data)) {
            $message = '<div class="success-message">Registro de salário atualizado com sucesso!</div>';
            
            // Atualiza a movimentação financeira correspondente
            $movimentacaoCrud->db->execute("DELETE FROM movimentacao_financeira WHERE categoria = 'Salário' AND id_referencia = :id", ['id' => $id_registro]);
            
            if ($data['status_pagamento'] === 'Pago') {
                $movimentacaoCrud->create([
                    'tipo_movimentacao' => 'Saída',
                    'categoria' => 'Salário',
                    'descricao' => 'Pagamento de Salário/Comissão - Ref: ' . $mes_ref,
                    'valor' => $valor,
                    'data_movimentacao' => $data['data_pagamento'] ?? date('Y-m-d'),
                    'id_referencia' => $id_registro,
                ]);
            }
        } else {
            $message = '<div class="error-message">Erro ao atualizar registro de salário.</div>';
        }
    } else {
        // Criar
        $id_registro = $salarioCrud->create($data);
        if ($id_registro) {
            $message = '<div class="success-message">Registro de salário cadastrado com sucesso!</div>';
            
            // Registra a saída no financeiro
            if ($data['status_pagamento'] === 'Pago') {
                $movimentacaoCrud->create([
                    'tipo_movimentacao' => 'Saída',
                    'categoria' => 'Salário',
                    'descricao' => 'Pagamento de Salário/Comissão - Ref: ' . $mes_ref,
                    'valor' => $valor,
                    'data_movimentacao' => $data['data_pagamento'] ?? date('Y-m-d'),
                    'id_referencia' => $id_registro,
                ]);
            }
        } else {
            $message = '<div class="error-message">Erro ao cadastrar registro de salário. Verifique se já existe um registro para o motorista no mês de referência.</div>';
        }
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    // Deletar
    $id_registro = $_GET['id'];
    if ($salarioCrud->delete($id_registro)) {
        // Remove a movimentação financeira
        $movimentacaoCrud->db->execute("DELETE FROM movimentacao_financeira WHERE categoria = 'Salário' AND id_referencia = :id", ['id' => $id_registro]);
        $message = '<div class="success-message">Registro de salário excluído com sucesso!</div>';
    } else {
        $message = '<div class="error-message">Erro ao excluir registro de salário.</div>';
    }
}

// Carregar dados para edição
$salario_edit = null;
if (isset($_GET['id']) && !isset($_GET['action'])) {
    $salario_edit = $salarioCrud->getById($_GET['id']);
    if ($salario_edit) {
        $salario_edit['mes_referencia'] = substr($salario_edit['mes_referencia'], 0, 7); // Formato YYYY-MM para o input
    }
}

// Listar dados auxiliares
$motoristas = $motoristaCrud->getAll('nome ASC');

// Listar todos os registros de salários
$salarios = $salarioCrud->getAllSalarios();

// Definição dos cabeçalhos para a tabela
$headers = [
    'id_registro' => 'ID',
    'nome_motorista' => 'Motorista',
    'mes_referencia' => 'Mês Ref.',
    'salario_base' => 'Salário Base',
    'comissao_total' => 'Comissão',
    'valor_liquido' => 'Líquido',
    'status_pagamento' => 'Status',
    'data_pagamento' => 'Data Pag.',
];
?>

<h2>Gestão de Salários e Comissões</h2>

<?php echo $message; ?>

<div class="form-section">
    <h3><?php echo $salario_edit ? 'Editar Registro' : 'Novo Registro de Salário'; ?></h3>
    <form method="POST" action="salarios.php">
        <?php if ($salario_edit): ?>
            <input type="hidden" name="id_registro" value="<?php echo $salario_edit['id_registro']; ?>">
        <?php endif; ?>

        <label for="id_motorista">Motorista:</label>
        <select id="id_motorista" name="id_motorista" required>
            <option value="">-- Selecione --</option>
            <?php foreach ($motoristas as $motorista): ?>
                <option value="<?php echo $motorista['id_motorista']; ?>" 
                    <?php echo ($salario_edit['id_motorista'] ?? '') == $motorista['id_motorista'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($motorista['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="mes_referencia">Mês de Referência:</label>
        <input type="month" id="mes_referencia" name="mes_referencia" value="<?php echo $salario_edit['mes_referencia'] ?? date('Y-m'); ?>" required>

        <label for="salario_base">Salário Base (R$):</label>
        <input type="number" step="0.01" id="salario_base" name="salario_base" value="<?php echo $salario_edit['salario_base'] ?? '0.00'; ?>" required>

        <label for="comissao_total">Comissão Total (R$):</label>
        <input type="number" step="0.01" id="comissao_total" name="comissao_total" value="<?php echo $salario_edit['comissao_total'] ?? '0.00'; ?>" required>

        <label for="outros_bonus">Outros Bônus (R$):</label>
        <input type="number" step="0.01" id="outros_bonus" name="outros_bonus" value="<?php echo $salario_edit['outros_bonus'] ?? '0.00'; ?>" required>

        <label for="descontos">Descontos (R$):</label>
        <input type="number" step="0.01" id="descontos" name="descontos" value="<?php echo $salario_edit['descontos'] ?? '0.00'; ?>" required>

        <label for="status_pagamento">Status do Pagamento:</label>
        <select id="status_pagamento" name="status_pagamento" required>
            <option value="Pendente" <?php echo ($salario_edit['status_pagamento'] ?? '') == 'Pendente' ? 'selected' : ''; ?>>Pendente</option>
            <option value="Pago" <?php echo ($salario_edit['status_pagamento'] ?? '') == 'Pago' ? 'selected' : ''; ?>>Pago</option>
        </select>
        
        <label for="data_pagamento">Data do Pagamento (Opcional):</label>
        <input type="date" id="data_pagamento" name="data_pagamento" value="<?php echo $salario_edit['data_pagamento'] ?? ''; ?>">

        <button type="submit"><?php echo $salario_edit ? 'Salvar Alterações' : 'Registrar Salário'; ?></button>
        <?php if ($salario_edit): ?>
            <a href="salarios.php" class="btn btn-secondary">Cancelar Edição</a>
        <?php endif; ?>
    </form>
</div>

<div class="list-section">
    <h3>Histórico de Pagamentos de Salários e Comissões</h3>
    <?php 
    // Formatação dos valores monetários e datas para exibição
    $salarios_formatados = array_map(function($s) {
        $s['mes_referencia'] = date('m/Y', strtotime($s['mes_referencia']));
        $s['salario_base'] = 'R$ ' . number_format($s['salario_base'], 2, ',', '.');
        $s['comissao_total'] = 'R$ ' . number_format($s['comissao_total'], 2, ',', '.');
        $s['valor_liquido'] = 'R$ ' . number_format($s['valor_liquido'], 2, ',', '.');
        $s['data_pagamento'] = $s['data_pagamento'] ? date('d/m/Y', strtotime($s['data_pagamento'])) : 'N/A';
        return $s;
    }, $salarios);
    
    echo HtmlHelper::createListTable($salarios_formatados, $headers, 'id_registro', 'salarios.php'); 
    ?>
</div>

<?php
require_once '../includes/footer.php';
?>
