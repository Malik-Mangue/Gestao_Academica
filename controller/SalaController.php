<?php
require_once __DIR__ . '/../Dao/SalaDao.php';

class SalaController {
    private $dao;

    public function __construct() {
        $this->dao = new SalaDao();
    }

    public function listar() {
        return $this->dao->getAll();
    }

    public function buscar($codigo) {
        return $this->dao->getById($codigo);
    }

    public function store() {
        $sala = new Sala(null, $_POST['designacao'], $_POST['tipo_sala']);
        return $this->dao->create($sala);
    }

    public function update($codigo) {
        $sala = new Sala($codigo, $_POST['designacao'], $_POST['tipo_sala']);
        return $this->dao->update($sala);
    }

    public function delete($codigo) {
        return $this->dao->delete($codigo);
    }
}
?>
