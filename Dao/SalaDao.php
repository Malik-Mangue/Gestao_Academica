<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../model/Sala.php';

class SalaDAO {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAll() {
        $sql = "select codigo, designacao, tipo_sala from Sala order by designacao";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $salas = [];
        while ($rs = $result->fetch_assoc()) {
            $salas[] = new Sala($rs['codigo'], $rs['designacao'], $rs['tipo_sala']);
        }
        return $salas;
    }

    public function getById($codigo) {
        $sql = "select codigo, designacao, tipo_sala from Sala where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        if ($rs === null) {
            return null;
        }
        return new Sala($rs['codigo'], $rs['designacao'], $rs['tipo_sala']);
    }

    public function create(Sala $sala) {
        $sql = "insert into Sala (designacao, tipo_sala) values (?, ?)";
        $stmt = $this->db->prepare($sql);
        $designacao = $sala->getDesignacao();
        $tipo_sala = $sala->getTipo_sala();
        $stmt->bind_param("ss", $designacao, $tipo_sala);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function update(Sala $sala) {
        $sql = "update Sala set designacao = ?, tipo_sala = ? where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $designacao = $sala->getDesignacao();
        $tipo_sala = $sala->getTipo_sala();
        $codigo = $sala->getCodigo();
        $stmt->bind_param("ssi", $designacao, $tipo_sala, $codigo);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function delete($codigo) {
        $sql = "delete from Sala where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
}
?>
