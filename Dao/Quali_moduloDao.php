<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/sessao.php';
require_once __DIR__ . '/../model/Quali_modulo.php';
require_once __DIR__ . '/../model/Logs.php';
require_once __DIR__ . '/LogDao.php';

class Quali_moduloDao {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function create(Quali_modulo $quali_modulo) {
        $sql = "insert into Quali_modulo (cod_modulo, cod_Quali, semestre) values (?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        $codModulo = $quali_modulo->getModulo()->getCodigo();
        $codQuali = $quali_modulo->getQualificacao()->getCodigo();
        $semestre = $quali_modulo->getSemestre();

        $stmt->bind_param("iis", $codModulo, $codQuali, $semestre);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "INSERT", "Quali_modulo do semestre " . $semestre . " foi cadastrado", $usuario);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function getAll($semestre) {
        $sql = "select * from Quali_modulo where semestre = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $semestre);
        $stmt->execute();
        $result = $stmt->get_result();

        $lista = [];
        while ($rs = $result->fetch_assoc()) {
            $qualiModulo = new Quali_modulo($rs['codigo'], $rs['semestre'], null, null);
            $lista[] = $qualiModulo;
        }
        return $lista;
    }

    public function update(Quali_modulo $quali_modulo) {
        $sql = "update Quali_modulo set cod_modulo = ?, cod_Quali = ?, semestre = ? where codigo = ?";
        $stmt = $this->db->prepare($sql);

        $codModulo = $quali_modulo->getModulo()->getCodigo();
        $codQuali = $quali_modulo->getQualificacao()->getCodigo();
        $semestre = $quali_modulo->getSemestre();
        $codigo = $quali_modulo->getCodigo();

        $stmt->bind_param("iisi", $codModulo, $codQuali, $semestre, $codigo);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "UPDATE", "Quali_modulo (ID: " . $codigo . ") do semestre " . $semestre . " foi atualizado", $usuario);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function delete($codigo) {
        $sqlBuscar = "select semestre from Quali_modulo where codigo = ?";
        $stmtBuscar = $this->db->prepare($sqlBuscar);
        $stmtBuscar->bind_param("i", $codigo);
        $stmtBuscar->execute();
        $rs = $stmtBuscar->get_result()->fetch_assoc();
        $semestre = $rs != null ? $rs['semestre'] : "";

        $sql = "delete from Quali_modulo where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "DELETE", "Quali_modulo (ID: " . $codigo . ") do semestre " . $semestre . " foi removido", $usuario);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }
}
?>
