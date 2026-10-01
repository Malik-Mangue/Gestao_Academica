<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../model/Turma.php';

class TurmaDAO {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAll() {
        $sql = "select codigo, nome, ano_lectivo, turno, id_Diretor_Turma, id_Quali_Nivel from Turma order by codigo";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $turmas = [];
        while ($rs = $result->fetch_assoc()) {
            $turmas[] = new Turma($rs['codigo'], $rs['nome'], $rs['ano_lectivo'], $rs['turno'], $rs['id_Diretor_Turma'], $rs['id_Quali_Nivel']);
        }
        return $turmas;
    }

    public function getById($codigo) {
        $sql = "select codigo, nome, ano_lectivo, turno, id_Diretor_Turma, id_Quali_Nivel from Turma where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        if ($rs === null) {
            return null;
        }
        return new Turma($rs['codigo'], $rs['nome'], $rs['ano_lectivo'], $rs['turno'], $rs['id_Diretor_Turma'], $rs['id_Quali_Nivel']);
    }

    public function create(Turma $turma) {
        $sql = "insert into Turma (nome, ano_lectivo, turno, id_Diretor_Turma, id_Quali_Nivel) values (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $nome = $turma->getNome();
        $ano_lectivo = $turma->getAno_lectivo();
        $turno = $turma->getTurno();
        $id_diretor_turma = $turma->getId_diretor_turma();
        $id_quali_nivel = $turma->getId_quali_nivel();
        $stmt->bind_param("sisii", $nome, $ano_lectivo, $turno, $id_diretor_turma, $id_quali_nivel);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function update(Turma $turma) {
        $sql = "update Turma set nome = ?, ano_lectivo = ?, turno = ?, id_Diretor_Turma = ?, id_Quali_Nivel = ? where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $nome = $turma->getNome();
        $ano_lectivo = $turma->getAno_lectivo();
        $turno = $turma->getTurno();
        $id_diretor_turma = $turma->getId_diretor_turma();
        $id_quali_nivel = $turma->getId_quali_nivel();
        $codigo = $turma->getCodigo();
        $stmt->bind_param("sisiii", $nome, $ano_lectivo, $turno, $id_diretor_turma, $id_quali_nivel, $codigo);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function delete($codigo) {
        $sql = "delete from Turma where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    private function listaOpcoes($sql) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $opcoes = [];
        while ($rs = $result->fetch_assoc()) {
            $opcoes[] = ['codigo' => $rs['codigo'], 'descricao' => $rs['descricao']];
        }
        return $opcoes;
    }

    public function getDiretores() {
        return $this->listaOpcoes("select d.cod_Formador as codigo, concat(f.nome, ' ', f.apelido) as descricao from Diretor_Turma d inner join Formador f on f.codigo = d.cod_Formador order by f.nome");
    }

    public function getQualiNiveis() {
        return $this->listaOpcoes("select qn.codigo_Quali_Nivel as codigo, concat(q.titulo, ' - ', n.nome) as descricao from Quali_Nivel qn inner join Qualificacao q on q.cod_Quali = qn.cod_Quali inner join Nivel n on n.codigo = qn.cod_Nivel order by q.titulo, n.nome");
    }
}
?>
