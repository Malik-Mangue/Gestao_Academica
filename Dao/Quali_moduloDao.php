<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../model/Quali_modulo.php';

class Quali_moduloDAO {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAll() {
        $sql = "select codigo, cod_modulo, cod_Quali, semestre from Quali_modulo order by codigo";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $qualiModulos = [];
        while ($rs = $result->fetch_assoc()) {
            $qualiModulos[] = new Quali_modulo($rs['codigo'], $rs['cod_modulo'], $rs['cod_Quali'], $rs['semestre']);
        }
        return $qualiModulos;
    }

    public function getById($codigo) {
        $sql = "select codigo, cod_modulo, cod_Quali, semestre from Quali_modulo where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        if ($rs === null) {
            return null;
        }
        return new Quali_modulo($rs['codigo'], $rs['cod_modulo'], $rs['cod_Quali'], $rs['semestre']);
    }

    public function create(Quali_modulo $qualiModulo) {
        $sql = "insert into Quali_modulo (cod_modulo, cod_Quali, semestre) values (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $cod_modulo = $qualiModulo->getCod_modulo();
        $cod_quali = $qualiModulo->getCod_quali();
        $semestre = $qualiModulo->getSemestre();
        $stmt->bind_param("iis", $cod_modulo, $cod_quali, $semestre);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function update(Quali_modulo $qualiModulo) {
        $sql = "update Quali_modulo set cod_modulo = ?, cod_Quali = ?, semestre = ? where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $cod_modulo = $qualiModulo->getCod_modulo();
        $cod_quali = $qualiModulo->getCod_quali();
        $semestre = $qualiModulo->getSemestre();
        $codigo = $qualiModulo->getCodigo();
        $stmt->bind_param("iisi", $cod_modulo, $cod_quali, $semestre, $codigo);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function delete($codigo) {
        $sql = "delete from Quali_modulo where codigo = ?";
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

    public function getModulos() {
        return $this->listaOpcoes("select codigo, nome_modulo as descricao from Modulo order by nome_modulo");
    }

    public function getQualificacoes() {
        return $this->listaOpcoes("select cod_Quali as codigo, titulo as descricao from Qualificacao order by titulo");
    }
}
?>
