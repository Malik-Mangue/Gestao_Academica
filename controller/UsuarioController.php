<?php
require_once __DIR__ . '/../Dao/UsuarioDao.php';

class UsuarioController {
    private $dao;

    public function __construct() {
        $this->dao = new UsuarioDao();
    }

    public function listar() {
        return $this->dao->getAll();
    }

    public function listarPerfis() {
        return $this->dao->getPerfis();
    }

    public function buscar($codigo) {
        return $this->dao->getById($codigo);
    }

    public function store() {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $usuario = new Usuario(null, (int) $_POST['idPerfil'], trim($_POST['nome']), trim($_POST['username']), trim($_POST['apelido']), $password, 1);
        return $this->dao->create($usuario);
    }

    public function update($codigo) {
        $atual = $this->dao->getById($codigo);
        if ($atual === null) {
            return false;
        }
        $password = $atual->getPassword();
        $primeiroAcesso = $atual->getPrimeiroAcesso();
        if (($_POST['password'] ?? '') !== '') {
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $primeiroAcesso = 1;
        }
        $usuario = new Usuario($codigo, (int) $_POST['idPerfil'], trim($_POST['nome']), trim($_POST['username']), trim($_POST['apelido']), $password, $primeiroAcesso);
        return $this->dao->update($usuario);
    }

    public function delete($codigo) {
        return $this->dao->delete($codigo);
    }
}
?>
