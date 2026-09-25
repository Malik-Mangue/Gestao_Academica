<?php
require_once __DIR__ . '/../Dao/CampoDao.php';

class CampoController {
    private $dao;

    public function __construct() {
        $this->dao = new CampoDao();
    }

    public function listar() {
        return $this->dao->getAll();
    }

    public function buscar($codigo) {
        return $this->dao->getById($codigo);
    }

    public function store() {
        $campo = new Campo(null, $_POST['nome']);
        $this->dao->create($campo);
    }

    public function update($codigo) {
        $campo = new Campo($codigo, $_POST['nome']);
        $this->dao->update($campo);
    }

    public function delete($codigo) {
        $this->dao->delete($codigo);
    }
}
?>
