<?php
$page_title = 'Gestão de Reservas e Corridas';
require_once '../includes/header.php';
require_once '../includes/Crud.php';
require_once '../includes/Reserva.php';

$auth->requireAccess(NIVEL_OPERACIONAL);

$reservaCrud = new Reserva();
$clienteCrud = new Cliente();
$motoristaCrud = new Motorista();
$veiculoCrud = new Veiculo();
$message = '';

// Processamento de Ações (Criar, Editar, Deletar, Gerenciar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'id_cliente' => trim($_POST['id_cliente']),
        'id_motorista' => empty($_POST['id_motorista']) ? null : trim($_POST['id_motorista']),
        'id_veiculo' => empty($_POST['id_veiculo']) ? null : trim($_POST['id_veiculo']),
        'origem' => trim($_POST['origem']),
        'destino' => trim($_POST['destino']),
        'data_hora_reserva' => trim($_POST['data_hora_reserva']),
        'valor_estimado' => floatval($_POST['valor_estimado']),
        'distancia_km' => empty($_POST['distancia_km']) ? null : floatval($_POST['distancia_km']),
        'valor_final' => empty($_POST['valor_final']) ? null : floatval($_POST['valor_final']),
        'status_viagem' => trim($_POST['status_viagem']),
    ];

    $id_reserva = $_POST['id_reserva'] ?? null;

    if ($id_reserva) {
        // Atualizar
        if ($reservaCrud->update($id_reserva, $data)) {
            $message = '<div class="success-message">Reserva atualizada com sucesso!</div>';
        } else {
            $message = '<div class="error-message">Erro ao atualizar reserva.</div>';
        }
    } else {
        // Criar
        if ($reservaCrud->create($data)) {
            $message = '<div class="success-message">Reserva cadastrada com sucesso!</div>';
        } else {
            $message = '<div class="error-message">Erro ao cadastrar reserva.</div>';
        }
    }
} elseif (isset($_GET['action']) && isset($_GET['id'])) {
    $id_reserva = $_GET['id'];
    
    if ($_GET['action'] === 'delete') {
        // Deletar
        if ($reservaCrud->delete($id_reserva)) {
            $message = '<div class="success-message">Reserva excluída com sucesso!</div>';
        } else {
            $message = '<div class="error-message">Erro ao excluir reserva.</div>';
        }
    } elseif ($_GET['action'] === 'cancelar') {
        // Cancelar Reserva
        if ($reservaCrud->updateStatus($id_reserva, 'Cancelada')) {
            $message = '<div class="success-message">Reserva cancelada com sucesso!</div>';
        } else {
            $message = '<div class="error-message">Erro ao cancelar reserva.</div>';
        }
    } elseif ($_GET['action'] === 'confirmar') {
        // Confirmar Reserva (e iniciar a viagem)
        if ($reservaCrud->updateStatus($id_reserva, 'Confirmada')) {
            $message = '<div class="success-message">Reserva confirmada e viagem iniciada com sucesso!</div>';
        } else {
            $message = '<div class="error-message">Erro ao confirmar reserva.</div>';
        }
    } elseif ($_GET['action'] === 'concluir') {
        // Concluir Viagem
        if ($reservaCrud->updateStatus($id_reserva, 'Concluída')) {
            $message = '<div class="success-message">Viagem concluída com sucesso! Lembre-se de registrar o pagamento e a quilometragem.</div>';
        } else {
            $message = '<div class="error-message">Erro ao concluir viagem.</div>';
        }
    }
}

// Carregar dados para edição
$reserva_edit = null;
if (isset($_GET['id']) && !isset($_GET['action'])) {
    $reserva_edit = $reservaCrud->getById($_GET['id']);
}

// Listar dados auxiliares
$clientes = $clienteCrud->getAll('nome ASC');
$motoristas = $motoristaCrud->getAvailableDrivers(); // Motoristas disponíveis
$veiculos = $veiculoCrud->getAvailableVehicles(); // Veículos disponíveis

// Se estiver editando, inclui o motorista e veículo atualmente associados na lista
if ($reserva_edit && $reserva_edit['id_motorista']) {
    $motorista_atual = $motoristaCrud->getById($reserva_edit['id_motorista']);
    if ($motorista_atual && !in_array($motorista_atual, $motoristas)) {
        $motoristas[] = $motorista_atual;
    }
}
if ($reserva_edit && $reserva_edit['id_veiculo']) {
    $veiculo_atual = $veiculoCrud->getById($reserva_edit['id_veiculo']);
    if ($veiculo_atual && !in_array($veiculo_atual, $veiculos)) {
        $veiculos[] = $veiculo_atual;
    }
}

// Listar todas as reservas
$reservas = $reservaCrud->getAllReservas();

// Definição dos cabeçalhos para a tabela
$headers = [
    'id_reserva' => 'ID',
    'nome_cliente' => 'Cliente',
    'nome_motorista' => 'Motorista',
    'placa' => 'Veículo',
    'origem' => 'Origem',
    'destino' => 'Destino',
    'data_hora_reserva' => 'Data/Hora',
    'status_viagem' => 'Status',
    'valor_final' => 'Valor Final',
];
?>

<h2>Gestão de Reservas e Corridas</h2>

<?php echo $message; ?>

<div class="form-section">
    <h3><?php echo $reserva_edit ? 'Gerenciar Reserva' : 'Nova Reserva'; ?></h3>
    <form method="POST" action="reservas.php">
        <?php if ($reserva_edit): ?>
            <input type="hidden" name="id_reserva" value="<?php echo $reserva_edit['id_reserva']; ?>">
        <?php endif; ?>

        <label for="id_cliente">Cliente:</label>
        <select id="id_cliente" name="id_cliente" required>
            <option value="">-- Selecione --</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?php echo $cliente['id_cliente']; ?>" 
                    <?php echo ($reserva_edit['id_cliente'] ?? '') == $cliente['id_cliente'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cliente['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="id_motorista">Motorista (Opcional):</label>
        <select id="id_motorista" name="id_motorista">
            <option value="">-- Selecione --</option>
            <?php foreach ($motoristas as $motorista): ?>
                <option value="<?php echo $motorista['id_motorista']; ?>" 
                    <?php echo ($reserva_edit['id_motorista'] ?? '') == $motorista['id_motorista'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($motorista['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="id_veiculo">Veículo (Opcional):</label>
        <select id="id_veiculo" name="id_veiculo">
            <option value="">-- Selecione --</option>
            <?php foreach ($veiculos as $veiculo): ?>
                <option value="<?php echo $veiculo['id_veiculo']; ?>" 
                    <?php echo ($reserva_edit['id_veiculo'] ?? '') == $veiculo['id_veiculo'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($veiculo['placa'] . ' - ' . $veiculo['modelo']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="origem">Origem:</label>
        <input type="text" id="origem" name="origem" value="<?php echo $reserva_edit['origem'] ?? ''; ?>" required>

        <label for="destino">Destino:</label>
        <input type="text" id="destino" name="destino" value="<?php echo $reserva_edit['destino'] ?? ''; ?>" required>

        <label for="data_hora_reserva">Data/Hora da Reserva:</label>
        <input type="datetime-local" id="data_hora_reserva" name="data_hora_reserva" value="<?php echo date('Y-m-d\TH:i', strtotime($reserva_edit['data_hora_reserva'] ?? date('Y-m-d H:i'))); ?>" required>

        <label for="valor_estimado">Valor Estimado (R$):</label>
        <input type="number" step="0.01" id="valor_estimado" name="valor_estimado" value="<?php echo $reserva_edit['valor_estimado'] ?? '0.00'; ?>">
        
        <label for="distancia_km">Distância (KM - Opcional):</label>
        <input type="number" step="0.01" id="distancia_km" name="distancia_km" value="<?php echo $reserva_edit['distancia_km'] ?? ''; ?>">
        
        <label for="valor_final">Valor Final (R$ - Opcional):</label>
        <input type="number" step="0.01" id="valor_final" name="valor_final" value="<?php echo $reserva_edit['valor_final'] ?? ''; ?>">

        <label for="status_viagem">Status da Viagem:</label>
        <select id="status_viagem" name="status_viagem" required>
            <option value="Pendente" <?php echo ($reserva_edit['status_viagem'] ?? '') == 'Pendente' ? 'selected' : ''; ?>>Pendente</option>
            <option value="Confirmada" <?php echo ($reserva_edit['status_viagem'] ?? '') == 'Confirmada' ? 'selected' : ''; ?>>Confirmada</option>
            <option value="Em Andamento" <?php echo ($reserva_edit['status_viagem'] ?? '') == 'Em Andamento' ? 'selected' : ''; ?>>Em Andamento</option>
            <option value="Concluída" <?php echo ($reserva_edit['status_viagem'] ?? '') == 'Concluída' ? 'selected' : ''; ?>>Concluída</option>
            <option value="Cancelada" <?php echo ($reserva_edit['status_viagem'] ?? '') == 'Cancelada' ? 'selected' : ''; ?>>Cancelada</option>
        </select>

        <button type="submit"><?php echo $reserva_edit ? 'Salvar Gerenciamento' : 'Cadastrar Reserva'; ?></button>
        <?php if ($reserva_edit): ?>
            <a href="reservas.php" class="btn btn-secondary">Cancelar Edição</a>
        <?php endif; ?>
    </form>
</div>

<div class="list-section">
    <h3>Histórico de Reservas</h3>
    <table class="data-table">
        <thead>
            <tr>
                <?php foreach ($headers as $key => $header): ?>
                    <th><?php echo $header; ?></th>
                <?php endforeach; ?>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($reservas): ?>
                <?php foreach ($reservas as $reserva): ?>
                    <tr>
                        <?php foreach (array_keys($headers) as $key): ?>
                            <td><?php 
                                if ($key === 'data_hora_reserva') {
                                    echo date('d/m/Y H:i', strtotime($reserva[$key]));
                                } elseif ($key === 'valor_final' && $reserva[$key] !== null) {
                                    echo 'R$ ' . number_format($reserva[$key], 2, ',', '.');
                                } else {
                                    echo htmlspecialchars($reserva[$key] ?? '');
                                }
                            ?></td>
                        <?php endforeach; ?>
                        <td>
                            <a href="reservas.php?id=<?php echo $reserva['id_reserva']; ?>" class="btn btn-edit">Gerenciar</a>
                            <?php if ($reserva['status_viagem'] === 'Pendente'): ?>
                                <a href="reservas.php?action=confirmar&id=<?php echo $reserva['id_reserva']; ?>" class="btn btn-success">Confirmar</a>
                                <a href="reservas.php?action=cancelar&id=<?php echo $reserva['id_reserva']; ?>" class="btn btn-warning">Cancelar</a>
                            <?php elseif ($reserva['status_viagem'] === 'Confirmada' || $reserva['status_viagem'] === 'Em Andamento'): ?>
                                <a href="reservas.php?action=concluir&id=<?php echo $reserva['id_reserva']; ?>" class="btn btn-primary">Concluir</a>
                            <?php endif; ?>
                            <a href="reservas.php?action=delete&id=<?php echo $reserva['id_reserva']; ?>" class="btn btn-delete" onclick="return confirm('Tem certeza que deseja excluir esta reserva?')">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="<?php echo count($headers) + 1; ?>">Nenhuma reserva encontrada.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once '../includes/footer.php';
?>
