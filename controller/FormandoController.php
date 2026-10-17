<?php
require_once __DIR__ . '/../Dao/FormandoDao.php';

class FormandoController {
    private $dao;

    public function __construct() {
        $this->dao = new FormandoDao();
    }

    public function listar($pesquisa = null, $codCampo = null) {
        return $this->dao->getAll($pesquisa, $codCampo);
    }

    public function buscar($codigo) {
        return $this->dao->getById($codigo);
    }

    public function listarPorCampo($codCampo = null) {
        return $this->dao->getFormandosPorCampo($codCampo);
    }

   public function store() {
    $contacto = !empty($_POST['contacto']) ? (int) $_POST['contacto'] : null;
    $email = !empty($_POST['email']) ? $_POST['email'] : null;
    $formando = new Formando(null, $_POST['nome'], $_POST['apelido'], $email, $_POST['bi'], $contacto);
    $formando->setCodCampo(isset($_POST['codCampo']) ? (int) $_POST['codCampo'] : null);
    return $this->dao->create($formando);
}

public function update($codigo) {
    $contacto = !empty($_POST['contacto']) ? (int) $_POST['contacto'] : null;
    $email = !empty($_POST['email']) ? $_POST['email'] : null;
    $formando = new Formando($codigo, $_POST['nome'], $_POST['apelido'], $email, $_POST['bi'], $contacto);
    $formando->setCodCampo(isset($_POST['codCampo']) ? (int) $_POST['codCampo'] : null);
    return $this->dao->update($formando);
}

    public function delete($codigo) {
        return $this->dao->delete($codigo);
    }
}
?>
