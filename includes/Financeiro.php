<?php
require_once 'Crud.php';

class MovimentacaoFinanceira extends Crud {
    public function __construct() {
        parent::__construct('movimentacao_financeira', 'id_movimentacao');
    }
    
    // Método para obter o saldo total (Entradas - Saídas)
    public function getSaldoTotal() {
        $sql = "SELECT SUM(CASE WHEN tipo_movimentacao = 'Entrada' THEN valor ELSE -valor END) AS saldo FROM movimentacao_financeira";
        $result = $this->db->fetchOne($sql);
        return $result['saldo'] ?? 0.00;
    }
    
    // Método para obter o total de entradas
    public function getTotalEntradas() {
        $sql = "SELECT SUM(valor) AS total FROM movimentacao_financeira WHERE tipo_movimentacao = 'Entrada'";
        $result = $this->db->fetchOne($sql);
        return $result['total'] ?? 0.00;
    }
    
    // Método para obter o total de saídas
    public function getTotalSaidas() {
        $sql = "SELECT SUM(valor) AS total FROM movimentacao_financeira WHERE tipo_movimentacao = 'Saída'";
        $result = $this->db->fetchOne($sql);
        return $result['total'] ?? 0.00;
    }
}

class Pagamento extends Crud {
    public function __construct() {
        parent::__construct('pagamentos', 'id_pagamento');
    }
    
    // Método para buscar pagamentos com dados da reserva e cliente
    public function getAllPagamentos() {
        $sql = "SELECT p.*, r.id_reserva, r.origem, r.destino, c.nome as nome_cliente
                FROM pagamentos p
                JOIN reservas r ON p.id_reserva = r.id_reserva
                JOIN clientes c ON r.id_cliente = c.id_cliente
                ORDER BY p.data_pagamento DESC";
        return $this->db->fetchAll($sql);
    }
}

class SalarioComissao extends Crud {
    public function __construct() {
        parent::__construct('salarios_comissoes', 'id_registro');
    }
    
    // Método para buscar salários com nome do motorista
    public function getAllSalarios() {
        $sql = "SELECT sc.*, m.nome as nome_motorista
                FROM salarios_comissoes sc
                JOIN motoristas m ON sc.id_motorista = m.id_motorista
                ORDER BY sc.mes_referencia DESC, m.nome ASC";
        return $this->db->fetchAll($sql);
    }
}

class ManutencaoVeiculo extends Crud {
    public function __construct() {
        parent::__construct('manutencao_veiculos', 'id_manutencao');
    }
    
    // Método para buscar manutenções com placa do veículo
    public function getAllManutencoes() {
        $sql = "SELECT mv.*, v.placa, v.modelo
                FROM manutencao_veiculos mv
                JOIN veiculos v ON mv.id_veiculo = v.id_veiculo
                ORDER BY mv.data_manutencao DESC";
        return $this->db->fetchAll($sql);
    }
}

class RegistroQuilometragem extends Crud {
    public function __construct() {
        parent::__construct('registro_quilometragem', 'id_registro_km');
    }
    
    // Método para buscar registros com placa do veículo
    public function getAllRegistros() {
        $sql = "SELECT rq.*, v.placa, v.modelo
                FROM registro_quilometragem rq
                JOIN veiculos v ON rq.id_veiculo = v.id_veiculo
                ORDER BY rq.data_registro DESC";
        return $this->db->fetchAll($sql);
    }
}
?>
