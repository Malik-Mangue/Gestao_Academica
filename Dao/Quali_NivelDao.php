<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../model/Quali_Nivel.php';

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
            return $stmt->execute();
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
}
?>
