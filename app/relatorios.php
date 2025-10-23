<?php
$page_title = 'Relatórios e Análises';
require_once '../includes/header.php';
require_once '../includes/Relatorio.php';

$auth->requireAccess(NIVEL_OPERACIONAL);

$relatorio = new Relatorio();
$data_inicio = $_POST['data_inicio'] ?? date('Y-m-01');
$data_fim = $_POST['data_fim'] ?? date('Y-m-d');
$relatorio_selecionado = $_POST['relatorio_selecionado'] ?? 'faturamento';
$resultados = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($relatorio_selecionado) {
        case 'faturamento':
            $resultados = $relatorio->faturamentoPorPeriodo($data_inicio, $data_fim);
            break;
        case 'corridas_motorista':
            $resultados = $relatorio->corridasPorMotorista($data_inicio, $data_fim);
            break;
        case 'gastos_manutencao':
            $resultados = $relatorio->gastosManutencaoPorVeiculo($data_inicio, $data_fim);
            break;
    }
}
?>

<h2>Relatórios e Análises</h2>

<div class="form-section">
    <h3>Gerar Relatório</h3>
    <form method="POST" action="relatorios.php">
        <label for="relatorio_selecionado">Tipo de Relatório:</label>
        <select id="relatorio_selecionado" name="relatorio_selecionado" required>
            <option value="faturamento" <?php echo $relatorio_selecionado == 'faturamento' ? 'selected' : ''; ?>>Faturamento por Período</option>
            <option value="corridas_motorista" <?php echo $relatorio_selecionado == 'corridas_motorista' ? 'selected' : ''; ?>>Corridas por Motorista</option>
            <option value="gastos_manutencao" <?php echo $relatorio_selecionado == 'gastos_manutencao' ? 'selected' : ''; ?>>Gastos com Manutenção por Veículo</option>
        </select>

        <label for="data_inicio">Data de Início:</label>
        <input type="date" id="data_inicio" name="data_inicio" value="<?php echo $data_inicio; ?>" required>

        <label for="data_fim">Data de Fim:</label>
        <input type="date" id="data_fim" name="data_fim" value="<?php echo $data_fim; ?>" required>

        <button type="submit">Gerar Relatório</button>
    </form>
</div>

<div class="list-section">
    <h3>Resultado do Relatório (Período: <?php echo date('d/m/Y', strtotime($data_inicio)); ?> a <?php echo date('d/m/Y', strtotime($data_fim)); ?>)</h3>

    <?php if ($resultados): ?>
        <?php if ($relatorio_selecionado == 'faturamento'): ?>
            <h4>Relatório de Faturamento</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Total de Entradas</th>
                        <th>Total de Saídas</th>
                        <th>Saldo Líquido</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>R$ <?php echo number_format($resultados['total_entradas'] ?? 0, 2, ',', '.'); ?></td>
                        <td>R$ <?php echo number_format($resultados['total_saidas'] ?? 0, 2, ',', '.'); ?></td>
                        <td>R$ <?php echo number_format($resultados['saldo_liquido'] ?? 0, 2, ',', '.'); ?></td>
                    </tr>
                </tbody>
            </table>
        <?php elseif ($relatorio_selecionado == 'corridas_motorista'): ?>
            <h4>Relatório de Corridas por Motorista</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Motorista</th>
                        <th>Total de Corridas</th>
                        <th>Faturamento Bruto (R$)</th>
                        <th>Total KM</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['motorista']); ?></td>
                        <td><?php echo $row['total_corridas']; ?></td>
                        <td>R$ <?php echo number_format($row['faturamento_bruto'] ?? 0, 2, ',', '.'); ?></td>
                        <td><?php echo number_format($row['total_km'] ?? 0, 2, ',', '.') . ' km'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($relatorio_selecionado == 'gastos_manutencao'): ?>
            <h4>Relatório de Gastos com Manutenção por Veículo</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Veículo (Placa)</th>
                        <th>Modelo</th>
                        <th>Custo Total (R$)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['placa']); ?></td>
                        <td><?php echo htmlspecialchars($row['modelo']); ?></td>
                        <td>R$ <?php echo number_format($row['custo_total'] ?? 0, 2, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php else: ?>
        <p>Nenhum dado encontrado para o período e relatório selecionados.</p>
    <?php endif; ?>
</div>

<?php
require_once '../includes/footer.php';
?>
