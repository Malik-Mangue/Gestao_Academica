<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../services/Sessao.php';
require_once __DIR__ . '/../model/Licao.php';
require_once __DIR__ . '/../model/Modulo.php';
require_once __DIR__ . '/../model/Formador.php';
require_once __DIR__ . '/../model/Sala.php';
require_once __DIR__ . '/../model/Turma.php';
require_once __DIR__ . '/../model/Logs.php';
require_once __DIR__ . '/LogDao.php';

class LicaoDao {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function create(Licao $licao) {
        $sql = "insert into Licao (cod_Modulo, cod_Formador, cod_Sala, cod_Turma, data, hora_inicio, hora_fim) values (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        $codModulo = $licao->getModulo()->getCodigo();
        $codFormador = $licao->getFormador()->getCodigo();
        $codSala = $licao->getSala()->getCodigo();
        $codTurma = $licao->getTurma()->getCodigo();
        $data = $licao->getData();
        $horaInicio = $licao->getHoraInicio() . ":00";
        $horaFim = $licao->getHoraFim() . ":00";

        $stmt->bind_param("iiiisss", $codModulo, $codFormador, $codSala, $codTurma, $data, $horaInicio, $horaFim);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "INSERT", "Lição do módulo " . $licao->getModulo()->getNome() . " foi cadastrada", $usuario);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function getAll($texto) {
        $sql = "select l.codigo, l.data, l.hora_inicio, l.hora_fim,
                       m.codigo as cod_modulo, m.nome_modulo,
                       f.codigo as cod_formador, f.nome as nome_formador,
                       s.codigo as cod_sala, s.designacao,
                       t.codigo as cod_turma, t.nome as nome_turma
                from Licao l
                join Modulo m on m.codigo = l.cod_Modulo
                join Formador f on f.codigo = l.cod_Formador
                join Sala s on s.codigo = l.cod_Sala
                join Turma t on t.codigo = l.cod_Turma
                where m.nome_modulo like ? or f.nome like ? or t.nome like ?";
        $stmt = $this->db->prepare($sql);
        $filtro = "%" . $texto . "%";
        $stmt->bind_param("sss", $filtro, $filtro, $filtro);
        $stmt->execute();
        $result = $stmt->get_result();

        $licoes = [];
        while ($rs = $result->fetch_assoc()) {
            $modulo = new Modulo($rs['cod_modulo'], $rs['nome_modulo'], null);
            $formador = new Formador($rs['cod_formador'], $rs['nome_formador'], null, null, null, null, null, null, null, null);
            $sala = new Sala($rs['cod_sala'], $rs['designacao'], null);
            $turma = new Turma($rs['cod_turma'], $rs['nome_turma'], null, null, null, null, null);
            $licao = new Licao($rs['codigo'], $modulo, $formador, $sala, $turma, $rs['data'], substr($rs['hora_inicio'], 0, 5), substr($rs['hora_fim'], 0, 5));
            $licoes[] = $licao;
        }
        return $licoes;
    }

    public function update(Licao $licao) {
        $sql = "update Licao set cod_Modulo = ?, cod_Formador = ?, cod_Sala = ?, cod_Turma = ?,
                       data = ?, hora_inicio = ?, hora_fim = ?
                where codigo = ?";
        $stmt = $this->db->prepare($sql);

        $codModulo   = $licao->getModulo()->getCodigo();
        $codFormador = $licao->getFormador()->getCodigo();
        $codSala     = $licao->getSala()->getCodigo();
        $codTurma    = $licao->getTurma()->getCodigo();
        $data        = $licao->getData();
        $horaInicio  = substr($licao->getHoraInicio(), 0, 5) . ':00';
        $horaFim     = substr($licao->getHoraFim(), 0, 5) . ':00';
        $codigo      = $licao->getCodigo();

        $stmt->bind_param("iiiisssi", $codModulo, $codFormador, $codSala, $codTurma, $data, $horaInicio, $horaFim, $codigo);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "UPDATE", "Lição (ID: " . $codigo . ") foi atualizada", $usuario);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function delete($codigo) {
        $sql = "delete from Licao where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "DELETE", "Lição (ID: " . $codigo . ") foi removida", $usuario);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function existeLicaoPorModulo($codigoModulo) {
        $sql = "select count(*) as total from Licao where cod_Modulo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoModulo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs['total'] > 0;
    }

    public function existeLicaoPorFormador($codigoFormador) {
        $sql = "select count(*) as total from Licao where cod_Formador = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoFormador);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs['total'] > 0;
    }

    public function existeLicaoPorSala($codigoSala) {
        $sql = "select count(*) as total from Licao where cod_Sala = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoSala);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs['total'] > 0;
    }

    public function existeLicaoPorTurma($codigoTurma) {
        $sql = "select count(*) as total from Licao where cod_Turma = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoTurma);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs['total'] > 0;
    }

    // Listas de opcoes para preencher os <select> do formulario de lição.
    public function listarOpcoes($sql) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        $opcoes = [];
        while ($rs = $result->fetch_assoc()) {
            $opcoes[] = ['codigo' => $rs['codigo'], 'descricao' => $rs['descricao']];
        }
        return $opcoes;
    }

    public function getModulos() {
        return $this->listarOpcoes("select codigo, nome_modulo as descricao from Modulo order by nome_modulo");
    }

    public function getFormadores() {
        return $this->listarOpcoes("select codigo, concat(nome, ' ', apelido) as descricao from Formador order by nome");
    }

    public function getSalas() {
        return $this->listarOpcoes("select codigo, designacao as descricao from Sala order by designacao");
    }

    public function getTurmas() {
        return $this->listarOpcoes("select codigo, nome as descricao from Turma order by nome");
    }

    // Impede a sobreposicao de horario para a mesma sala e formador.
    public function existeConflito($codSala, $codFormador, $data, $horaInicio, $horaFim, $codigoIgnorado = null) {
        $sql = "select count(*) as total from Licao
                where cod_Sala = ? and cod_Formador = ? and data = ?
                and hora_inicio < ? and hora_fim > ?";
        $params = [$codSala, $codFormador, $data, $horaFim, $horaInicio];
        $types  = "iiiss";

        if ($codigoIgnorado !== null) {
            $sql .= " and codigo <> ?";
            $params[] = $codigoIgnorado;
            $types   .= "i";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();

        return (int) $rs['total'] > 0;
    }

    public function existeLicaoPorQualificacao($codigoQualificacao) {
        $sql = "select count(*) as total from Licao l
                join Modulo m on m.codigo = l.cod_Modulo
                join Quali_Nivel qn on qn.codigo_Quali_Nivel = m.id_Quali_Nivel
                where qn.cod_Quali = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoQualificacao);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs['total'] > 0;
    }
}
?>
