<?php
require_once __DIR__ . '/../Dao/FormandoDao.php';

class FormandoController {
    private $dao;

    public function __construct() {
        $this->dao = new FormandoDao();
    }

    public function listar() {
        return $this->dao->getAll();
    }

    public function buscar($codigo) {
        return $this->dao->getById($codigo);
    }

    public function store() {
        $contacto = !empty($_POST['contacto']) ? (int) $_POST['contacto'] : null;
        $email = !empty($_POST['email']) ? $_POST['email'] : null;
        $formando = new Formando(null, $_POST['nome'], $_POST['apelido'], $contacto, $email, $_POST['bi']);
        return $this->dao->create($formando);
    }

    public function update($codigo) {
        $contacto = !empty($_POST['contacto']) ? (int) $_POST['contacto'] : null;
        $email = !empty($_POST['email']) ? $_POST['email'] : null;
        $formando = new Formando($codigo, $_POST['nome'], $_POST['apelido'], $contacto, $email, $_POST['bi']);
        return $this->dao->update($formando);
    }

    public function delete($codigo) {
        return $this->dao->delete($codigo);
    }
}
?>
