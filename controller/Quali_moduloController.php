<?php
require_once __DIR__ . '/../Dao/Quali_moduloDao.php';

class Quali_moduloController {
    private $dao;

    public function __construct() {
        $this->dao = new Quali_moduloDao();
    }

    public function listar() {
        return $this->dao->getAll();
    }

    public function listarModulos() {
        return $this->dao->getModulos();
    }

    public function listarQualificacoes() {
        return $this->dao->getQualificacoes();
    }

    public function buscar($codigo) {
        return $this->dao->getById($codigo);
    }

    public function store() {
        $qualiModulo = new Quali_modulo(null, (int) $_POST['cod_modulo'], (int) $_POST['cod_quali'], trim($_POST['semestre']));
        return $this->dao->create($qualiModulo);
    }

    public function update($codigo) {
        $qualiModulo = new Quali_modulo($codigo, (int) $_POST['cod_modulo'], (int) $_POST['cod_quali'], trim($_POST['semestre']));
        return $this->dao->update($qualiModulo);
    }

    public function delete($codigo) {
        return $this->dao->delete($codigo);
    }
}
?>
