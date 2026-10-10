<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../model/Permissao.php';

/**
 * Acesso a tabela permissao (consultar, criar, editar, eliminar, ...).
 * Uma nova permissao e sempre um novo INSERT nesta tabela: o motor de
 * autorizacao (Sessao) aceita tanto o codigo curto como o nome da permissao.
 */
class PermissaoDao {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    private function montar($rs) {
        return new Permissao($rs['id'], $rs['nome']);
    }

    public function getAll() {
        $sql = "select id, nome from permissao order by id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $permissoes = [];
        while ($rs = $result->fetch_assoc()) {
            $permissoes[] = $this->montar($rs);
        }
        return $permissoes;
    }

    public function getById($codigo) {
        $sql = "select id, nome from permissao where id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs === null ? null : $this->montar($rs);
    }

    public function getByNome($nome) {
        $sql = "select id, nome from permissao where nome = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $nome);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs === null ? null : $this->montar($rs);
    }
}
?>
