<?php
$page_title = 'Gestão de Quilometragem';
require_once '../includes/header.php';
require_once '../includes/Crud.php';
require_once '../includes/Financeiro.php';

$auth->requireAccess(NIVEL_FINANCEIRO);

$quilometragemCrud = new Quilometragem();
$veiculoCrud = new Veiculo();
$message = '';

// Processamento de Ações (Criar, Editar, Deletar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'id_veiculo' => trim($_POST['id_veiculo']),
        'data_registro' => trim($_POST['data_registro']),
        'km_inicial' => trim($_POST['km_inicial']),
        'km_final' => trim($_POST['km_final']),
        'observacoes' => trim($_POST['observacoes']),
    ];
    
    // Atualiza KM atual do veículo
    $veiculoCrud->update($data['id_veiculo'], ['quilometragem_atual' => $data['km_final']]);

    $id_registro_km = $_POST['id_registro_km'] ?? null;

    if ($id_registro_km) {
        // Atualizar
        if ($quilometragemCrud->update($id_registro_km, $data)) {
            $message = '<div class="success-message">Registro de quilometragem atualizado com sucesso!</div>';
        } else {
            $message = '<div class="error-message">Erro ao atualizar registro de quilometragem.</div>';
        }
    } else {
        // Criar
        if ($quilometragemCrud->create($data)) {
            $message = '<div class="success-message">Registro de quilometragem cadastrado com sucesso!</div>';
        } else {
            $message = '<div class="error-message">Erro ao cadastrar registro de quilometragem.</div>';
        }
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    // Deletar
    if ($quilometragemCrud->delete($_GET['id'])) {
        $message = '<div class="success-message">Registro de quilometragem excluído com sucesso!</div>';
    } else {
        $message = '<div class="error-message">Erro ao excluir registro de quilometragem.</div>';
    }
}

// Carregar dados para edição
$quilometragem_edit = null;
if (isset($_GET['id']) && !isset($_GET['action'])) {
    $quilometragem_edit = $quilometragemCrud->getById($_GET['id']);
}

// Listar dados auxiliares
$veiculos = $veiculoCrud->getAll('placa ASC');
$quilometragens = $quilometragemCrud->getAllWithVehicle();

// Definição dos cabeçalhos para a tabela
$headers = [
    'id_registro_km' => 'ID',
    'placa' => 'Veículo',
    'data_registro' => 'Data',
    'km_inicial' => 'KM Inicial',
    'km_final' => 'KM Final',
    'km_percorrida' => 'KM Percorrida',
    'observacoes' => 'Observações',
];
?>

<h2>Gestão de Quilometragem</h2>

<?php echo $message; ?>

<div class="form-section">
    <h3><?php echo $quilometragem_edit ? 'Editar Registro' : 'Novo Registro de Quilometragem'; ?></h3>
    <form method="POST" action="quilometragem.php">
        <?php if ($quilometragem_edit): ?>
            <input type="hidden" name="id_registro_km" value="<?php echo $quilometragem_edit['id_registro_km']; ?>">
        <?php endif; ?>

        <label for="id_veiculo">Veículo:</label>
        <select id="id_veiculo" name="id_veiculo" required>
            <option value="">-- Selecione --</option>
            <?php foreach ($veiculos as $veiculo): ?>
                <option value="<?php echo $veiculo['id_veiculo']; ?>" 
                    <?php echo ($quilometragem_edit['id_veiculo'] ?? '') == $veiculo['id_veiculo'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($veiculo['placa'] . ' - ' . $veiculo['modelo']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="data_registro">Data do Registro:</label>
        <input type="date" id="data_registro" name="data_registro" value="<?php echo $quilometragem_edit['data_registro'] ?? date('Y-m-d'); ?>" required>

        <label for="km_inicial">KM Inicial:</label>
        <input type="number" id="km_inicial" name="km_inicial" value="<?php echo $quilometragem_edit['km_inicial'] ?? ''; ?>" required>

        <label for="km_final">KM Final:</label>
        <input type="number" id="km_final" name="km_final" value="<?php echo $quilometragem_edit['km_final'] ?? ''; ?>" required>

        <label for="observacoes">Observações:</label>
        <textarea id="observacoes" name="observacoes"><?php echo $quilometragem_edit['observacoes'] ?? ''; ?></textarea>

        <button type="submit"><?php echo $quilometragem_edit ? 'Salvar Alterações' : 'Cadastrar Registro'; ?></button>
        <?php if ($quilometragem_edit): ?>
            <a href="quilometragem.php" class="btn btn-secondary">Cancelar Edição</a>
        <?php endif; ?>
    </form>
</div>

<div class="list-section">
    <h3>Histórico de Quilometragem</h3>
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
            <?php if ($quilometragens): ?>
                <?php foreach ($quilometragens as $quilometragem): ?>
                    <tr>
                        <td><?php echo $quilometragem['id_registro_km']; ?></td>
                        <td><?php echo htmlspecialchars($quilometragem['placa'] . ' - ' . $quilometragem['modelo']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($quilometragem['data_registro'])); ?></td>
                        <td><?php echo number_format($quilometragem['km_inicial'], 0, ',', '.'); ?></td>
                        <td><?php echo number_format($quilometragem['km_final'], 0, ',', '.'); ?></td>
                        <td><?php echo number_format($quilometragem['km_percorrida'], 0, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($quilometragem['observacoes']); ?></td>
                        <td>
                            <a href="quilometragem.php?id=<?php echo $quilometragem['id_registro_km']; ?>" class="btn btn-edit">Editar</a>
                            <a href="quilometragem.php?action=delete&id=<?php echo $quilometragem['id_registro_km']; ?>" class="btn btn-delete" onclick="return confirm('Tem certeza que deseja excluir este registro?')">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="<?php echo count($headers) + 1; ?>">Nenhum registro encontrado.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once '../includes/footer.php';
?>
