<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/sessao.php';
require_once __DIR__ . '/../model/Coordenador.php';
require_once __DIR__ . '/../model/Formador.php';
require_once __DIR__ . '/../model/Logs.php';
require_once __DIR__ . '/LogDao.php';

class CoordenadorDao {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function create(Coordenador $coordenador) {
        $sql = "insert into Coordenador values (?)";
        $stmt = $this->db->prepare($sql);

        $codigoFormador = $coordenador->getFormador()->getCodigo();
        $stmt->bind_param("i", $codigoFormador);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "INSERT", "Coordenador " . $coordenador->getFormador()->getNome() . " foi cadastrado", $usuario);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function getAll() {
        $sql = "select cod_Formador, Formador.nome from Coordenador
                join Formador on cod_Formador = codigo";
        $result = mysqli_query($this->db, $sql);

        $lista = [];
        while ($rs = $result->fetch_assoc()) {
            $formador = new Formador($rs['cod_Formador'], $rs['nome'], null, null, null, null, null, null, null, null);
            $coordenador = new Coordenador(null, $formador);
            $lista[] = $coordenador;
        }
        return $lista;
    }

    public function update(Coordenador $coordenador, $codigoFormador) {
        $sql = "update Coordenador set cod_formador = ? where codigo = ?";
        $stmt = $this->db->prepare($sql);

        $codigoNovoFormador = $coordenador->getFormador()->getCodigo();
        $stmt->bind_param("ii", $codigoNovoFormador, $codigoFormador);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "UPDATE", "Coordenador " . $coordenador->getFormador()->getNome() . " foi atualizado", $usuario);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function isCoordenador($codigoFormador) {
        $sql = "select * from Coordenador where cod_Formador = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoFormador);
        $stmt->execute();
        $rs = $stmt->get_result();
        return $rs->num_rows > 0;
    }

    public function delete($codigo) {
        $sql = "delete from Coordenador where cod_Formador = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "DELETE", "Coordenador (ID Formador: " . $codigo . ") foi removido", $usuario);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function existeCoordenadorEmQualificacao($codigoFormador) {
        $sql = "select count(*) as total from Qualificacao where cod_Coordenador = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoFormador);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs['total'] > 0;
    }
}
?>
