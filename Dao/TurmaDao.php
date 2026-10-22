<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../services/Sessao.php';
require_once __DIR__ . '/../model/Turma.php';
require_once __DIR__ . '/../model/Diretor_turma.php';
require_once __DIR__ . '/../model/Formador.php';
require_once __DIR__ . '/../model/Qualificacao.php';
require_once __DIR__ . '/../model/Nivel.php';
require_once __DIR__ . '/../model/Quali_Nivel.php';
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

        $stmt->bind_param("sisii", $nome, $anoIngresso, $turno, $codigoFormador, $codigoQualiNivel);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "INSERT", "Turma " . $nome . " foi cadastrada", $usuario);
                $log->setData(date('d/m/Y H:i:s'));
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

        $stmt->bind_param("sisiii", $nome, $anoIngresso, $turno, $codigoFormador, $codigoQualiNivel, $codigo);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "UPDATE", "Turma " . $nome . " (ID: " . $codigo . ") foi atualizada", $usuario);
                $log->setData(date('d/m/Y H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function getAll($nome, $codCampo = null) {
        $sql = "select Turma.codigo, Turma.nome, ano_lectivo, turno, Turma.id_Diretor_Turma, Turma.id_Quali_Nivel,
                       Quali_Nivel.cod_Quali as cod_quali, Quali_Nivel.cod_Nivel as cod_nivel,
                       Formador.nome as diretor_turma, Qualificacao.titulo as titulo, Nivel.nome as nivel,
                       (select group_concat(c.nome order by c.nome separator ', ')
                          from Classificacao cl
                          join Campo c on c.codigo = cl.cod_Campo
                         where cl.cod_Qualificacao = Quali_Nivel.cod_Quali) as campo,
                       (select min(cl.cod_Campo)
                          from Classificacao cl
                         where cl.cod_Qualificacao = Quali_Nivel.cod_Quali) as campo_cod
                from Turma
                join Diretor_Turma on cod_Formador = id_Diretor_Turma
                join Formador on Formador.codigo = cod_Formador
                join Quali_Nivel on id_Quali_Nivel = codigo_Quali_Nivel
                join Nivel on cod_Nivel = Nivel.codigo
                join Qualificacao on Qualificacao.cod_Quali = Quali_Nivel.cod_Quali
                where Turma.nome like ?";
        if ($codCampo !== null && (int) $codCampo > 0) {
            $sql .= " and Quali_Nivel.cod_Quali in (select cod_Qualificacao from Classificacao where cod_Campo = ?)";
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

        $turmas = [];
        while ($rs = $result->fetch_assoc()) {
            $formador = new Formador($rs['id_Diretor_Turma'], $rs['diretor_turma'], null, null, null, null, null, null, null, null);
            $diretorTurma = new Diretor_turma($formador);

            $qualificacao = new Qualificacao($rs['cod_quali'], $rs['titulo'], null);
            $qualificacao->setCampo($rs['campo']);
            $qualificacao->setCodCampo($rs['campo_cod'] ?? null);
            $nivel = new Nivel($rs['cod_nivel'], $rs['nivel']);

            $qualiNivel = new Quali_Nivel($rs['id_Quali_Nivel'], $rs['cod_quali'], $rs['cod_nivel']);
            $qualiNivel->setQualificacao($qualificacao);
            $qualiNivel->setNivel($nivel);

            $turmas[] = new Turma($rs['codigo'], $rs['nome'], $rs['ano_lectivo'], $rs['turno'], $diretorTurma, $qualificacao, $qualiNivel);
        }
        return $turmas;
    }


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

    public function getDiretores($codCampo = null) {
        $sql = "select f.codigo as codigo, concat(f.nome, ' ', f.apelido) as descricao
                from Diretor_Turma dt
                join Formador f on f.codigo = dt.cod_Formador";
        if ($codCampo !== null && (int) $codCampo > 0) {
            $sql .= " where f.codCampo = ?";
        }
        $sql .= " order by f.nome";

        $stmt = $this->db->prepare($sql);
        if ($codCampo !== null && (int) $codCampo > 0) {
            $stmt->bind_param("i", $codCampo);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $opcoes = [];
        while ($rs = $result->fetch_assoc()) {
            $opcoes[] = ['codigo' => $rs['codigo'], 'descricao' => $rs['descricao']];
        }
        return $opcoes;
    }

    public function getQualificacoes() {
        return $this->listarOpcoes("select cod_Quali as codigo, titulo as descricao from Qualificacao order by titulo");
    }

    public function getNiveis() {
        return $this->listarOpcoes("select codigo, nome as descricao from Nivel order by nome");
    }


    public function getParesQualiNivel($codCampo = null) {
        $sql = "select qn.codigo_Quali_Nivel as codigo,
                       concat(q.titulo, ' — ', n.nome) as descricao
                from Quali_Nivel qn
                join Qualificacao q on q.cod_Quali = qn.cod_Quali
                join Nivel n on n.codigo = qn.cod_Nivel";
        if ($codCampo !== null && (int) $codCampo > 0) {
            $sql .= " where q.cod_Quali in (select cod_Qualificacao from Classificacao where cod_Campo = ?)";
        }
        $sql .= " order by q.titulo, n.nome";

        $stmt = $this->db->prepare($sql);
        if ($codCampo !== null && (int) $codCampo > 0) {
            $stmt->bind_param("i", $codCampo);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $opcoes = [];
        while ($rs = $result->fetch_assoc()) {
            $opcoes[] = ['codigo' => $rs['codigo'], 'descricao' => $rs['descricao']];
        }
        return $opcoes;
    }

    public function getById($codigo) {
        $sql = "select Turma.codigo, Turma.nome, ano_lectivo, turno,
                       Turma.id_Diretor_Turma, Turma.id_Quali_Nivel,
                       Quali_Nivel.cod_Quali as cod_quali, Quali_Nivel.cod_Nivel as cod_nivel,
                       Formador.nome as diretor_turma, Qualificacao.titulo as titulo, Nivel.nome as nivel
                from Turma
                join Diretor_Turma on cod_Formador = id_Diretor_Turma
                join Formador on Formador.codigo = cod_Formador
                join Quali_Nivel on id_Quali_Nivel = codigo_Quali_Nivel
                join Nivel on cod_Nivel = Nivel.codigo
                join Qualificacao on Qualificacao.cod_Quali = Quali_Nivel.cod_Quali
                where Turma.codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();

        if ($rs === null) {
            return null;
        }

        $formador = new Formador($rs['id_Diretor_Turma'], $rs['diretor_turma'], null, null, null, null, null, null, null, null);
        $qualificacao = new Qualificacao($rs['cod_quali'], $rs['titulo'], null);
        $nivel = new Nivel($rs['cod_nivel'], $rs['nivel']);

        $qualiNivel = new Quali_Nivel($rs['id_Quali_Nivel'], $rs['cod_quali'], $rs['cod_nivel']);
        $qualiNivel->setQualificacao($qualificacao);
        $qualiNivel->setNivel($nivel);

        return new Turma(
            $rs['codigo'],
            $rs['nome'],
            $rs['ano_lectivo'],
            $rs['turno'],
            new Diretor_turma($formador),
            $qualificacao,
            $qualiNivel
        );
    }

    public function delete($codigo) {
        $sql = "delete from Turma where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "DELETE", "Turma (ID: " . $codigo . ") foi removida", $usuario);
                $log->setData(date('d/m/Y H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function existeTurmaComDiretor($codigoFormador) {
        $sql = "select count(*) as total from Turma where id_Diretor_Turma = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoFormador);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs['total'] > 0;
    }

    public function getByQualiNivel($codigoQualiNivel) {
        $sql = "select Turma.codigo, Turma.nome, ano_lectivo, turno,
                       Turma.id_Diretor_Turma, Turma.id_Quali_Nivel,
                       Quali_Nivel.cod_Quali as cod_quali, Quali_Nivel.cod_Nivel as cod_nivel,
                       Formador.nome as diretor_turma, Qualificacao.titulo as titulo, Nivel.nome as nivel
                from Turma
                join Diretor_Turma on cod_Formador = id_Diretor_Turma
                join Formador on Formador.codigo = cod_Formador
                join Quali_Nivel on id_Quali_Nivel = codigo_Quali_Nivel
                join Nivel on cod_Nivel = Nivel.codigo
                join Qualificacao on Qualificacao.cod_Quali = Quali_Nivel.cod_Quali
                where Turma.id_Quali_Nivel = ?
                order by Turma.nome";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoQualiNivel);
        $stmt->execute();
        $result = $stmt->get_result();

        $turmas = [];
        while ($rs = $result->fetch_assoc()) {
            $formador = new Formador($rs['id_Diretor_Turma'], $rs['diretor_turma'], null, null, null, null, null, null, null, null);
            $diretorTurma = new Diretor_turma($formador);

            $qualificacao = new Qualificacao($rs['cod_quali'], $rs['titulo'], null);
            $nivel = new Nivel($rs['cod_nivel'], $rs['nivel']);

            $qualiNivel = new Quali_Nivel($rs['id_Quali_Nivel'], $rs['cod_quali'], $rs['cod_nivel']);
            $qualiNivel->setQualificacao($qualificacao);
            $qualiNivel->setNivel($nivel);

            $turmas[] = new Turma($rs['codigo'], $rs['nome'], $rs['ano_lectivo'], $rs['turno'], $diretorTurma, $qualificacao, $qualiNivel);
        }
        return $turmas;
    }
}
?>
