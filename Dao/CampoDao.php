<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../model/Campo.php';

class CampoDAO {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAll() {
        $sql = "select * from Campo";
        $result = mysqli_query($this->db, $sql);
        $campos = [];
        while ($rs = mysqli_fetch_assoc($result)) {
            $campos[] = new Campo($rs['codigo'], $rs['nome']);
        }
        return $campos;
    }

    public function getById($codigo) {
        $sql = "select * from Campo where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return new Campo($rs['codigo'], $rs['nome']);
    }

    public function create(Campo $campo) {
        $sql = "insert into Campo (nome) values (?)";
        $stmt = $this->db->prepare($sql);
        $nome = $campo->getNome();
        $stmt->bind_param("s", $nome);
        return $stmt->execute();
    }

    public function update(Campo $campo) {
        $sql = "update Campo set nome = ? where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $nome = $campo->getNome();
        $codigo = $campo->getCodigo();
        $stmt->bind_param("si", $nome, $codigo);
        return $stmt->execute();
    }

    public function delete($codigo) {
        $sql = "delete from Campo where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        return $stmt->execute();
    }
}
?>
