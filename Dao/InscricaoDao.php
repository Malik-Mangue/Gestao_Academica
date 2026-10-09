<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../model/Inscricao.php';
require_once __DIR__ . '/../model/Formando.php';
require_once __DIR__ . '/../model/Modulo.php';
require_once __DIR__ . '/../model/Quali_Nivel.php';
require_once __DIR__ . '/../model/Qualificacao.php';
require_once __DIR__ . '/../model/Logs.php';
require_once __DIR__ . '/LogDao.php';

class InscricaoDao {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function create(Inscricao $inscricao) {
        $sql = "insert into Inscricao (codigo_formando, codigo_modulo, semestre, data_inscricao) values (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        $codigoFormando = $inscricao->getFormando()->getCodigo();
        $codigoModulo = $inscricao->getModulo()->getCodigo();
        $semestre = $inscricao->getSemestre();
        $dataInscricao = $inscricao->getDataInscricao();

        $stmt->bind_param("iiss", $codigoFormando, $codigoModulo, $semestre, $dataInscricao);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "INSERT", "Inscrição para o semestre " . $semestre . " foi cadastrada", $usuario);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function getAll($semestre) {
        $sql = "select i.codigo_inscricao, i.codigo_formando, i.codigo_modulo, i.semestre, i.data_inscricao,
                       f.nome_formando, f.apelido_formando, m.nome_modulo,
                       qn.codigo_Quali_Nivel, q.cod_Quali, q.titulo
                from Inscricao i
                join Formando f on f.codigo_formando = i.codigo_formando
                join Modulo m on m.codigo = i.codigo_modulo
                left join Quali_Nivel qn on qn.codigo_Quali_Nivel = m.id_Quali_Nivel
                left join Qualificacao q on q.cod_Quali = qn.cod_Quali
                where i.semestre like ?";
        $stmt = $this->db->prepare($sql);
        $busca = "%" . $semestre . "%";
        $stmt->bind_param("s", $busca);
        $stmt->execute();
        $result = $stmt->get_result();

        $inscricoes = [];
        while ($rs = $result->fetch_assoc()) {
            $formando = new Formando($rs['codigo_formando'], $rs['nome_formando'], $rs['apelido_formando'], null, null, null);
            $modulo = new Modulo($rs['codigo_modulo'], $rs['nome_modulo'], null);

            // NOVO: liga a qualificação ao módulo
            if ($rs['cod_Quali'] !== null) {
                $qualificacao = new Qualificacao($rs['cod_Quali'], $rs['titulo'], null);
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
                $log->setData(date('Y-m-d H:i:s'));
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
                $log->setData(date('Y-m-d H:i:s'));
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
}
?>
