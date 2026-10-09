<?php
require_once __DIR__ . '/../Dao/Quali_NivelDao.php';
require_once __DIR__ . '/../model/Quali_Nivel.php';

class Quali_NivelController {
    private $dao;

    public function __construct() {
        $this->dao = new Quali_NivelDao();
    }

    public function listar() {
        return $this->dao->getAll();
    }

    public function listarQualificacoes() {
        return $this->dao->getQualificacoes();
    }

    public function listarNiveis() {
        return $this->dao->getNiveis();
    }

    public function buscar($codigo) {
        return $this->dao->getById($codigo);
    }

    public function store() {
        $qualiNivel = new Quali_Nivel(null, (int) $_POST['cod_quali'], (int) $_POST['cod_nivel']);
        return $this->dao->create($qualiNivel);
    }

    public function update($codigo) {
        $qualiNivel = new Quali_Nivel($codigo, (int) $_POST['cod_quali'], (int) $_POST['cod_nivel']);
        return $this->dao->update($qualiNivel);
    }

    public function delete($codigo) {
        return $this->dao->delete($codigo);
    }

    public function cadastrarQuali_Nivel($nivel, $qualificacao) {
        if ($nivel != null && $qualificacao != null) {
            $qualiNivel = new Quali_Nivel(null, $qualificacao->getCodigo(), $nivel->getCodigo());
            $this->dao->create($qualiNivel);
            return $qualiNivel;
        }
        return null;
    }

    public function getQualificacao_Nivel($qualificacao) {
        return $this->dao->getQualificacao_Nivel($qualificacao);
    }

    public function listarNiveisDaQualificacao($codQualificacao) {
        return $this->dao->getNiveisDaQualificacao($codQualificacao);
    }

    public function listarNiveisPorQualificacao() {
        return $this->dao->getNiveisPorQualificacao();
    }

    public function buscarCodigo($qualificacao, $nivel) {
        return $this->dao->buscarCodigo($qualificacao, $nivel);
    }
}
?>
