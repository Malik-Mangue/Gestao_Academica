<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../services/Sessao.php';
require_once __DIR__ . '/../model/Formador.php';
require_once __DIR__ . '/../model/Campo.php';
require_once __DIR__ . '/../model/Logs.php';
require_once __DIR__ . '/LogDao.php';

class FormadorDao {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function create(Formador $formador) {
        $sql = "insert into Formador (nome, apelido, email, genero, estadoCivil, contacto, valor_hora, horas_mes, salario, codCampo) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
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
        $codCampo = $formador->getCodCampo();

        $stmt->bind_param("sssssiiidi", $nome, $apelido, $email, $genero, $estadoCivil, $contacto, $valorHoras, $horasMes, $salario, $codCampo);
        $result = $stmt->execute();
        if ($result) {

            $formador->setCodigo($this->db->insert_id);

            $this->registarLog("INSERT", "Formador " . $nome . " " . $apelido . " foi cadastrado");
        }
        return $result;

    }

    public function getAll($pesquisa = null, $codCampo = null) {
        $sql = "select f.codigo, f.nome, f.apelido, f.email, f.genero, f.estadoCivil, f.contacto,
                       f.valor_hora, f.horas_mes, f.salario, f.codCampo, c.nome as campo_nome
                from Formador f
                left join Campo c on c.codigo = f.codCampo";
        $condicoes = [];
        $tipos = "";
        $parametros = [];
        if ($pesquisa !== null && strlen($pesquisa) > 0) {
            $condicoes[] = "(f.nome like ? or f.apelido like ? or f.email like ? or f.genero like ?
                             or f.estadoCivil like ? or f.contacto like ?)";
            $busca = "%" . $pesquisa . "%";
            $parametros = array_merge($parametros, [$busca, $busca, $busca, $busca, $busca, $busca]);
            $tipos .= "ssssss";
        }
        if ($codCampo !== null && (int) $codCampo > 0) {
            $condicoes[] = "f.codCampo = ?";
            $parametros[] = (int) $codCampo;
            $tipos .= "i";
        }
        if (!empty($condicoes)) {
            $sql .= " where " . implode(" and ", $condicoes);
        }
        $sql .= " order by f.nome";
        $stmt = $this->db->prepare($sql);
        if (!empty($parametros)) {
            $stmt->bind_param($tipos, ...$parametros);
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
        $sql = "select f.codigo, f.nome, f.apelido, f.email, f.genero, f.estadoCivil, f.contacto,
                       f.valor_hora, f.horas_mes, f.salario, f.codCampo, c.nome as campo_nome
                from Formador f
                left join Campo c on c.codigo = f.codCampo
                where f.codigo = ?";
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
        $sql = "update Formador set nome = ?, apelido = ?, email = ?, genero = ?, estadoCivil = ?, contacto = ?, valor_hora = ?, horas_mes = ?, salario = ?, codCampo = ? where codigo = ?";
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
        $codCampo = $formador->getCodCampo();
        $codigo = $formador->getCodigo();

        $stmt->bind_param("sssssiiidii", $nome, $apelido, $email, $genero, $estadoCivil, $contacto, $valorHoras, $horasMes, $salario, $codCampo, $codigo);
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
        $formador = new Formador(
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
        if (isset($rs['codCampo'])) {
            $formador->setCodCampo($rs['codCampo']);
            if (isset($rs['campo_nome'])) {
                $formador->setCampo(new Campo($rs['codCampo'], $rs['campo_nome']));
            }
        }
        return $formador;
    }

    /**
     * Lista formadores (para dropdowns), opcionalmente filtrados por campo e/ou
     * por função (coordenador / diretor de turma).
     */
    public function getFormadoresPorCampo($codCampo = null, $funcao = null) {
        $sql = "select distinct f.codigo as codigo,
                       concat(f.nome, ' ', f.apelido) as descricao
                from Formador f";
        if ($funcao === 'coordenador') {
            $sql .= " join Coordenador co on co.cod_Formador = f.codigo";
        } elseif ($funcao === 'diretor') {
            $sql .= " join Diretor_Turma dt on dt.cod_Formador = f.codigo";
        }
        if ($codCampo !== null && (int) $codCampo > 0) {
            $sql .= " where f.codCampo = ?";
        }
        $sql .= " order by descricao";

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
}
?>
