<?php
require_once __DIR__ . '/../Dao/NivelDao.php';

class NivelController {
    private $dao;

    public function __construct() {
        $this->dao = new NivelDao();
    }

    public function listar() {
        return $this->dao->getAll();
    }

    public function buscar($codigo) {
        return $this->dao->getById($codigo);
    }

    public function store() {
        $nivel = new Nivel(null, $_POST['nome']);
        return $this->dao->create($nivel);
    }

    public function update($codigo) {
        $nivel = new Nivel($codigo, $_POST['nome']);
        return $this->dao->update($nivel);
    }

    public function delete($codigo) {
        return $this->dao->delete($codigo);
    }
}
?>