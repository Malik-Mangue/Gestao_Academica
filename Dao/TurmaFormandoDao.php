<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../services/Sessao.php';
require_once __DIR__ . '/../model/Turma_Formando.php';
require_once __DIR__ . '/../model/Logs.php';
require_once __DIR__ . '/LogDao.php';

class TurmaFormandoDao {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Formandos (via matrícula) de uma turma
    public function listarPorTurma($codTurma) {
        $sql = "select tf.cod_Turma, tf.cod_Matricula, tf.data_inscricao,
                       f.codigo_formando, f.nome_formando, f.apelido_formando,
                       f.contacto_formando, f.email, f.BI, f.codCampo
                from Turma_Formando tf
                join Matricula m on m.codigo = tf.cod_Matricula
                join Formando f on f.codigo_formando = m.cod_formando
                where tf.cod_Turma = ?
                order by f.nome_formando, f.apelido_formando";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codTurma);
        $stmt->execute();
        $result = $stmt->get_result();

        $lista = [];
        while ($rs = $result->fetch_assoc()) {
            $lista[] = new Turma_Formando($rs['cod_Turma'], $rs['cod_Matricula'], $rs['data_inscricao'], null, $rs);
        }
        return $lista;
    }

    // Matrículas compatíveis com a turma e ainda sem turma (para o select de inscrição)
    public function listarDisponiveis($codTurma) {
        $sql = "select m.codigo as codigo,
                       concat(f.nome_formando, ' ', f.apelido_formando, ' (matrícula ', m.codigo, ')') as descricao
                from Turma t
                join Matricula m on m.id_Quali_Nivel = t.id_Quali_Nivel
                join Formando f on f.codigo_formando = m.cod_formando
                where t.codigo = ?
                  and m.codigo not in (select cod_Matricula from Turma_Formando)
                order by f.nome_formando";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codTurma);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Turmas com vaga para uma matrícula (usar no ecrã de matrículas)
    public function listarTurmasCompativeis($codMatricula) {
        $sql = "select t.codigo, t.nome, t.turno, t.capacidade_maxima,
                       (select count(*) from Turma_Formando tf where tf.cod_Turma = t.codigo) as inscritos
                from Matricula m
                join Turma t on t.id_Quali_Nivel = m.id_Quali_Nivel
                where m.codigo = ?
                having inscritos < t.capacidade_maxima
                order by t.nome";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codMatricula);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function contarInscritos($codTurma) {
        $stmt = $this->db->prepare("select count(*) as total from Turma_Formando where cod_Turma = ?");
        $stmt->bind_param("i", $codTurma);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }

    // A matrícula tem de existir e ser da mesma qualificação/nível da turma
    private function matriculaCompativel($codTurma, $codMatricula) {
        $sql = "select count(*) as total
                from Matricula m
                join Turma t on t.id_Quali_Nivel = m.id_Quali_Nivel
                where m.codigo = ? and t.codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $codMatricula, $codTurma);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total'] > 0;
    }

    public function adicionar(Turma_Formando $tf) {
        $codTurma = $tf->getCodTurma();
        $codMatricula = $tf->getCodMatricula();

        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare("select nome, capacidade_maxima from Turma where codigo = ? for update");
            $stmt->bind_param("i", $codTurma);
            $stmt->execute();
            $turma = $stmt->get_result()->fetch_assoc();
            if (!$turma) {
                throw new Exception("Turma inexistente.");
            }

            if (!$this->matriculaCompativel($codTurma, $codMatricula)) {
                throw new Exception("A matrícula não existe ou não corresponde à qualificação/nível desta turma.");
            }

            if ($this->contarInscritos($codTurma) >= (int) $turma['capacidade_maxima']) {
                throw new Exception("A turma já atingiu a capacidade máxima.");
            }

            $stmt = $this->db->prepare("insert into Turma_Formando (cod_Matricula, cod_Turma) values (?, ?)");
            $stmt->bind_param("ii", $codMatricula, $codTurma);
            $stmt->execute();

            $this->db->commit();
        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            if ($e->getCode() == 1062) {
                throw new Exception("Esta matrícula já está associada a uma turma.");
            }
            throw $e;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        $this->registarLog("INSERT", "Matrícula (ID: $codMatricula) associada à turma " . $turma['nome']);
        return true;
    }

    public function remover($codTurma, $codMatricula) {
        $stmt = $this->db->prepare("delete from Turma_Formando where cod_Turma = ? and cod_Matricula = ?");
        $stmt->bind_param("ii", $codTurma, $codMatricula);
        $ok = $stmt->execute();
        if ($ok && $stmt->affected_rows > 0) {
            $this->registarLog("DELETE", "Matrícula (ID: $codMatricula) removida da turma (ID: $codTurma)");
        }
        return $ok;
    }

    private function registarLog($acao, $descricao) {
        $usuario = Sessao::obterUtilizador();
        if ($usuario != null) {
            $log = new Logs(null, $acao, $descricao, $usuario);
            $log->setData(date('d/m/Y H:i:s'));
            (new LogDao())->salvar($log);
        }
    }
}
?>
