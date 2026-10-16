<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../services/Sessao.php';
require_once __DIR__ . '/../model/Formador.php';
require_once __DIR__ . '/../model/Logs.php';
require_once __DIR__ . '/LogDao.php';

class FormadorDao {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function create(Formador $formador) {
        $sql = "insert into Formador (nome, apelido, email, genero, estadoCivil, contacto, valor_hora, horas_mes, salario) values (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        $nome = $formador->getNome();
        $apelido = $formador->getApelido();
        $email = $formador->getEmail();
        $genero = $formador->getGenero();
        $estadoCivil = $formador->getEstadoCivil();
        $contacto = $formador->getContacto();
        $valorHoras = $formador->getValorHoras();
        $horasMes = $formador->getHorasMes();
        $salario = $formador->getSalario();

        $stmt->bind_param("sssssiiid", $nome, $apelido, $email, $genero, $estadoCivil, $contacto, $valorHoras, $horasMes, $salario);
        $result = $stmt->execute();
        if ($result) {

            $formador->setCodigo($this->db->insert_id);

            $this->registarLog("INSERT", "Formador " . $nome . " " . $apelido . " foi cadastrado");
        }
        return $result;

    }

    public function getAll($pesquisa = null) {
        $sql = "select codigo, nome, apelido, email, genero, estadoCivil, contacto,
                       valor_hora, horas_mes, salario
                from Formador";
        if ($pesquisa !== null && strlen($pesquisa) > 0) {
            $sql .= " where nome like ? or apelido like ? or email like ? or genero like ?
                            or estadoCivil like ? or contacto like ?
                      order by nome";
            $stmt = $this->db->prepare($sql);
            $busca = "%" . $pesquisa . "%";
            $stmt->bind_param("ssssss", $busca, $busca, $busca, $busca, $busca, $busca);
        } else {
            $sql .= " order by nome";
            $stmt = $this->db->prepare($sql);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $formadores = [];
        while ($rs = $result->fetch_assoc()) {
            $formadores[] = $this->montarFormador($rs);
        }
        return $formadores;
    }

    public function getById($codigo) {
        $sql = "select codigo, nome, apelido, email, genero, estadoCivil, contacto,
                       valor_hora, horas_mes, salario
                from Formador where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();

        if ($rs == null) {
            return null;
        }
        return $this->montarFormador($rs);
    }

    public function update(Formador $formador) {
        $sql = "update Formador set nome = ?, apelido = ?, email = ?, genero = ?, estadoCivil = ?, contacto = ?, valor_hora = ?, horas_mes = ?, salario = ? where codigo = ?";
        $stmt = $this->db->prepare($sql);

        $nome = $formador->getNome();
        $apelido = $formador->getApelido();
        $email = $formador->getEmail();
        $genero = $formador->getGenero();
        $estadoCivil = $formador->getEstadoCivil();
        $contacto = $formador->getContacto();
        $valorHoras = $formador->getValorHoras();
        $horasMes = $formador->getHorasMes();
        $salario = $formador->getSalario();
        $codigo = $formador->getCodigo();

        $stmt->bind_param("sssssiiidi", $nome, $apelido, $email, $genero, $estadoCivil, $contacto, $valorHoras, $horasMes, $salario, $codigo);
        $result = $stmt->execute();
        if ($result) {
            $this->registarLog("UPDATE", "Formador " . $nome . " " . $apelido . " (ID: " . $codigo . ") foi atualizado");
        }
        return $result;
    }

    public function delete($codigo) {
        $sql = "delete from Formador where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $result = $stmt->execute();
        if ($result) {
            $this->registarLog("DELETE", "Formador (ID: " . $codigo . ") foi removido");
        }
        return $result;
    }

private function consultarLista($sql, $codigoFormador) {
    try {
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoFormador);
        $stmt->execute();
        $result = $stmt->get_result();

        $lista = [];
        while ($rs = $result->fetch_assoc()) {
            $lista[] = $rs;
        }
        return $lista;
    } catch (mysqli_sql_exception $e) {
        return [];
    }
}

public function listarTurmasQueDirige($codigoFormador) {
    return $this->consultarLista(
        "select t.nome as turma,
                concat(q.titulo, ', ', n.nome) as qualificacao,
                t.ano_lectivo as ano_letivo,
                t.turno as turno
         from Turma t
         join Quali_Nivel qn on qn.codigo_Quali_Nivel = t.id_Quali_Nivel
         join Qualificacao q on q.cod_Quali = qn.cod_Quali
         join Nivel n on n.codigo = qn.cod_Nivel
         where t.id_Diretor_Turma = ?
         order by t.ano_lectivo desc, t.nome",
        $codigoFormador
    );
}

public function listarQualificacoesQueCoordena($codigoFormador) {
    return $this->consultarLista(
        "select q.titulo as qualificacao,
                (select group_concat(c.nome order by c.nome separator ', ')
                 from Classificacao cl
                 join Campo c on c.codigo = cl.cod_Campo
                 where cl.cod_Qualificacao = q.cod_Quali) as campo,
                (select group_concat(n.nome order by n.nome separator ', ')
                 from Quali_Nivel qn
                 join Nivel n on n.codigo = qn.cod_Nivel
                 where qn.cod_Quali = q.cod_Quali) as niveis
         from Qualificacao q
         where q.cod_Coordenador = ?
         order by q.titulo",
        $codigoFormador
    );
}

    private function registarLog($acao, $descricao) {
        $usuario = Sessao::obterUtilizador();
        if ($usuario != null) {
            $log = new Logs(null, $acao, $descricao, $usuario);
            $log->setData(date('d/m/Y H:i:s'));
            (new LogDao())->salvar($log);
        }
    }

    private function montarFormador($rs) {
        return new Formador(
            $rs['codigo'],
            $rs['nome'],
            $rs['apelido'],
            $rs['email'],
            $rs['genero'],
            $rs['estadoCivil'],
            $rs['contacto'],
            $rs['valor_hora'],
            $rs['horas_mes'],
            $rs['salario']
        );
    }
}
?>
