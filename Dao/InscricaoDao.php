<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../model/Inscricao.php';
require_once __DIR__ . '/../model/Formando.php';
require_once __DIR__ . '/../model/Modulo.php';
require_once __DIR__ . '/../model/Quali_Nivel.php';
require_once __DIR__ . '/../model/Qualificacao.php';
require_once __DIR__ . '/../model/Turma.php';
require_once __DIR__ . '/../model/Logs.php';
require_once __DIR__ . '/LogDao.php';

class InscricaoDao {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Constrói o objeto Turma a partir da linha do SELECT.
     * Se o construtor de Turma for diferente, ajuste apenas este método.
     */
    private function montarTurma($codigo, $nome) {
        return new Turma($codigo, $nome, null, null, null);
    }

    public function create(Inscricao $inscricao) {
        $sql = "insert into Inscricao (codigo_formando, codigo_modulo, semestre, data_inscricao, codigo_turma) values (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        $codigoFormando = $inscricao->getFormando()->getCodigo();
        $codigoModulo = $inscricao->getModulo()->getCodigo();
        $semestre = $inscricao->getSemestre();
        $dataInscricao = $inscricao->getDataInscricao();
        $codigoTurma = $inscricao->getTurma() !== null ? (int) $inscricao->getTurma()->getCodigo() : null;

        $stmt->bind_param("iissi", $codigoFormando, $codigoModulo, $semestre, $dataInscricao, $codigoTurma);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "INSERT", "Inscrição para o semestre " . $semestre . " foi cadastrada", $usuario);
                $log->setData(date('d/m/Y H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    /**
     * Regista várias inscrições numa única transação (tudo ou nada).
     */
    public function createMany(array $inscricoes) {
        if (empty($inscricoes)) {
            return false;
        }

        $this->db->begin_transaction();
        try {
            foreach ($inscricoes as $inscricao) {
                if (!$this->create($inscricao)) {
                    throw new mysqli_sql_exception("Falha ao registar uma inscrição.");
                }
            }
            $this->db->commit();
            return true;
        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function getAll($pesquisa = null, $codCampo = null) {
        $sql = "select i.codigo_inscricao, i.codigo_formando, i.codigo_modulo, i.semestre, i.data_inscricao,
                       i.codigo_turma, t.nome as nome_turma,
                       f.nome_formando, f.apelido_formando, m.nome_modulo,
                       qn.codigo_Quali_Nivel, q.cod_Quali, q.titulo,
                       (select min(cl.cod_Campo)
                          from Classificacao cl
                         where cl.cod_Qualificacao = qn.cod_Quali) as campo_cod
                from Inscricao i
                join Formando f on f.codigo_formando = i.codigo_formando
                join Modulo m on m.codigo = i.codigo_modulo
                left join Turma t on t.codigo = i.codigo_turma
                left join Quali_Nivel qn on qn.codigo_Quali_Nivel = m.id_Quali_Nivel
                left join Qualificacao q on q.cod_Quali = qn.cod_Quali
                where (i.semestre like ?
                   or f.nome_formando like ?
                   or f.apelido_formando like ?
                   or m.nome_modulo like ?
                   or q.titulo like ?
                   or t.nome like ?
                   or CAST(i.data_inscricao as CHAR) like ?)";
        if ($codCampo !== null && (int) $codCampo > 0) {
            $sql .= " and f.codCampo = ?";
        }
        $stmt = $this->db->prepare($sql);
        $busca = "%" . $pesquisa . "%";
        if ($codCampo !== null && (int) $codCampo > 0) {
            $stmt->bind_param("sssssssi", $busca, $busca, $busca, $busca, $busca, $busca, $busca, $codCampo);
        } else {
            $stmt->bind_param("sssssss", $busca, $busca, $busca, $busca, $busca, $busca, $busca);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $inscricoes = [];
        while ($rs = $result->fetch_assoc()) {
            $formando = new Formando($rs['codigo_formando'], $rs['nome_formando'], $rs['apelido_formando'], null, null, null);
            $modulo = new Modulo($rs['codigo_modulo'], $rs['nome_modulo'], null);

            if ($rs['cod_Quali'] !== null) {
                $qualificacao = new Qualificacao($rs['cod_Quali'], $rs['titulo'], null);
                $qualificacao->setCodCampo($rs['campo_cod'] ?? null);
                $qualiNivel = new Quali_Nivel($rs['codigo_Quali_Nivel'], $rs['cod_Quali'], null);
                $qualiNivel->setQualificacao($qualificacao);
                $modulo->setQualiNivel($qualiNivel);
            }

            $inscricao = new Inscricao(
                $rs['codigo_inscricao'],
                $formando,
                $modulo,
                $rs['semestre'],
                $rs['data_inscricao']
            );

            if ($rs['codigo_turma'] !== null) {
                $inscricao->setTurma($this->montarTurma($rs['codigo_turma'], $rs['nome_turma']));
            }

            $inscricoes[] = $inscricao;
        }
        return $inscricoes;
    }

    public function update(Inscricao $inscricao) {
        $sql = "update Inscricao set codigo_formando = ?, codigo_modulo = ?, semestre = ?, data_inscricao = ? where codigo_inscricao = ?";
        $stmt = $this->db->prepare($sql);

        $codigoFormando = $inscricao->getFormando()->getCodigo();
        $codigoModulo = $inscricao->getModulo()->getCodigo();
        $semestre = $inscricao->getSemestre();
        $dataInscricao = $inscricao->getDataInscricao();
        $codigo = $inscricao->getCodigo();

        $stmt->bind_param("iissi", $codigoFormando, $codigoModulo, $semestre, $dataInscricao, $codigo);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "UPDATE", "Inscrição (ID: " . $codigo . ") para o semestre " . $semestre . " foi atualizada", $usuario);
                $log->setData(date('d/m/Y H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function delete($codigo) {
        $semestre = "";
        $sqlBuscar = "select semestre from Inscricao where codigo_inscricao = ?";
        $stmtBuscar = $this->db->prepare($sqlBuscar);
        $stmtBuscar->bind_param("i", $codigo);
        $stmtBuscar->execute();
        $rs = $stmtBuscar->get_result()->fetch_assoc();
        if ($rs != null) {
            $semestre = $rs['semestre'];
        }

        $sql = "delete from Inscricao where codigo_inscricao = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "DELETE", "Inscrição (ID: " . $codigo . ") para o semestre " . $semestre . " foi removida", $usuario);
                $log->setData(date('d/m/Y H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function existeInscricaoPorFormando($codigoFormando) {
        $sql = "select count(*) as total from Inscricao where codigo_formando = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoFormando);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs['total'] > 0;
    }

    public function existeInscricaoPorModulo($codigoModulo) {
        $sql = "select count(*) as total from Inscricao where codigo_modulo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoModulo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs['total'] > 0;
    }

    public function existeInscricaoPorFormandoModulo($codigoFormando, $codigoModulo) {
        $sql = "select count(*) as total from Inscricao where codigo_formando = ? and codigo_modulo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $codigoFormando, $codigoModulo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return (int) $rs['total'] > 0;
    }
}
?>
