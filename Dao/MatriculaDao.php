<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../services/Sessao.php';
require_once __DIR__ . '/../model/Matricula.php';
require_once __DIR__ . '/../model/Formando.php';
require_once __DIR__ . '/../model/Qualificacao.php';
require_once __DIR__ . '/../model/Nivel.php';
require_once __DIR__ . '/../model/Logs.php';
require_once __DIR__ . '/LogDao.php';

class MatriculaDao {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    
    private function anoDaData($data) {
        if (!is_string($data) || !preg_match('/^(\d{4})-\d{2}-\d{2}$/', $data, $m)) {
            return null;
        }
        return $m[1];
    }


    public function existeMatriculaNoAno($codFormando, $data, $ignorarCodigo = 0) {
        $ano = $this->anoDaData($data);
        if ($ano === null) {
            return false;
        }
        $sql = "select count(*) as total from Matricula
                where cod_formando = ? and left(`data`, 4) = ? and codigo <> ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("isi", $codFormando, $ano, $ignorarCodigo);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total'] > 0;
    }

    private function validarAno($codFormando, $data, $ignorarCodigo = 0) {
        $ano = $this->anoDaData($data);
        if ($ano === null) {
            throw new Exception("A data da matrícula deve estar no formato AAAA-MM-DD.");
        }
        if ($this->existeMatriculaNoAno($codFormando, $data, $ignorarCodigo)) {
            throw new Exception("Este formando já tem matrícula no ano " . $ano . ".");
        }
    }

    // Turma: tem de existir, ser da mesma qualificação, do mesmo ano e ter vaga.
    private function validarTurma($codTurma, $codQuali, $data, $ignorarMatricula = 0) {
        if (!$codTurma) {
            return;
        }

        $stmt = $this->db->prepare(
            "select t.nome, t.ano_lectivo, t.capacidade_maxima, qn.cod_Quali
             from Turma t
             join Quali_Nivel qn on qn.codigo_Quali_Nivel = t.id_Quali_Nivel
             where t.codigo = ? for update"
        );
        $stmt->bind_param("i", $codTurma);
        $stmt->execute();
        $turma = $stmt->get_result()->fetch_assoc();

        if (!$turma) {
            throw new Exception("Turma inexistente.");
        }
        if ((int) $turma['cod_Quali'] !== (int) $codQuali) {
            throw new Exception("A turma " . $turma['nome'] . " não corresponde à qualificação da matrícula.");
        }
        $ano = $this->anoDaData($data);
        if ((string) $turma['ano_lectivo'] !== (string) $ano) {
            throw new Exception("A turma " . $turma['nome'] . " é do ano " . $turma['ano_lectivo'] . " e a matrícula é do ano " . $ano . ".");
        }

        $stmtC = $this->db->prepare("select count(*) as total from Matricula where cod_Turma = ? and codigo <> ?");
        $stmtC->bind_param("ii", $codTurma, $ignorarMatricula);
        $stmtC->execute();
        $inscritos = (int) $stmtC->get_result()->fetch_assoc()['total'];
        if ($inscritos >= (int) $turma['capacidade_maxima']) {
            throw new Exception("A turma " . $turma['nome'] . " já atingiu a capacidade máxima (" . $turma['capacidade_maxima'] . ").");
        }
    }

    public function create(Matricula $matricula) {
        $codigoFormando = $matricula->getFormando()->getCodigo();
        $codigoQuali = $matricula->getQualificacao()->getCodigo();
        $idQualiNivel = $matricula->getId_quali_nivel();
        $codTurma = $matricula->getCodTurma();
        $dataMatricula = $matricula->getDataMatricula();

        $this->db->begin_transaction();
        try {
            $this->validarAno($codigoFormando, $dataMatricula);
            $this->validarTurma($codTurma, $codigoQuali, $dataMatricula);

            $sql = "insert into Matricula (cod_formando, cod_Quali, id_Quali_Nivel, cod_Turma, data) values (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("iiiis", $codigoFormando, $codigoQuali, $idQualiNivel, $codTurma, $dataMatricula);
            $result = $stmt->execute();
            if (!$result) {
                $this->db->rollback();
                return false;
            }
            $this->db->commit();
        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            if ($e->getCode() == 1062) {
                throw new Exception("Este formando já tem matrícula no ano " . $this->anoDaData($dataMatricula) . ".");
            }
            return false;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        $usuario = Sessao::obterUtilizador();
        if ($usuario != null) {
            $log = new Logs(null, "INSERT", "Matrícula para a data " . $dataMatricula . " foi cadastrada", $usuario);
            $log->setData(date('d/m/Y H:i:s'));
            (new LogDao())->salvar($log);
        }

        return true;
    }

    public function getAll($nome, $codCampo = null) {
        $sql = "select Matricula.codigo, nome_formando, apelido_formando, titulo, Nivel.nome as nivel, Matricula.data,
                       Quali_Nivel.codigo_Quali_Nivel,
                       Turma.codigo as cod_turma, Turma.nome as turma,
                       (select group_concat(c.nome order by c.nome separator ', ')
                          from Classificacao cl
                          join Campo c on c.codigo = cl.cod_Campo
                         where cl.cod_Qualificacao = Matricula.cod_Quali) as campo,
                       (select min(cl.cod_Campo)
                          from Classificacao cl
                         where cl.cod_Qualificacao = Matricula.cod_Quali) as campo_cod
                from Matricula
                join Formando on codigo_formando = Matricula.cod_formando
                join Qualificacao on Qualificacao.cod_Quali = Matricula.cod_Quali
                left join Quali_Nivel on Quali_Nivel.codigo_Quali_Nivel = Matricula.id_Quali_Nivel
                join Nivel on Nivel.codigo = Quali_Nivel.cod_Nivel
                left join Turma on Turma.codigo = Matricula.cod_Turma
                where nome_formando like ?";
        if ($codCampo !== null && (int) $codCampo > 0) {
            $sql .= " and Matricula.cod_Quali in (select cod_Qualificacao from Classificacao where cod_Campo = ?)";
        }
        $stmt = $this->db->prepare($sql);
        $busca = "%" . $nome . "%";
        if ($codCampo !== null && (int) $codCampo > 0) {
            $stmt->bind_param("si", $busca, $codCampo);
        } else {
            $stmt->bind_param("s", $busca);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $matriculas = [];
        while ($rs = $result->fetch_assoc()) {
            $formando = new Formando(null, $rs['nome_formando'], $rs['apelido_formando'], null, null, null);
            $qualificacao = new Qualificacao(null, $rs['titulo'], null);
            $qualificacao->setCampo($rs['campo']);
            $qualificacao->setCodCampo($rs['campo_cod'] ?? null);
            $nivel = new Nivel(null, $rs['nivel']);
            $idQualiNivel = $rs['codigo_Quali_Nivel'] ?? null;

            $matricula = new Matricula(
                $rs['codigo'], $formando, $qualificacao, $nivel, $idQualiNivel, $rs['data'],
                $rs['cod_turma'] ?? null, $rs['turma'] ?? null
            );
            $matriculas[] = $matricula;
        }
        return $matriculas;
    }

    public function update(Matricula $matricula) {
        $codigoFormando = $matricula->getFormando()->getCodigo();
        $codigoQuali = $matricula->getQualificacao()->getCodigo();
        $idQualiNivel = $matricula->getId_quali_nivel();
        $codTurma = $matricula->getCodTurma();
        $dataMatricula = $matricula->getDataMatricula();
        $codigo = (int) $matricula->getCodigo();

        $this->db->begin_transaction();
        try {
            $this->validarAno($codigoFormando, $dataMatricula, $codigo);
            $this->validarTurma($codTurma, $codigoQuali, $dataMatricula, $codigo);

            $sql = "update Matricula set cod_formando = ?, cod_Quali = ?, id_Quali_Nivel = ?, cod_Turma = ?, data = ? where codigo = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("iiiisi", $codigoFormando, $codigoQuali, $idQualiNivel, $codTurma, $dataMatricula, $codigo);
            $result = $stmt->execute();
            if (!$result) {
                $this->db->rollback();
                return false;
            }
            $this->db->commit();
        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            if ($e->getCode() == 1062) {
                throw new Exception("Este formando já tem matrícula no ano " . $this->anoDaData($dataMatricula) . ".");
            }
            return false;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        $usuario = Sessao::obterUtilizador();
        if ($usuario != null) {
            $log = new Logs(null, "UPDATE", "Matrícula (ID: " . $codigo . ") para a data " . $dataMatricula . " foi atualizada", $usuario);
            $log->setData(date('d/m/Y H:i:s'));
            (new LogDao())->salvar($log);
        }

        return true;
    }

    public function delete($codigo) {
        $sqlBuscar = "select data from Matricula where codigo = ?";
        $stmtBuscar = $this->db->prepare($sqlBuscar);
        $stmtBuscar->bind_param("i", $codigo);
        $stmtBuscar->execute();
        $rs = $stmtBuscar->get_result()->fetch_assoc();
        $data = $rs != null ? $rs['data'] : "";

        $sql = "delete from Matricula where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "DELETE", "Matrícula (ID: " . $codigo . ") da data " . $data . " foi removida", $usuario);
                $log->setData(date('d/m/Y H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function existeMatriculaPorFormando($codigoFormando) {
        $sql = "select count(*) as total from Matricula where cod_formando = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoFormando);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs['total'] > 0;
    }

    public function existeMatriculaPorQualificacao($codigoQualificacao) {
        $sql = "select count(*) as total from Matricula where cod_Quali = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoQualificacao);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs['total'] > 0;
    }

    public function getByFormando($codigoFormando) {
        $sql = "select Matricula.codigo, Matricula.cod_formando, Matricula.cod_Quali, Matricula.id_Quali_Nivel, Matricula.data,
                       Qualificacao.titulo, Nivel.nome as nivel
                from Matricula
                join Qualificacao on Qualificacao.cod_Quali = Matricula.cod_Quali
                left join Quali_Nivel on Quali_Nivel.codigo_Quali_Nivel = Matricula.id_Quali_Nivel
                left join Nivel on Nivel.codigo = Quali_Nivel.cod_Nivel
                where Matricula.cod_formando = ?
                order by Matricula.codigo desc
                limit 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoFormando);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();

        if ($rs === null) {
            return null;
        }

        $formando = new Formando($rs['cod_formando'], null, null, null, null, null);
        $qualificacao = new Qualificacao($rs['cod_Quali'], $rs['titulo'], null);
        $nivel = new Nivel(null, $rs['nivel']);
        $idQualiNivel = $rs['id_Quali_Nivel'] ?? null;

        return new Matricula($rs['codigo'], $formando, $qualificacao, $nivel, $idQualiNivel, $rs['data']);
    }
}
?>
