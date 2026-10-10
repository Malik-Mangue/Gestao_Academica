<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../model/Recurso.php';

class RecursoDao {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    private function montar($rs) {
        return new Recurso($rs['id'], $rs['nome'], $rs['grupo']);
    }

    public function getAll() {
        $sql = "select id, nome, grupo from recurso order by grupo, nome";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $recursos = [];
        while ($rs = $result->fetch_assoc()) {
            $recursos[] = $this->montar($rs);
        }
        return $recursos;
    }

    public function getById($codigo) {
        $sql = "select id, nome, grupo from recurso where id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs === null ? null : $this->montar($rs);
    }

    public function getByNome($nome) {
        $sql = "select id, nome, grupo from recurso where nome = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $nome);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs === null ? null : $this->montar($rs);
    }

    public function getPorGrupo($grupo) {
        $sql = "select id, nome, grupo from recurso where grupo = ? order by nome";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $grupo);
        $stmt->execute();
        $result = $stmt->get_result();
        $recursos = [];
        while ($rs = $result->fetch_assoc()) {
            $recursos[] = $this->montar($rs);
        }
        return $recursos;
    }
}
?>
