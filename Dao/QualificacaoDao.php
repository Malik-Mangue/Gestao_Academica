<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../services/Sessao.php';
require_once __DIR__ . '/../model/Qualificacao.php';
require_once __DIR__ . '/../model/Logs.php';
require_once __DIR__ . '/LogDao.php';

class QualificacaoDao {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAll($titulo = null) {
        $sql = "select q.cod_Quali, q.titulo, q.cod_Coordenador,
                       (select group_concat(c.nome order by c.nome separator ', ')
                          from Classificacao cl
                          join Campo c on c.codigo = cl.cod_Campo
                         where cl.cod_Qualificacao = q.cod_Quali) as campo,
                       (select group_concat(n.nome order by n.nome separator ', ')
                          from Quali_Nivel qn
                          join Nivel n on n.codigo = qn.cod_Nivel
                         where qn.cod_Quali = q.cod_Quali) as niveis
                from Qualificacao q";

        if ($titulo != null && strlen($titulo) > 0) {
            $sql .= " where q.titulo like ? order by q.cod_Quali";
            $stmt = $this->db->prepare($sql);
            $busca = "%" . $titulo . "%";
            $stmt->bind_param("s", $busca);
        } else {
            $sql .= " order by q.cod_Quali";
            $stmt = $this->db->prepare($sql);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $qualificacaos = [];
        while ($rs = $result->fetch_assoc()) {
            $qualificacao = new Qualificacao($rs['cod_Quali'], $rs['titulo'], $rs['cod_Coordenador']);
            $qualificacao->setCampo($rs['campo']);       // NOVO
            $qualificacao->setNiveis($rs['niveis']);    // NOVO
            $qualificacaos[] = $qualificacao;
        }
        return $qualificacaos;
    }

    public function getById($codigo) {
        $sql = "select cod_Quali, titulo, cod_Coordenador from Qualificacao where cod_Quali = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        if ($rs === null) {
            return null;
        }
        return new Qualificacao($rs['cod_Quali'], $rs['titulo'], $rs['cod_Coordenador']);
    }

    public function create(Qualificacao $qualificacao) {
        $sql = "insert into Qualificacao (titulo, cod_Coordenador) values (?, ?)";
        $stmt = $this->db->prepare($sql);
        $titulo = $qualificacao->getTitulo();
        $cod_coordenador = $qualificacao->getCod_coordenador();
        $stmt->bind_param("si", $titulo, $cod_coordenador);
        try {
            $result = $stmt->execute();
            if ($result) {
                $qualificacao->setCodigo($this->db->insert_id);
                $this->registarLog("INSERT", "Qualificação " . $titulo . " foi cadastrada");
            }
            return $result;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function update(Qualificacao $qualificacao) {
        $sql = "update Qualificacao set titulo = ?, cod_Coordenador = ? where cod_Quali = ?";
        $stmt = $this->db->prepare($sql);
        $titulo = $qualificacao->getTitulo();
        $cod_coordenador = $qualificacao->getCod_coordenador();
        $codigo = $qualificacao->getCodigo();
        $stmt->bind_param("sii", $titulo, $cod_coordenador, $codigo);
        try {
            $result = $stmt->execute();
            if ($result) {
                $this->registarLog("UPDATE", "Qualificação " . $titulo . " (ID: " . $codigo . ") foi atualizada");
            }
            return $result;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function delete($codigo) {
        $sql = "delete from Qualificacao where cod_Quali = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        try {
            $result = $stmt->execute();
            if ($result) {
                $this->registarLog("DELETE", "Qualificação (ID: " . $codigo . ") foi removida");
            }
            return $result;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    // Apaga a qualificacao e as suas ligacoes (Classificacao e Quali_Nivel)
    // numa transacao: ou sai tudo, ou nao sai nada.
    public function deleteCompleto($codigo) {
        $this->db->begin_transaction();
        try {
            $sqls = [
                "delete from Classificacao where cod_Qualificacao = ?",
                "delete from Quali_Nivel where cod_Quali = ?",
                "delete from Qualificacao where cod_Quali = ?"
            ];
            foreach ($sqls as $sql) {
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("i", $codigo);
                if (!$stmt->execute()) {
                    throw new mysqli_sql_exception("Falha ao executar: " . $sql);
                }
            }
            $this->db->commit();
            $this->registarLog("DELETE", "Qualificação (ID: " . $codigo . ") e as suas associações foram removidas");
            return true;
        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
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

    public function listarOpcoes($sql) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $opcoes = [];
        while ($rs = $result->fetch_assoc()) {
            $opcoes[] = ['codigo' => $rs['codigo'], 'descricao' => $rs['descricao']];
        }
        return $opcoes;
    }

    public function getCoordenadores() {
        return $this->listarOpcoes("select f.codigo as codigo, concat(f.nome, ' ', f.apelido) as descricao from Coordenador c inner join Formador f on f.codigo = c.cod_Formador order by f.nome");
    }

    public function existeQualificacaoEmClassificacao($codigoQualificacao) {
        $sql = "select count(*) as total from Classificacao where cod_Qualificacao = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoQualificacao);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs['total'] > 0;
    }

    public function existeQualificacaoEmQualiNivel($codigoQualificacao) {
        $sql = "select count(*) as total from Quali_Nivel where cod_Quali = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoQualificacao);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs['total'] > 0;
    }

    public function existeQualificacaoEmQualiModulo($codigoQualificacao) {
        $sql = "select count(*) as total from Quali_modulo where cod_Quali = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoQualificacao);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs['total'] > 0;
    }

    public function existeQualificacaoEmModulo($codigoQualificacao) {
        $sql = "select count(*) as total from Modulo m
                join Quali_Nivel qn on qn.codigo_Quali_Nivel = m.id_Quali_Nivel
                where qn.cod_Quali = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoQualificacao);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs['total'] > 0;
    }

    public function existeQualificacaoEmTurma($codigoQualificacao) {
        $sql = "select count(*) as total from Turma t
                join Quali_Nivel qn on qn.codigo_Quali_Nivel = t.id_Quali_Nivel
                where qn.cod_Quali = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoQualificacao);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs['total'] > 0;
    }
}
?>
