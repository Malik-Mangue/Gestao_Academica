<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../model/Logs.php';
require_once __DIR__ . '/../model/Usuario.php';
require_once __DIR__ . '/../model/Perfil.php';

class LogDao {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function salvar(Logs $log) {
        $sql = "insert into Log (id_Usuario, acao, descricao, data) values (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        $idUsuario = $log->getUsuario()->getCodigo();
        $acao = $log->getAcao();
        $descricao = $log->getDescricao();
        $data = $log->getData();

        $stmt->bind_param("isss", $idUsuario, $acao, $descricao, $data);
        return $stmt->execute();
    }

    private function montarLogs($rs) {
        $usuario = new Usuario(null, null, null, $rs['username'], null, null, 0);
        $usuario->setPerfil(new Perfil(null, $rs['nome']));

        $log = new Logs(null, $rs['acao'], $rs['descricao'], $usuario);
        $log->setData($rs['data']);

        return $log;
    }

    public function listarLogs() {
        $sql = "select Usuario.username, Perfil.nome, acao, descricao, data from Log
                join Usuario on id_Usuario = idUser
                join Perfil on idPerfil = id
                order by codigo desc";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        $logs = [];
        while ($rs = $result->fetch_assoc()) {
            $logs[] = $this->montarLogs($rs);
        }
        return $logs;
    }

    public function listarLogsComFiltro($filtro) {
        $sql = "select Usuario.username, Perfil.nome, acao, descricao, data from Log
                join Usuario on id_Usuario = idUser
                join Perfil on idPerfil = id
                where Usuario.username like ?
                or Perfil.nome like ?
                or acao like ?
                or descricao like ?
                or data like ?
                order by codigo desc";
        $stmt = $this->db->prepare($sql);
        $termo = "%" . $filtro . "%";
        $stmt->bind_param("sssss", $termo, $termo, $termo, $termo, $termo);
        $stmt->execute();
        $result = $stmt->get_result();

        $logs = [];
        while ($rs = $result->fetch_assoc()) {
            $logs[] = $this->montarLogs($rs);
        }
        return $logs;
    }
}
?>
