<?php
$page_title = 'Dashboard';
require_once '../includes/header.php';
require_once '../includes/Database.php';

$db = Database::getInstance();

// Exemplo de dados para o Dashboard (apenas para demonstração)
$total_reservas = $db->fetchOne("SELECT COUNT(*) as total FROM reservas")['total'] ?? 0;
$total_clientes = $db->fetchOne("SELECT COUNT(*) as total FROM clientes")['total'] ?? 0;
$total_motoristas = $db->fetchOne("SELECT COUNT(*) as total FROM motoristas")['total'] ?? 0;
$total_veiculos = $db->fetchOne("SELECT COUNT(*) as total FROM veiculos")['total'] ?? 0;

// Exemplo de saldo financeiro (apenas entradas)
$saldo_financeiro = $db->fetchOne("SELECT SUM(valor) as total FROM movimentacao_financeira WHERE tipo_movimentacao = 'Entrada'")['total'] ?? 0.00;

// Exemplo de corridas pendentes
$corridas_pendentes = $db->fetchAll("SELECT r.id_reserva, c.nome as cliente_nome, r.origem, r.destino, r.data_hora_reserva 
                                     FROM reservas r
                                     JOIN clientes c ON r.id_cliente = c.id_cliente
                                     WHERE r.status_viagem = 'Pendente'
                                     ORDER BY r.data_hora_reserva ASC
                                     LIMIT 5");

?>

<style>
    /* Estilos para os cards do dashboard */
    .dashboard-cards {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }
    .card {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        flex: 1;
        min-width: 200px;
        text-align: center;
    }
    .card h4 {
        margin-top: 0;
        color: #7f8c8d;
    }
    .card p {
        font-size: 2em;
        font-weight: bold;
        color: #2c3e50;
        margin: 5px 0;
    }
    .recent-activity {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .recent-activity h3 {
        margin-top: 0;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    .recent-activity table {
        width: 100%;
        border-collapse: collapse;
    }
    .recent-activity th, .recent-activity td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #f4f4f4;
    }
    .recent-activity th {
        background-color: #ecf0f1;
    }
</style>

<h2>Visão Geral do Sistema</h2>

<div class="dashboard-cards">
    <div class="card">
        <h4>Total de Reservas</h4>
        <p><?php echo $total_reservas; ?></p>
    </div>
    <div class="card">
        <h4>Clientes Cadastrados</h4>
        <p><?php echo $total_clientes; ?></p>
    </div>
    <div class="card">
        <h4>Motoristas Ativos</h4>
        <p><?php echo $total_motoristas; ?></p>
    </div>
    <div class="card">
        <h4>Frota de Veículos</h4>
        <p><?php echo $total_veiculos; ?></p>
    </div>
    <?php if ($auth->hasAccess(NIVEL_FINANCEIRO)): ?>
    <div class="card" style="background-color: #e8f5e9;">
        <h4>Saldo Financeiro (Entradas)</h4>
        <p>R$ <?php echo number_format($saldo_financeiro, 2, ',', '.'); ?></p>
    </div>
    <?php endif; ?>
</div>

<div class="recent-activity">
    <h3>Próximas Corridas Pendentes</h3>
    <?php if ($corridas_pendentes): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Origem</th>
                <th>Destino</th>
                <th>Data/Hora</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($corridas_pendentes as $corrida): ?>
            <tr>
                <td><?php echo $corrida['id_reserva']; ?></td>
                <td><?php echo htmlspecialchars($corrida['cliente_nome']); ?></td>
                <td><?php echo htmlspecialchars($corrida['origem']); ?></td>
                <td><?php echo htmlspecialchars($corrida['destino']); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($corrida['data_hora_reserva'])); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>Nenhuma corrida pendente encontrada.</p>
    <?php endif; ?>
</div>

<?php
require_once '../includes/footer.php';
?>
