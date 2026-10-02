<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../Dao/UsuarioDao.php';
require_once __DIR__ . '/../Dao/PerfilDao.php';

class UsuarioController {
    // Senha utilizada pelo "Reset senha" da gestão de utilizadores
    const SENHA_PADRAO = '1234';

    private $dao;

    public function __construct() {
        $this->dao = new UsuarioDao();
    }

    public function listar() {
        return $this->dao->getAll();
    }

    // Perfis existentes rastreados na base de dados atraves da classe Perfil
    public function listarPerfis() {
        $perfilDao = new PerfilDao();
        return $perfilDao->getAll();
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

    // Autenticação por username + password (password_verify)
    public function autenticar($username, $password) {
        $username = trim($username);
        if ($username === '' || $password === '') {
            return false;
        }
        $usuario = $this->dao->getByUsername($username);
        if ($usuario === null) {
            return false;
        }
        if (!password_verify($password, $usuario->getPassword())) {
            return false;
        }
        return $usuario;
    }

    // Redefinição da própria senha: valida a senha antiga e grava a nova
    public function redefinirSenha($username, $senhaAntiga, $senhaNova) {
        $usuario = $this->dao->getByUsername(trim($username));
        if ($usuario === null) {
            return false;
        }
        if (!password_verify($senhaAntiga, $usuario->getPassword())) {
            return false;
        }
        $hash = password_hash($senhaNova, PASSWORD_BCRYPT);
        return $this->dao->atualizarPassword($usuario->getCodigo(), $hash, 0);
    }

    // Reset administrativo: volta à senha padrão e obriga ao primeiro acesso
    public function resetSenha($codigo) {
        $usuario = $this->dao->getById($codigo);
        if ($usuario === null) {
            return false;
        }
        $hash = password_hash(UsuarioController::SENHA_PADRAO, PASSWORD_BCRYPT);
        return $this->dao->atualizarPassword($codigo, $hash, 1);
    }
}
?>
