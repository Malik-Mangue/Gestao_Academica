<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../services/Sessao.php';
require_once __DIR__ . '/../model/Turma.php';
require_once __DIR__ . '/../model/Diretor_turma.php';
require_once __DIR__ . '/../model/Formador.php';
require_once __DIR__ . '/../model/Qualificacao.php';
require_once __DIR__ . '/../model/Nivel.php';
require_once __DIR__ . '/../model/Logs.php';
require_once __DIR__ . '/LogDao.php';

class TurmaDao {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function create(Turma $turma) {
        $sql = "insert into Turma (nome, ano_lectivo, turno, id_Diretor_Turma, id_Quali_Nivel) values (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        $nome = $turma->getNome();
        $anoIngresso = $turma->getAnoIngresso();
        $turno = $turma->getTurno();
        $codigoFormador = $turma->getDiretorTurma()->getFormador()->getCodigo();
        $codigoQualiNivel = $turma->getQualiNivel()->getCodigo();

        $stmt->bind_param("sissi", $nome, $anoIngresso, $turno, $codigoFormador, $codigoQualiNivel);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "INSERT", "Turma " . $nome . " foi cadastrada", $usuario);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function update(Turma $turma) {
        $sql = "update Turma set nome = ?, ano_lectivo = ?, turno = ?, id_Diretor_Turma = ?, id_Quali_Nivel = ? where codigo = ?";
        $stmt = $this->db->prepare($sql);

        $nome = $turma->getNome();
        $anoIngresso = $turma->getAnoIngresso();
        $turno = $turma->getTurno();
        $codigoFormador = $turma->getDiretorTurma()->getFormador()->getCodigo();
        $codigoQualiNivel = $turma->getQualiNivel()->getCodigo();
        $codigo = $turma->getCodigo();

        $stmt->bind_param("sissii", $nome, $anoIngresso, $turno, $codigoFormador, $codigoQualiNivel, $codigo);
        return $stmt->execute();
    }

    public function getAll($nome) {
        $sql = "select Turma.codigo, Turma.nome, ano_lectivo, turno, Formador.nome as diretor_turma, Qualificacao.titulo as titulo, Nivel.nome as nivel from Turma
                join Diretor_Turma on cod_Formador = id_Diretor_Turma
                join Formador on Formador.codigo = cod_Formador
                join Quali_Nivel on id_Quali_Nivel = codigo_Quali_Nivel
                join Nivel on cod_Nivel = Nivel.codigo
                join Qualificacao on Qualificacao.cod_Quali = Quali_Nivel.cod_Quali
                where Turma.nome like ?";
        $stmt = $this->db->prepare($sql);
        $busca = "%" . $nome . "%";
        $stmt->bind_param("s", $busca);
        $stmt->execute();
        $result = $stmt->get_result();

        $turmas = [];
        while ($rs = $result->fetch_assoc()) {
            $formador = new Formador(null, $rs['diretor_turma'], null, null, null, null, null, null, null, null);
            $diretorTurma = new Diretor_turma( $formador);

            $qualificacao = new Qualificacao(null, $rs['titulo'], null);
            $nivel = new Nivel(null, $rs['nivel']);

            $turma = new Turma($rs['codigo'], $rs['nome'], $rs['ano_lectivo'], $rs['turno'], $diretorTurma, $qualificacao);

            $turmas[] = $turma;
        }
        return $turmas;
    }

    public function delete($codigo) {
        $sql = "delete from Turma where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        return $stmt->execute();
    }

    public function existeTurmaComDiretor($codigoFormador) {
        $sql = "select count(*) as total from Turma where id_Diretor_Turma = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoFormador);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs['total'] > 0;
    }
}
?>
