<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../services/Sessao.php';
require_once __DIR__ . '/../model/Classificacao.php';
require_once __DIR__ . '/../model/Campo.php';
require_once __DIR__ . '/../model/Logs.php';
require_once __DIR__ . '/LogDao.php';

class ClassificacaoDao {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function create(Classificacao $classificacao) {
        $sql = "insert into Classificacao (cod_Campo, cod_Qualificacao) values (?, ?)";
        $stmt = $this->db->prepare($sql);

        $codCampo = $classificacao->getCampo()->getCodigo();
        $codQualificacao = $classificacao->getQualificacao()->getCodigo();

        $stmt->bind_param("ii", $codCampo, $codQualificacao);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "INSERT", "Classificação " . $classificacao->getCodigo() . " foi cadastrada", $usuario);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function getAll($campo) {
        $sql = "select * from Classificacao where campo = ?";
        $stmt = $this->db->prepare($sql);

        $codigoCampo = $campo->getCodigo();
        $stmt->bind_param("i", $codigoCampo);
        $stmt->execute();
        $result = $stmt->get_result();

        $lista = [];
        while ($rs = $result->fetch_assoc()) {
            $campoEncontrado = new Campo($rs['campo'], null);
            $c = new Classificacao($rs['codigo'], $campoEncontrado, null);
            $lista[] = $c;
        }
        return $lista;
    }

    public function update(Classificacao $classificacao) {
        $sql = "update Classificacao set campo = ? where codigo = ?";
        $stmt = $this->db->prepare($sql);

        $codigoCampo = $classificacao->getCampo()->getCodigo();
        $codigo = $classificacao->getCodigo();

        $stmt->bind_param("ii", $codigoCampo, $codigo);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "UPDATE", "Classificação " . $classificacao->getCodigo() . " foi atualizada", $usuario);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function delete($codigo) {
        $sql = "delete from Classificacao where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "DELETE", "Classificação " . $codigo . " foi removida", $usuario);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }
}
?>
