<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../model/Qualificacao.php';

class QualificacaoDAO {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAll($titulo = null) {
        if ($titulo != null && strlen($titulo) > 0) {
            $sql = "select cod_Quali, titulo, cod_Coordenador from Qualificacao where titulo like ? order by cod_Quali";
            $stmt = $this->db->prepare($sql);
            $busca = "%" . $titulo . "%";
            $stmt->bind_param("s", $busca);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $sql = "select cod_Quali, titulo, cod_Coordenador from Qualificacao order by cod_Quali";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
        }
        $qualificacaos = [];
        while ($rs = $result->fetch_assoc()) {
            $qualificacaos[] = new Qualificacao($rs['cod_Quali'], $rs['titulo'], $rs['cod_Coordenador']);
        }
        return $qualificacaos;
    }

    public function getById($codigo) {
        $sql = "select cod_Quali, titulo, cod_Coordenador from Qualificacao where cod_Quali = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        if ($rs === null) {
            return null;
        }
        return new Qualificacao($rs['cod_Quali'], $rs['titulo'], $rs['cod_Coordenador']);
    }

    public function create(Qualificacao $qualificacao) {
        $sql = "insert into Qualificacao (titulo, cod_Coordenador) values (?, ?)";
        $stmt = $this->db->prepare($sql);
        $titulo = $qualificacao->getTitulo();
        $cod_coordenador = $qualificacao->getCod_coordenador();
        $stmt->bind_param("si", $titulo, $cod_coordenador);
        try {
            $result = $stmt->execute();
            if ($result) {
                $qualificacao->setCodigo($this->db->insert_id);
            }
            return $result;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function update(Qualificacao $qualificacao) {
        $sql = "update Qualificacao set titulo = ?, cod_Coordenador = ? where cod_Quali = ?";
        $stmt = $this->db->prepare($sql);
        $titulo = $qualificacao->getTitulo();
        $cod_coordenador = $qualificacao->getCod_coordenador();
        $codigo = $qualificacao->getCodigo();
        $stmt->bind_param("sii", $titulo, $cod_coordenador, $codigo);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function delete($codigo) {
        $sql = "delete from Qualificacao where cod_Quali = ?";
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

    public function getCoordenadores() {
        return $this->listaOpcoes("select c.cod_Formador as codigo, concat(f.nome, ' ', f.apelido) as descricao from Coordenador c inner join Formador f on f.codigo = c.cod_Formador order by f.nome");
    }

    public function existeQualificacaoEmClassificacao($codigoQualificacao) {
        $sql = "select count(*) as total from Classificacao where cod_Qualificacao = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoQualificacao);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs['total'] > 0;
    }

    public function existeQualificacaoEmQualiNivel($codigoQualificacao) {
        $sql = "select count(*) as total from Quali_Nivel where cod_Quali = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoQualificacao);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs['total'] > 0;
    }

    public function existeQualificacaoEmQualiModulo($codigoQualificacao) {
        $sql = "select count(*) as total from Quali_modulo where cod_Quali = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigoQualificacao);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs['total'] > 0;
    }
}
?>
