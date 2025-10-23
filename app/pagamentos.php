<?php
$page_title = 'Gestão de Pagamentos';
require_once '../includes/header.php';
require_once '../includes/Crud.php';
require_once '../includes/Reserva.php';
require_once '../includes/Financeiro.php';

$auth->requireAccess(NIVEL_FINANCEIRO);

$pagamentoCrud = new Pagamento();
$reservaCrud = new Reserva();
$movimentacaoCrud = new MovimentacaoFinanceira();
$message = '';

// Processamento de Ações (Criar, Editar, Deletar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'id_reserva' => trim($_POST['id_reserva']),
        'valor_pago' => floatval($_POST['valor_pago']),
        'metodo_pagamento' => trim($_POST['metodo_pagamento']),
        'data_pagamento' => trim($_POST['data_pagamento']),
        'status_pagamento' => trim($_POST['status_pagamento']),
    ];

    $id_pagamento = $_POST['id_pagamento'] ?? null;
    $reserva_id = $data['id_reserva'];
    $valor = $data['valor_pago'];

    if ($id_pagamento) {
        // Atualizar
        if ($pagamentoCrud->update($id_pagamento, $data)) {
            $message = '<div class="success-message">Pagamento atualizado com sucesso!</div>';
            
            // Atualiza a movimentação financeira correspondente
            // Simplificando: Deleta a antiga movimentação e cria uma nova
            $movimentacaoCrud->db->execute("DELETE FROM movimentacao_financeira WHERE categoria = 'Viagem' AND id_referencia = :id", ['id' => $id_pagamento]);
            
            if ($data['status_pagamento'] === 'Concluído') {
                $movimentacaoCrud->create([
                    'tipo_movimentacao' => 'Entrada',
                    'categoria' => 'Viagem',
                    'descricao' => 'Pagamento da Reserva #' . $reserva_id,
                    'valor' => $valor,
                    'data_movimentacao' => date('Y-m-d', strtotime($data['data_pagamento'])),
                    'id_referencia' => $id_pagamento,
                ]);
            }
        } else {
            $message = '<div class="error-message">Erro ao atualizar pagamento.</div>';
        }
    } else {
        // Criar
        $id_pagamento = $pagamentoCrud->create($data);
        if ($id_pagamento) {
            $message = '<div class="success-message">Pagamento registrado com sucesso!</div>';
            
            // Registra a entrada no financeiro
            if ($data['status_pagamento'] === 'Concluído') {
                $movimentacaoCrud->create([
                    'tipo_movimentacao' => 'Entrada',
                    'categoria' => 'Viagem',
                    'descricao' => 'Pagamento da Reserva #' . $reserva_id,
                    'valor' => $valor,
                    'data_movimentacao' => date('Y-m-d', strtotime($data['data_pagamento'])),
                    'id_referencia' => $id_pagamento,
                ]);
            }
        } else {
            $message = '<div class="error-message">Erro ao registrar pagamento.</div>';
        }
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    // Deletar
    $id_pagamento = $_GET['id'];
    if ($pagamentoCrud->delete($id_pagamento)) {
        // Remove a movimentação financeira
        $movimentacaoCrud->db->execute("DELETE FROM movimentacao_financeira WHERE categoria = 'Viagem' AND id_referencia = :id", ['id' => $id_pagamento]);
        $message = '<div class="success-message">Pagamento excluído com sucesso!</div>';
    } else {
        $message = '<div class="error-message">Erro ao excluir pagamento.</div>';
    }
}

// Carregar dados para edição
$pagamento_edit = null;
if (isset($_GET['id']) && !isset($_GET['action'])) {
    $pagamento_edit = $pagamentoCrud->getById($_GET['id']);
}

// Listar dados auxiliares
$reservas_concluidas = $reservaCrud->db->fetchAll("SELECT id_reserva, origem, destino FROM reservas WHERE status_viagem = 'Concluída' ORDER BY id_reserva DESC");

// Listar todos os pagamentos
$pagamentos = $pagamentoCrud->getAllPagamentos();

// Definição dos cabeçalhos para a tabela
$headers = [
    'id_pagamento' => 'ID',
    'id_reserva' => 'Reserva #',
    'nome_cliente' => 'Cliente',
    'valor_pago' => 'Valor (R$)',
    'metodo_pagamento' => 'Método',
    'data_pagamento' => 'Data',
    'status_pagamento' => 'Status',
];
?>

<h2>Gestão de Pagamentos</h2>

<?php echo $message; ?>

<div class="form-section">
    <h3><?php echo $pagamento_edit ? 'Editar Pagamento' : 'Novo Pagamento'; ?></h3>
    <form method="POST" action="pagamentos.php">
        <?php if ($pagamento_edit): ?>
            <input type="hidden" name="id_pagamento" value="<?php echo $pagamento_edit['id_pagamento']; ?>">
        <?php endif; ?>

        <label for="id_reserva">Reserva Associada:</label>
        <select id="id_reserva" name="id_reserva" required>
            <option value="">-- Selecione a Reserva Concluída --</option>
            <?php foreach ($reservas_concluidas as $reserva): ?>
                <option value="<?php echo $reserva['id_reserva']; ?>" 
                    <?php echo ($pagamento_edit['id_reserva'] ?? '') == $reserva['id_reserva'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars("Reserva #{$reserva['id_reserva']} - {$reserva['origem']} para {$reserva['destino']}"); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="valor_pago">Valor Pago (R$):</label>
        <input type="number" step="0.01" id="valor_pago" name="valor_pago" value="<?php echo $pagamento_edit['valor_pago'] ?? '0.00'; ?>" required>

        <label for="metodo_pagamento">Método de Pagamento:</label>
        <select id="metodo_pagamento" name="metodo_pagamento" required>
            <option value="Dinheiro" <?php echo ($pagamento_edit['metodo_pagamento'] ?? '') == 'Dinheiro' ? 'selected' : ''; ?>>Dinheiro</option>
            <option value="Cartão de Crédito" <?php echo ($pagamento_edit['metodo_pagamento'] ?? '') == 'Cartão de Crédito' ? 'selected' : ''; ?>>Cartão de Crédito</option>
            <option value="Cartão de Débito" <?php echo ($pagamento_edit['metodo_pagamento'] ?? '') == 'Cartão de Débito' ? 'selected' : ''; ?>>Cartão de Débito</option>
            <option value="Pix" <?php echo ($pagamento_edit['metodo_pagamento'] ?? '') == 'Pix' ? 'selected' : ''; ?>>Pix</option>
        </select>
        
        <label for="data_pagamento">Data e Hora do Pagamento:</label>
        <input type="datetime-local" id="data_pagamento" name="data_pagamento" value="<?php echo date('Y-m-d\TH:i', strtotime($pagamento_edit['data_pagamento'] ?? date('Y-m-d H:i'))); ?>" required>

        <label for="status_pagamento">Status do Pagamento:</label>
        <select id="status_pagamento" name="status_pagamento" required>
            <option value="Concluído" <?php echo ($pagamento_edit['status_pagamento'] ?? '') == 'Concluído' ? 'selected' : ''; ?>>Concluído</option>
            <option value="Pendente" <?php echo ($pagamento_edit['status_pagamento'] ?? '') == 'Pendente' ? 'selected' : ''; ?>>Pendente</option>
            <option value="Falhou" <?php echo ($pagamento_edit['status_pagamento'] ?? '') == 'Falhou' ? 'selected' : ''; ?>>Falhou</option>
        </select>

        <button type="submit"><?php echo $pagamento_edit ? 'Salvar Alterações' : 'Registrar Pagamento'; ?></button>
        <?php if ($pagamento_edit): ?>
            <a href="pagamentos.php" class="btn btn-secondary">Cancelar Edição</a>
        <?php endif; ?>
    </form>
</div>

<div class="list-section">
    <h3>Histórico de Pagamentos</h3>
    <?php 
    // Formatação dos valores monetários para exibição
    $pagamentos_formatados = array_map(function($p) {
        $p['valor_pago'] = number_format($p['valor_pago'], 2, ',', '.');
        $p['data_pagamento'] = date('d/m/Y H:i', strtotime($p['data_pagamento']));
        return $p;
    }, $pagamentos);
    
    echo HtmlHelper::createListTable($pagamentos_formatados, $headers, 'id_pagamento', 'pagamentos.php'); 
    ?>
</div>

<?php
require_once '../includes/footer.php';
?>
