<?php
require_once 'Crud.php';

class Reserva extends Crud {
    public function __construct() {
        parent::__construct('reservas', 'id_reserva');
    }

    /**
     * Busca todas as reservas com os nomes do cliente, motorista e placa do veículo.
     * @param string $status Opcional, filtra por status da viagem.
     * @return array|bool
     */
    public function getAllReservas($status = null) {
        $sql = "SELECT r.*, c.nome as nome_cliente, m.nome as nome_motorista, v.placa
                FROM reservas r
                JOIN clientes c ON r.id_cliente = c.id_cliente
                LEFT JOIN motoristas m ON r.id_motorista = m.id_motorista
                LEFT JOIN veiculos v ON r.id_veiculo = v.id_veiculo";
        
        $params = [];
        if ($status) {
            $sql .= " WHERE r.status_viagem = :status";
            $params['status'] = $status;
        }
        
        $sql .= " ORDER BY r.data_hora_reserva DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Atualiza o status de uma reserva.
     * @param int $id_reserva
     * @param string $status
     * @return bool
     */
    public function updateStatus($id_reserva, $status) {
        $data = ['status_viagem' => $status];
        
        // Se a viagem for concluída, registra a data/hora de fim
        if ($status === 'Concluída') {
            $data['data_hora_fim_viagem'] = date('Y-m-d H:i:s');
        }
        
        // Se a viagem for confirmada/em andamento, registra a data/hora de início
        if ($status === 'Confirmada' || $status === 'Em Andamento') {
            $data['data_hora_inicio_viagem'] = date('Y-m-d H:i:s');
        }
        
        return $this->update($id_reserva, $data);
    }
}
?>
