<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../services/Sessao.php';
require_once __DIR__ . '/../model/Modulo.php';
require_once __DIR__ . '/../model/Qualificacao.php';
require_once __DIR__ . '/../model/Nivel.php';
require_once __DIR__ . '/../model/Quali_Nivel.php';
require_once __DIR__ . '/../model/Quali_modulo.php';
require_once __DIR__ . '/../model/Logs.php';
require_once __DIR__ . '/LogDao.php';

class ModuloDao {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function create(Modulo $modulo) {
        $sql = "insert into Modulo (nome_modulo, carga_horaria, categoria, id_Quali_Nivel) values (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        $nome = $modulo->getNome();
        $cargaHoraria = $modulo->getCargaHoraria();
        $categoria = $modulo->getCategoria();
        $codigoQualiNivel = $modulo->getQualiNivel()->getCodigo();

        $stmt->bind_param("sisi", $nome, $cargaHoraria, $categoria, $codigoQualiNivel);
        try {
            $result = $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }

        if ($result) {
            $modulo->setCodigo($this->db->insert_id);

            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "INSERT", "Módulo " . $nome . " foi cadastrado", $usuario);
                $log->setData(date('d/m/Y H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function update(Modulo $modulo) {
        $sql = "update Modulo set nome_modulo = ?, carga_horaria = ?, categoria = ?, id_Quali_Nivel = ? where codigo = ?";
        $stmt = $this->db->prepare($sql);

        $nome = $modulo->getNome();
        $cargaHoraria = $modulo->getCargaHoraria();
        $categoria = $modulo->getCategoria();
        $codigoQualiNivel = $modulo->getQualiNivel()->getCodigo();
        $codigo = $modulo->getCodigo();

        $stmt->bind_param("sisii", $nome, $cargaHoraria, $categoria, $codigoQualiNivel, $codigo);
        try {
            $result = $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "UPDATE", "Módulo " . $nome . " (ID: " . $codigo . ") foi atualizado", $usuario);
                $log->setData(date('d/m/Y H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function delete($codigo) {
        $sql = "delete from Modulo where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        try {
            $result = $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "DELETE", "Módulo (ID: " . $codigo . ") foi removido", $usuario);
                $log->setData(date('d/m/Y H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function getAll($nome) {
        $sql = "select Modulo.codigo, Modulo.nome_modulo as nome, Modulo.carga_horaria, Modulo.categoria,
                       Quali_Nivel.codigo_Quali_Nivel, Quali_Nivel.cod_Quali, Quali_Nivel.cod_Nivel,
                       Qualificacao.titulo, Nivel.nome as nivel, Quali_modulo.semestre as semestre,
                       (select min(cl.cod_Campo) from Classificacao cl
                         where cl.cod_Qualificacao = Quali_Nivel.cod_Quali) as codCampo
                from Modulo
                join Quali_Nivel on Quali_Nivel.codigo_Quali_Nivel = Modulo.id_Quali_Nivel
                join Qualificacao on Qualificacao.cod_Quali = Quali_Nivel.cod_Quali
                join Nivel on Nivel.codigo = Quali_Nivel.cod_Nivel
                left join Quali_modulo on Quali_modulo.cod_modulo = Modulo.codigo
                where Modulo.nome_modulo like ?";
        $stmt = $this->db->prepare($sql);
        $busca = "%" . $nome . "%";
        $stmt->bind_param("s", $busca);
        $stmt->execute();
        $result = $stmt->get_result();

        $modulos = [];
        while ($rs = $result->fetch_assoc()) {
            $modulo = new Modulo($rs['codigo'], $rs['nome'], $rs['carga_horaria']);
            $modulo->setCategoria($rs['categoria']);

            $qualificacao = new Qualificacao($rs['cod_Quali'], $rs['titulo'], null);
            $qualificacao->setCodCampo($rs['codCampo'] ?? null);
            $nivel = new Nivel($rs['cod_Nivel'], $rs['nivel']);
            $qualiNivel = new Quali_Nivel($rs['codigo_Quali_Nivel'], $rs['cod_Quali'], $rs['cod_Nivel']);
            $qualiNivel->setQualificacao($qualificacao);
            $qualiNivel->setNivel($nivel);
            $modulo->setQualiNivel($qualiNivel);

            $qualiModulo = new Quali_modulo(null, $rs['semestre'], null, null);
            $modulo->setQualiModulo($qualiModulo);

            $modulos[] = $modulo;
        }
        return $modulos;
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

    public function getModulos() {
        return $this->listarOpcoes("select codigo, nome_modulo as descricao from Modulo order by nome_modulo");
    }

    /**
     * Módulos filtrados por qualificação, categoria, semestre, nome, par
     * qualificação/nível e/ou formando.
     *
     * A qualificação é resolvida via Quali_Nivel (Modulo.id_Quali_Nivel) e o
     * semestre via Quali_modulo (Quali_modulo.cod_modulo = Modulo.codigo).
     * Quando $codigoFormando é indicado, são excluídos os módulos em que o
     * formando já possui inscrição.
     */
    public function getModulosFiltrados($codQuali = null, $categoria = null, $semestre = null,
                                       $pesquisa = null, $idQualiNivel = null, $codigoFormando = null) {
        $sql = "select m.codigo as codigo,
                       m.nome_modulo as descricao,
                       m.nome_modulo as nome,
                       m.categoria,
                       m.carga_horaria,
                       m.id_Quali_Nivel,
                       (select min(qm.semestre)
                          from Quali_modulo qm
                         where qm.cod_modulo = m.codigo) as semestre
                from Modulo m
                join Quali_Nivel qn on qn.codigo_Quali_Nivel = m.id_Quali_Nivel";
        $condicoes = [];
        $tipos = "";
        $parametros = [];
        if ($codQuali !== null && (int) $codQuali > 0) {
            $condicoes[] = "qn.cod_Quali = ?";
            $parametros[] = (int) $codQuali;
            $tipos .= "i";
        }
        if ($categoria !== null && strlen($categoria) > 0) {
            $condicoes[] = "m.categoria = ?";
            $parametros[] = $categoria;
            $tipos .= "s";
        }
        if ($semestre !== null && strlen($semestre) > 0) {
            $condicoes[] = "exists (select 1
                                      from Quali_modulo qm_semestre
                                     where qm_semestre.cod_modulo = m.codigo
                                       and qm_semestre.semestre = ?)";
            $parametros[] = $semestre;
            $tipos .= "s";
        }
        if ($pesquisa !== null && strlen($pesquisa) > 0) {
            $condicoes[] = "m.nome_modulo like ?";
            $parametros[] = "%" . $pesquisa . "%";
            $tipos .= "s";
        }
        if ($idQualiNivel !== null && (int) $idQualiNivel > 0) {
            $condicoes[] = "m.id_Quali_Nivel = ?";
            $parametros[] = (int) $idQualiNivel;
            $tipos .= "i";
        }
        if ($codigoFormando !== null && (int) $codigoFormando > 0) {
            $condicoes[] = "not exists (select 1
                                           from Inscricao i
                                          where i.codigo_modulo = m.codigo
                                            and i.codigo_formando = ?)";
            $parametros[] = (int) $codigoFormando;
            $tipos .= "i";
        }
        if (!empty($condicoes)) {
            $sql .= " where " . implode(" and ", $condicoes);
        }
        $sql .= " order by m.nome_modulo";

        $stmt = $this->db->prepare($sql);
        if (!empty($parametros)) {
            $stmt->bind_param($tipos, ...$parametros);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $opcoes = [];
        while ($rs = $result->fetch_assoc()) {
            $opcoes[] = [
                'codigo' => $rs['codigo'],
                'descricao' => $rs['descricao'],
                'nome' => $rs['nome'],
                'categoria' => $rs['categoria'],
                'carga_horaria' => $rs['carga_horaria'],
                'idQualiNivel' => $rs['id_Quali_Nivel'],
                'semestre' => $rs['semestre'],
            ];
        }
        return $opcoes;
    }

    public function existeModuloEmInscricao($codigoModulo) {
        $sql = "select count(*) as total from Inscricao where codigo_modulo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoModulo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();

        return (int) $rs['total'] > 0;
    }

    public function existeModuloEmLicao($codigoModulo) {
        $sql = "select count(*) as total from Licao where cod_Modulo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoModulo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();

        return (int) $rs['total'] > 0;
    }

    public function existeModuloEmQualiModulo($codigoModulo) {
        $sql = "select count(*) as total from Quali_modulo where cod_modulo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoModulo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();

        return (int) $rs['total'] > 0;
    }

    public function existeModuloEmQualiNivel($codigoModulo) {
        $sql = "select count(*) as total from Modulo where id_Quali_Nivel = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoModulo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();

        return (int) $rs['total'] > 0;
    }

    public function existeNivelEmQualificacao($codigoNivel) {
        $sql = "select count(*) as total from Quali_Nivel where cod_Nivel = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoNivel);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();

        return (int) $rs['total'] > 0;
    }

    public function existeNivelEmTurma($codigoNivel) {
        $sql = "select count(*) as total from Turma
                join Quali_Nivel on Turma.id_Quali_Nivel = Quali_Nivel.codigo_Quali_Nivel
                where Quali_Nivel.cod_Nivel = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoNivel);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();

        return (int) $rs['total'] > 0;
    }

    public function getById($codigo) {
        $sql = "select Modulo.codigo, Modulo.nome_modulo as nome, Modulo.carga_horaria, Modulo.categoria,
                       Quali_Nivel.codigo_Quali_Nivel, Quali_Nivel.cod_Quali, Quali_Nivel.cod_Nivel,
                       Qualificacao.titulo, Nivel.nome as nivel, Quali_modulo.semestre as semestre,
                       (select min(cl.cod_Campo) from Classificacao cl
                         where cl.cod_Qualificacao = Quali_Nivel.cod_Quali) as codCampo
                from Modulo
                join Quali_Nivel on Quali_Nivel.codigo_Quali_Nivel = Modulo.id_Quali_Nivel
                join Qualificacao on Qualificacao.cod_Quali = Quali_Nivel.cod_Quali
                join Nivel on Nivel.codigo = Quali_Nivel.cod_Nivel
                left join Quali_modulo on Quali_modulo.cod_modulo = Modulo.codigo
                where Modulo.codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();

        if ($rs === null) {
            return null;
        }

        $modulo = new Modulo($rs['codigo'], $rs['nome'], $rs['carga_horaria']);
        $modulo->setCategoria($rs['categoria']);

        $qualiNivel = new Quali_Nivel($rs['codigo_Quali_Nivel'], $rs['cod_Quali'], $rs['cod_Nivel']);
        $qualificacao = new Qualificacao($rs['cod_Quali'], $rs['titulo'], null);
        $qualificacao->setCodCampo($rs['codCampo'] ?? null);
        $qualiNivel->setQualificacao($qualificacao);
        $qualiNivel->setNivel(new Nivel($rs['cod_Nivel'], $rs['nivel']));
        $modulo->setQualiNivel($qualiNivel);

        $modulo->setQualiModulo(new Quali_modulo(null, $rs['semestre'], null, null));
        return $modulo;
    }
}
?>
