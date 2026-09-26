<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../model/Nivel.php';

class NivelDAO {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAll() {
        $sql = "select codigo, nome from Nivel order by codigo";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $niveis = [];
        while ($rs = $result->fetch_assoc()) {
            $niveis[] = new Nivel($rs['codigo'], $rs['nome']);
        }
        return $niveis;
    }

    public function getById($codigo) {
        $sql = "select codigo, nome from Nivel where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        if ($rs === null) {
            return null;
        }
        return new Nivel($rs['codigo'], $rs['nome']);
    }

    public function create(Nivel $nivel) {
        $sql = "insert into Nivel (nome) values (?)";
        $stmt = $this->db->prepare($sql);
        $nome = $nivel->getNome();
        $stmt->bind_param("s", $nome);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function update(Nivel $nivel) {
        $sql = "update Nivel set nome = ? where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $nome = $nivel->getNome();
        $codigo = $nivel->getCodigo();
        $stmt->bind_param("si", $nome, $codigo);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function delete($codigo) {
        $sql = "delete from Nivel where codigo = ?";
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