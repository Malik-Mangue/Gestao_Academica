<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../model/Quali_Nivel.php';
require_once __DIR__ . '/../model/Nivel.php';

class Quali_NivelDAO {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAll() {
        $sql = "select codigo_Quali_Nivel, cod_Quali, cod_Nivel from Quali_Nivel order by codigo_Quali_Nivel";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $qualiNivels = [];
        while ($rs = $result->fetch_assoc()) {
            $qualiNivels[] = new Quali_Nivel($rs['codigo_Quali_Nivel'], $rs['cod_Quali'], $rs['cod_Nivel']);
        }
        return $qualiNivels;
    }

    public function getById($codigo) {
        $sql = "select codigo_Quali_Nivel, cod_Quali, cod_Nivel from Quali_Nivel where codigo_Quali_Nivel = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        if ($rs === null) {
            return null;
        }
        return new Quali_Nivel($rs['codigo_Quali_Nivel'], $rs['cod_Quali'], $rs['cod_Nivel']);
    }

    public function create(Quali_Nivel $qualiNivel) {
        $sql = "insert into Quali_Nivel (cod_Quali, cod_Nivel) values (?, ?)";
        $stmt = $this->db->prepare($sql);
        $cod_quali = $qualiNivel->getCod_quali();
        $cod_nivel = $qualiNivel->getCod_nivel();
        $stmt->bind_param("ii", $cod_quali, $cod_nivel);
        try {
            $result = $stmt->execute();
            if ($result) {
                $qualiNivel->setCodigo($this->db->insert_id);
            }
            return $result;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function update(Quali_Nivel $qualiNivel) {
        $sql = "update Quali_Nivel set cod_Quali = ?, cod_Nivel = ? where codigo_Quali_Nivel = ?";
        $stmt = $this->db->prepare($sql);
        $cod_quali = $qualiNivel->getCod_quali();
        $cod_nivel = $qualiNivel->getCod_nivel();
        $codigo = $qualiNivel->getCodigo();
        $stmt->bind_param("iii", $cod_quali, $cod_nivel, $codigo);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function delete($codigo) {
        $sql = "delete from Quali_Nivel where codigo_Quali_Nivel = ?";
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

    public function getQualificacoes() {
        return $this->listaOpcoes("select cod_Quali as codigo, titulo as descricao from Qualificacao order by titulo");
    }

    public function getNiveis() {
        return $this->listaOpcoes("select codigo, nome as descricao from Nivel order by nome");
    }

    // Niveis de uma qualificacao (tabela associativa Quali_Nivel): opcoes do
    // <select> de nivel que depende da qualificacao escolhida no formulario.
    public function getNiveisDaQualificacao($codQualificacao) {
        $sql = "select Nivel.codigo, Nivel.nome from Quali_Nivel
                join Nivel on Nivel.codigo = Quali_Nivel.cod_Nivel
                where Quali_Nivel.cod_Quali = ? order by Nivel.nome";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codQualificacao);
        $stmt->execute();
        $result = $stmt->get_result();

        $opcoes = [];
        while ($rs = $result->fetch_assoc()) {
            $opcoes[] = ['codigo' => $rs['codigo'], 'descricao' => $rs['nome']];
        }
        return $opcoes;
    }

    // Todos os pares (qualificacao -> niveis) numa so consulta: alimenta o
    // mapa JavaScript que filtra os niveis conforme a qualificacao escolhida.
    public function getNiveisPorQualificacao() {
        $sql = "select Quali_Nivel.cod_Quali, Quali_Nivel.cod_Nivel, Nivel.nome
                from Quali_Nivel
                join Nivel on Nivel.codigo = Quali_Nivel.cod_Nivel
                order by Quali_Nivel.cod_Quali, Nivel.nome";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        $lista = [];
        while ($rs = $result->fetch_assoc()) {
            $lista[] = [
                'cod_quali' => (int) $rs['cod_Quali'],
                'cod_nivel' => (int) $rs['cod_Nivel'],
                'nome'      => $rs['nome'],
            ];
        }
        return $lista;
    }

    public function buscarCodigo($qualificacao, $nivel) {
        $sql = "select codigo_Quali_Nivel from Quali_Nivel where cod_Quali = ? and cod_Nivel = ?";
        $stmt = $this->db->prepare($sql);
        $codQuali = $qualificacao->getCodigo();
        $codNivel = $nivel->getCodigo();
        $stmt->bind_param("ii", $codQuali, $codNivel);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs != null ? $rs['codigo_Quali_Nivel'] : 0;
    }

    public function getQualificacao_Nivel($qualificacao) {
        $sql = "select Nivel.codigo, Nivel.nome from Quali_Nivel
                join Qualificacao on Quali_Nivel.cod_Quali = Qualificacao.cod_Quali
                join Nivel on Nivel.codigo = Quali_Nivel.cod_Nivel
                where Qualificacao.cod_Quali = ?";
        $stmt = $this->db->prepare($sql);
        $codQuali = $qualificacao->getCodigo();
        $stmt->bind_param("i", $codQuali);
        $stmt->execute();
        $result = $stmt->get_result();

        $niveis = [];
        while ($rs = $result->fetch_assoc()) {
            $niveis[] = new Nivel($rs['codigo'], $rs['nome']);
        }
        return $niveis;
    }
}
?>
