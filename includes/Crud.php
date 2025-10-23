<?php
require_once 'Database.php';

class Crud {
    protected $db;
    protected $table;
    protected $primaryKey;

    public function __construct($table, $primaryKey) {
        $this->db = Database::getInstance();
        $this->table = $table;
        $this->primaryKey = $primaryKey;
    }

    /**
     * Busca todos os registros da tabela.
     * @param string $orderBy Coluna para ordenação.
     * @return array|bool
     */
    public function getAll($orderBy = null) {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        return $this->db->fetchAll($sql);
    }

    /**
     * Busca um registro pelo ID.
     * @param int $id
     * @return array|bool
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    /**
     * Insere um novo registro.
     * @param array $data Dados a serem inseridos (coluna => valor).
     * @return int|bool ID do novo registro ou false em caso de falha.
     */
    public function create($data) {
        $fields = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";

        if ($this->db->execute($sql, $data)) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Atualiza um registro existente.
     * @param int $id ID do registro a ser atualizado.
     * @param array $data Dados a serem atualizados (coluna => valor).
     * @return bool
     */
    public function update($id, $data) {
        $setClauses = [];
        foreach (array_keys($data) as $field) {
            $setClauses[] = "{$field} = :{$field}";
        }
        $setClause = implode(', ', $setClauses);
        $data[$this->primaryKey] = $id;

        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :{$this->primaryKey}";
        
        return $this->db->execute($sql, $data);
    }

    /**
     * Deleta um registro.
     * @param int $id ID do registro a ser deletado.
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }
}

// Classes específicas para cada módulo
class Cliente extends Crud {
    public function __construct() {
        parent::__construct('clientes', 'id_cliente');
    }
}

class Motorista extends Crud {
    public function __construct() {
        parent::__construct('motoristas', 'id_motorista');
    }
    
    // Método para buscar motoristas disponíveis (sem veículo associado ou status 'Disponível')
    public function getAvailableDrivers() {
        $sql = "SELECT id_motorista, nome FROM motoristas WHERE status_motorista = 'Disponível' OR id_veiculo_atual IS NULL ORDER BY nome";
        return $this->db->fetchAll($sql);
    }
}

class Veiculo extends Crud {
    public function __construct() {
        parent::__construct('veiculos', 'id_veiculo');
    }
    
    // Método para buscar veículos disponíveis
    public function getAvailableVehicles() {
        $sql = "SELECT id_veiculo, placa, marca, modelo FROM veiculos WHERE status_veiculo = 'Disponível' ORDER BY placa";
        return $this->db->fetchAll($sql);
    }
    
    // Método para buscar veículos com nome da categoria
    public function getAllWithCategory() {
        $sql = "SELECT v.*, c.nome_categoria 
                FROM veiculos v
                JOIN categorias_veiculos c ON v.id_categoria = c.id_categoria
                ORDER BY v.placa";
        return $this->db->fetchAll($sql);
    }
}

class CategoriaVeiculo extends Crud {
    public function __construct() {
        parent::__construct('categorias_veiculos', 'id_categoria');
    }
}

class Seguradora extends Crud {
    public function __construct() {
        parent::__construct('seguradoras', 'id_seguradora');
    }
}

class UsuarioSistema extends Crud {
    public function __construct() {
        parent::__construct('usuarios', 'id_usuario');
    }
    
    // Sobrescreve o método create para fazer o hash da senha
    public function create($data) {
        if (isset($data['senha'])) {
            $data['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
        }
        return parent::create($data);
    }
    
    // Sobrescreve o método update para fazer o hash da senha, se for alterada
    public function update($id, $data) {
        if (isset($data['senha']) && !empty($data['senha'])) {
            $data['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
        } else {
            // Remove a senha do array se não foi alterada para evitar hash de string vazia
            unset($data['senha']);
        }
        return parent::update($id, $data);
    }
}

// Classe de Geração de Formulários e Listagens (Helper)
class HtmlHelper {
    public static function createListTable($data, $headers, $primaryKey, $editPage, $deleteAction = true) {
        if (!$data) {
            return "<p>Nenhum registro encontrado.</p>";
        }

        $html = '<table class="data-table">';
        $html .= '<thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '<th>Ações</th>';
        $html .= '</tr></thead>';
        $html .= '<tbody>';

        foreach ($data as $row) {
            $html .= '<tr>';
            foreach (array_keys($headers) as $key) {
                $html .= '<td>' . htmlspecialchars($row[$key] ?? '') . '</td>';
            }
            $html .= '<td>';
            $html .= '<a href="' . htmlspecialchars($editPage) . '?id=' . $row[$primaryKey] . '" class="btn btn-edit">Editar</a>';
            if ($deleteAction) {
                $html .= ' <a href="' . htmlspecialchars($editPage) . '?action=delete&id=' . $row[$primaryKey] . '" class="btn btn-delete" onclick="return confirm(\'Tem certeza que deseja excluir este registro?\')">Excluir</a>';
            }
            $html .= '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }
}
?>
