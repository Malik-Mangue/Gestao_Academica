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

    public function create(Matricula $matricula) {
        $sql = "insert into Matricula (cod_formando, cod_Quali, id_Quali_Nivel, data) values (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        $codigoFormando = $matricula->getFormando()->getCodigo();
        
        $codigoQuali = $matricula->getQualificacao()->getCodigo();
        $idQualiNivel = $matricula->getId_quali_nivel();
        $dataMatricula = $matricula->getDataMatricula();

        $stmt->bind_param("iiis", $codigoFormando, $codigoQuali, $idQualiNivel, $dataMatricula);
        try {
            $result = $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "INSERT", "Matrícula para a data " . $dataMatricula . " foi cadastrada", $usuario);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function getAll($nome) {
        $sql = "select Matricula.codigo, nome_formando, apelido_formando, titulo, Nivel.nome as nivel, data,
                       Quali_Nivel.codigo_Quali_Nivel
                from Matricula
                join Formando on codigo_formando = Matricula.cod_formando
                join Qualificacao on Qualificacao.cod_Quali = Matricula.cod_Quali
                left join Quali_Nivel on Quali_Nivel.codigo_Quali_Nivel = Matricula.id_Quali_Nivel
                join Nivel on Nivel.codigo = Quali_Nivel.cod_Nivel
                where nome_formando like ?";
        $stmt = $this->db->prepare($sql);
        $busca = "%" . $nome . "%";
        $stmt->bind_param("s", $busca);
        $stmt->execute();
        $result = $stmt->get_result();

        $matriculas = [];
        while ($rs = $result->fetch_assoc()) {
            $formando = new Formando(null, $rs['nome_formando'], $rs['apelido_formando'], null, null, null);
            $qualificacao = new Qualificacao(null, $rs['titulo'], null);
            $nivel = new Nivel(null, $rs['nivel']);
            $idQualiNivel = $rs['codigo_Quali_Nivel'] ?? null;

            $matricula = new Matricula($rs['codigo'], $formando, $qualificacao, $nivel, $idQualiNivel, $rs['data']);
            $matriculas[] = $matricula;
        }
        return $matriculas;
    }

    public function update(Matricula $matricula) {
        $sql = "update Matricula set cod_formando = ?, cod_Quali = ?, id_Quali_Nivel = ?, data = ? where codigo = ?";
        $stmt = $this->db->prepare($sql);

        $codigoFormando = $matricula->getFormando()->getCodigo();
        $codigoQuali = $matricula->getQualificacao()->getCodigo();
        $idQualiNivel = $matricula->getId_quali_nivel();
        $dataMatricula = $matricula->getDataMatricula();
        $codigo = $matricula->getCodigo();

        $stmt->bind_param("iiisi", $codigoFormando, $codigoQuali, $idQualiNivel, $dataMatricula, $codigo);
        try {
            $result = $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "UPDATE", "Matrícula (ID: " . $codigo . ") para a data " . $dataMatricula . " foi atualizada", $usuario);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
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
                $log->setData(date('Y-m-d H:i:s'));
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
}
?>
