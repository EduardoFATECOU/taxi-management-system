<?php
require_once 'Database.php';

class Relatorio {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Relatório de Faturamento por Período
     * @param string $data_inicio
     * @param string $data_fim
     * @return array
     */
    public function faturamentoPorPeriodo($data_inicio, $data_fim) {
        $sql = "SELECT 
                    SUM(CASE WHEN tipo_movimentacao = 'Entrada' THEN valor ELSE 0 END) AS total_entradas,
                    SUM(CASE WHEN tipo_movimentacao = 'Saída' THEN valor ELSE 0 END) AS total_saidas,
                    SUM(CASE WHEN tipo_movimentacao = 'Entrada' THEN valor ELSE -valor END) AS saldo_liquido
                FROM movimentacao_financeira
                WHERE data_movimentacao BETWEEN :data_inicio AND :data_fim";
        
        return $this->db->fetchOne($sql, [
            'data_inicio' => $data_inicio,
            'data_fim' => $data_fim
        ]);
    }
    
    /**
     * Relatório de Corridas Concluídas por Motorista
     * @param string $data_inicio
     * @param string $data_fim
     * @return array
     */
    public function corridasPorMotorista($data_inicio, $data_fim) {
        $sql = "SELECT 
                    m.nome as motorista,
                    COUNT(r.id_reserva) as total_corridas,
                    SUM(r.valor_final) as faturamento_bruto,
                    SUM(r.distancia_km) as total_km
                FROM motoristas m
                JOIN reservas r ON m.id_motorista = r.id_motorista
                WHERE r.status_viagem = 'Concluída'
                AND r.data_hora_fim_viagem BETWEEN :data_inicio AND :data_fim
                GROUP BY m.nome
                ORDER BY total_corridas DESC";
        
        return $this->db->fetchAll($sql, [
            'data_inicio' => $data_inicio . ' 00:00:00',
            'data_fim' => $data_fim . ' 23:59:59'
        ]);
    }
    
    /**
     * Relatório de Gastos com Manutenção por Veículo
     * @param string $data_inicio
     * @param string $data_fim
     * @return array
     */
    public function gastosManutencaoPorVeiculo($data_inicio, $data_fim) {
        $sql = "SELECT
                    v.placa,
                    v.modelo,
                    SUM(mv.custo) as custo_total
                FROM veiculos v
                JOIN manutencao_veiculos mv ON v.id_veiculo = mv.id_veiculo
                WHERE mv.data_manutencao BETWEEN :data_inicio AND :data_fim
                GROUP BY v.placa, v.modelo
                ORDER BY custo_total DESC";
        
        return $this->db->fetchAll($sql, [
            'data_inicio' => $data_inicio,
            'data_fim' => $data_fim
        ]);
    }
}
?>
