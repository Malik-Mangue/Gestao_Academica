<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../model/Perfil.php';

class PerfilDAO {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAll() {
        $sql = "select id, nome from Perfil order by id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $perfis = [];
        while ($rs = $result->fetch_assoc()) {
            $perfis[] = new Perfil($rs['id'], $rs['nome']);
        }
        return $perfis;
    }

    public function getById($codigo) {
        $sql = "select id, nome from Perfil where id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        if ($rs === null) {
            return null;
        }
        return new Perfil($rs['id'], $rs['nome']);
    }

    public function create(Perfil $perfil) {
        $sql = "insert into Perfil (id, nome) values (?, ?)";
        $stmt = $this->db->prepare($sql);
        $id = $perfil->getId();
        $nome = $perfil->getNome();
        $stmt->bind_param("is", $id, $nome);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function update(Perfil $perfil) {
        $sql = "update Perfil set nome = ? where id = ?";
        $stmt = $this->db->prepare($sql);
        $nome = $perfil->getNome();
        $id = $perfil->getId();
        $stmt->bind_param("si", $nome, $id);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function delete($codigo) {
        $sql = "delete from Perfil where id = ?";
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
