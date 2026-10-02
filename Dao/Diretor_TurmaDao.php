<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/Sessao.php';
require_once __DIR__ . '/../model/Diretor_turma.php';
require_once __DIR__ . '/../model/Formador.php';
require_once __DIR__ . '/../model/Logs.php';
require_once __DIR__ . '/LogDao.php';

class Diretor_TurmaDao {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function create(Diretor_turma $dt) {
        $sql = "insert into Diretor_Turma values (?)";
        $stmt = $this->db->prepare($sql);

        $codigoFormador = $dt->getFormador()->getCodigo();
        $stmt->bind_param("i", $codigoFormador);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "INSERT", "Diretor de Turma " . $dt->getFormador()->getNome() . " foi cadastrado", $usuario);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function getAll() {
        $sql = "select cod_Formador, nome from Diretor_Turma join Formador on cod_Formador = codigo";
        $result = mysqli_query($this->db, $sql);

        $diretores = [];
        while ($rs = $result->fetch_assoc()) {
            $formador = new Formador($rs['cod_Formador'], $rs['nome'], null, null, null, null, null, null, null, null);
            $dt = new Diretor_turma(null, $formador);
            $diretores[] = $dt;
        }
        return $diretores;
    }

    public function update(Diretor_turma $diretor, $codigo) {
        $sql = "update Diretor_Turma set cod_Formador = ? where cod_Formador = " . intval($codigo);
        $stmt = $this->db->prepare($sql);

        $codigoFormador = $diretor->getFormador()->getCodigo();
        $stmt->bind_param("i", $codigoFormador);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "UPDATE", "Diretor de Turma " . $diretor->getFormador()->getNome() . " foi atualizado", $usuario);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function isDiretor($codigoFormador) {
        $sql = "select * from Diretor_Turma where cod_Formador = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoFormador);
        $stmt->execute();
        $rs = $stmt->get_result();
        return $rs->num_rows > 0;
    }

    public function apagarDiretor($codigoFormador) {
        $sql = "delete from Diretor_Turma where cod_Formador = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoFormador);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "DELETE", "Diretor de Turma (ID Formador: " . $codigoFormador . ") foi removido", $usuario);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }
}
?>
