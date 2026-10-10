<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../services/Sessao.php';
require_once __DIR__ . '/../model/Perfil.php';
require_once __DIR__ . '/../model/Logs.php';
require_once __DIR__ . '/LogDao.php';

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
            $result = $stmt->execute();
            if ($result) {
                $this->registarLog("INSERT", "Perfil " . $nome . " (ID: " . $id . ") foi cadastrado");
            }
            return $result;
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
            $result = $stmt->execute();
            if ($result) {
                $this->registarLog("UPDATE", "Perfil " . $nome . " (ID: " . $id . ") foi atualizado");
            }
            return $result;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function delete($codigo) {
        $sql = "delete from Perfil where id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        try {
            $result = $stmt->execute();
            if ($result) {
                $this->registarLog("DELETE", "Perfil (ID: " . $codigo . ") foi removido");
            }
            return $result;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    // Regista uma acao de auditoria em nome do utilizador autenticado.
    private function registarLog($acao, $descricao) {
        $usuario = Sessao::obterUtilizador();
        if ($usuario != null) {
            $log = new Logs(null, $acao, $descricao, $usuario);
            $log->setData(date('Y-m-d H:i:s'));
            (new LogDao())->salvar($log);
        }
    }
}
?>
