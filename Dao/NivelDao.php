<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../services/Sessao.php';
require_once __DIR__ . '/../model/Nivel.php';
require_once __DIR__ . '/../model/Logs.php';
require_once __DIR__ . '/LogDao.php';

class NivelDAO {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAll($pesquisa = null) {
        $sql = "select codigo, nome from Nivel";
        if ($pesquisa !== null && strlen($pesquisa) > 0) {
            $sql .= " where nome like ? order by codigo";
            $stmt = $this->db->prepare($sql);
            $busca = "%" . $pesquisa . "%";
            $stmt->bind_param("s", $busca);
        } else {
            $sql .= " order by codigo";
            $stmt = $this->db->prepare($sql);
        }
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
            $result = $stmt->execute();
            if ($result) {
                $this->registarLog("INSERT", "Nível " . $nome . " foi cadastrado");
            }
            return $result;
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
            $result = $stmt->execute();
            if ($result) {
                $this->registarLog("UPDATE", "Nível " . $nome . " (ID: " . $codigo . ") foi atualizado");
            }
            return $result;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function delete($codigo) {
        $sql = "delete from Nivel where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        try {
            $result = $stmt->execute();
            if ($result) {
                $this->registarLog("DELETE", "Nível (ID: " . $codigo . ") foi removido");
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