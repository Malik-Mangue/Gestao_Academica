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
        $sql = "insert into Modulo (nome_modulo, carga_horaria, id_Quali_Nivel) values (?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        $nome = $modulo->getNome();
        $cargaHoraria = $modulo->getCargaHoraria();
        $codigoQualiNivel = $modulo->getQualiNivel()->getCodigo();

        $stmt->bind_param("sii", $nome, $cargaHoraria, $codigoQualiNivel);
        $result = $stmt->execute();

        if ($result) {
            $modulo->setCodigo($this->db->insert_id);

            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "INSERT", "Módulo " . $nome . " foi cadastrado", $usuario);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function update(Modulo $modulo) {
        $sql = "update Modulo set nome_modulo = ?, carga_horaria = ?, id_Quali_Nivel = ? where codigo = ?";
        $stmt = $this->db->prepare($sql);

        $nome = $modulo->getNome();
        $cargaHoraria = $modulo->getCargaHoraria();
        $codigoQualiNivel = $modulo->getQualiNivel()->getCodigo();
        $codigo = $modulo->getCodigo();

        $stmt->bind_param("siii", $nome, $cargaHoraria, $codigoQualiNivel, $codigo);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "UPDATE", "Módulo " . $nome . " (ID: " . $codigo . ") foi atualizado", $usuario);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function delete($codigo) {
        $sql = "delete from Modulo where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $result = $stmt->execute();

        if ($result) {
            $usuario = Sessao::obterUtilizador();
            if ($usuario != null) {
                $log = new Logs(null, "DELETE", "Módulo (ID: " . $codigo . ") foi removido", $usuario);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function getAll($nome) {
        $sql = "select Modulo.codigo, Modulo.nome_modulo as nome, Modulo.carga_horaria,
                       Qualificacao.titulo, Nivel.nome as nivel, Quali_modulo.semestre as semestre
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

            $qualificacao = new Qualificacao(null, $rs['titulo'], null);
            $nivel = new Nivel(null, $rs['nivel']);
            $qualiNivel = new Quali_Nivel(null, null, null);
            $qualiNivel->setQualificacao($qualificacao);
            $qualiNivel->setNivel($nivel);
            $modulo->setQualiNivel($qualiNivel);

            $qualiModulo = new Quali_modulo(null, $rs['semestre'], null, null);
            $modulo->setQualiModulo($qualiModulo);

            $modulos[] = $modulo;
        }
        return $modulos;
    }

    public function getById($codigo) {
        $sql = "select codigo, nome_modulo as nome, carga_horaria, id_Quali_Nivel from Modulo where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();

        if ($rs === null) {
            return null;
        }

        $modulo = new Modulo($rs['codigo'], $rs['nome'], $rs['carga_horaria']);
        $modulo->setQualiNivel(new Quali_Nivel($rs['id_Quali_Nivel'], null, null));
        return $modulo;
    }
}
?>
