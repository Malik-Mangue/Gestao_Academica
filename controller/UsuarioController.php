<?php
require_once __DIR__ . '/../Dao/UsuarioDao.php';
require_once __DIR__ . '/../Dao/PerfilDao.php';
require_once __DIR__ . '/../services/Sessao.php';

class UsuarioController {

    // Senha reposta pelo Administrador na acao "Reset senha". O primeiro
    // acesso fica obrigatorio, portanto o utilizador tem de a trocar no login.
    const SENHA_PADRAO = 1234;


    private $dao;

    public function __construct() {
        $this->dao = new UsuarioDao();
    }

    public function listar() {
        return $this->dao->getAll();
    }

    public function listarPerfis() {
        $perfilDao = new PerfilDao();
        return $perfilDao->getAll();
    }

    public function buscar($codigo) {
        return $this->dao->getById($codigo);
    }

    // Cria um utilizador novo. primeiroAcesso e sempre 1 (decisao do sistema,
    // nunca escolha do Administrador), forcando a troca de senha no login.
    public function store(Usuario $usuario) {
        // $password = password_hash($_POST['password'] ?? '', PASSWORD_BCRYPT);
        $usuario->setPassword(password_hash(Usuariocontroller::SENHA_PADRAO, PASSWORD_BCRYPT));
        // $usuario = new Usuario(
        //     null,
        //     (int) ($_POST['idPerfil'] ?? 0),
        //     trim($_POST['nome'] ?? ''),
        //     trim($_POST['username'] ?? ''),
        //     trim($_POST['apelido'] ?? ''),
        //     $password,
        //     1
        // );
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

        $usuario = new Usuario(
            $codigo,
            (int) ($_POST['idPerfil'] ?? 0),
            trim($_POST['nome'] ?? ''),
            trim($_POST['username'] ?? ''),
            trim($_POST['apelido'] ?? ''),
            $password,
            $primeiroAcesso
        );
        return $this->dao->update($usuario);
    }

    public function delete($codigo) {
        return $this->dao->delete($codigo);
    }

    // Autenticacao: a password e sempre verificada com password_verify sobre
    // o hash bcrypt guardado na base de dados.
    public function autenticar($username, $password) {
        $username = trim((string) $username);
        if ($username === '' || (string) $password === '') {
            return false;
        }

        $usuario = $this->dao->getByUsername($username);
        if ($usuario === null) {
            return false;
        }
        if (!password_verify($password, $usuario->getPassword())) {
            return false;
        }

        $this->dao->registarLogin($usuario);

        return $usuario;
    }

    // Troca de senha no primeiro acesso ou por iniciativa do proprio utilizador.
    // Devolve false quando a senha antiga nao confere.
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

    // "Reset senha" (Administrador): repoe a senha padrao e volta a exigir
    // a troca obrigatoria no proximo acesso.
    public function resetSenha($codigo) {
        $usuario = $this->dao->getById($codigo);
        if ($usuario === null) {
            return false;
        }

        $hash = password_hash(UsuarioController::SENHA_PADRAO, PASSWORD_BCRYPT);
        $resultado = $this->dao->atualizarPassword($codigo, $hash, 1);
        if ($resultado) {
            (new UsuarioDao())->registarLog(
                "UPDATE",
                "Senha do utilizador " . $usuario->getUsername() . " (ID: " . $codigo . ") foi resetada"
            );
        }

        return $resultado;
    }
}
?>
