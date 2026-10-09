<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../services/Sessao.php';
require_once __DIR__ . '/../model/Formando.php';
require_once __DIR__ . '/../model/Logs.php';
require_once __DIR__ . '/LogDao.php';

class FormandoDAO {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Ordem do construtor Formando: (codigo, nome, apelido, email, bi, contacto)
    private function montarFormando($rs) {
        return new Formando(
            $rs['codigo_formando'],
            $rs['nome_formando'],
            $rs['apelido_formando'],
            $rs['email'],
            $rs['BI'],
            $rs['contacto_formando']
        );
    }

    public function getAll($pesquisa = null) {
        $sql = "select codigo_formando, nome_formando, apelido_formando, contacto_formando, email, BI
                from Formando";
        if ($pesquisa !== null && strlen($pesquisa) > 0) {
            $sql .= " where nome_formando like ? or apelido_formando like ? or email like ?
                            or BI like ? or contacto_formando like ?
                      order by nome_formando, apelido_formando";
            $stmt = $this->db->prepare($sql);
            $busca = "%" . $pesquisa . "%";
            $stmt->bind_param("sssss", $busca, $busca, $busca, $busca, $busca);
        } else {
            $sql .= " order by nome_formando, apelido_formando";
            $stmt = $this->db->prepare($sql);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $formandos = [];
        while ($rs = $result->fetch_assoc()) {
            $formandos[] = $this->montarFormando($rs);
        }
        return $formandos;
    }

    public function getById($codigo) {
        $sql = "select codigo_formando, nome_formando, apelido_formando, contacto_formando, email, BI
                from Formando where codigo_formando = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();

        if ($rs === null) {
            return null;
        }
        return $this->montarFormando($rs);
    }

    public function create(Formando $formando) {
        $sql = "insert into Formando (nome_formando, apelido_formando, contacto_formando, email, BI) values (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $nome = $formando->getNome();
        $apelido = $formando->getApelido();
        $contacto = $formando->getContacto();
        $email = $formando->getEmail();
        $bi = $formando->getBi();
        $stmt->bind_param("ssiss", $nome, $apelido, $contacto, $email, $bi);
        try {
            $result = $stmt->execute();
            if ($result) {
                $this->registarLog("INSERT", "Formando " . $nome . " " . $apelido . " foi cadastrado");
            }
            return $result;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function update(Formando $formando) {
        $sql = "update Formando set nome_formando = ?, apelido_formando = ?, contacto_formando = ?, email = ?, BI = ? where codigo_formando = ?";
        $stmt = $this->db->prepare($sql);
        $nome = $formando->getNome();
        $apelido = $formando->getApelido();
        $contacto = $formando->getContacto();
        $email = $formando->getEmail();
        $bi = $formando->getBi();
        $codigo = $formando->getCodigo();
        $stmt->bind_param("ssissi", $nome, $apelido, $contacto, $email, $bi, $codigo);
        try {
            $result = $stmt->execute();
            if ($result) {
                $this->registarLog("UPDATE", "Formando " . $nome . " " . $apelido . " (ID: " . $codigo . ") foi atualizado");
            }
            return $result;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function delete($codigo) {
        $sql = "delete from Formando where codigo_formando = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        try {
            $result = $stmt->execute();
            if ($result) {
                $this->registarLog("DELETE", "Formando (ID: " . $codigo . ") foi removido");
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
