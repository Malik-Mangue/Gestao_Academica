<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../services/Sessao.php';
require_once __DIR__ . '/../model/Usuario.php';
require_once __DIR__ . '/../model/Perfil.php';
require_once __DIR__ . '/../model/Logs.php';
require_once __DIR__ . '/LogDao.php';

class UsuarioDao {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function login($username, $password) {
        $sql = "select * from Usuario
                join Perfil on idPerfil = id
                where username = ? and password = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        $usuario = null;
        while ($rs = $result->fetch_assoc()) {
            $perfil = new Perfil($rs['id'], $rs['nome']);
            $usuario = new Usuario(
                $rs['idUser'],
                null,
                $rs['username'],
                $rs['password'],
                $rs['apelido'],
                $perfil,
                (bool) $rs['primeiroAcesso']
            );
        }

        if ($usuario != null) {
            $log = new Logs(null, "LOGIN", "Utilizador " . $usuario->getUsername() . " iniciou sessão", $usuario);
            $log->setData(date('Y-m-d H:i:s'));
            (new LogDao())->salvar($log);
        }

        return $usuario;
    }

    public function create(Usuario $usuario) {
        $sql = "insert into Usuario (nome, username, password, apelido, idPerfil, primeiroAcesso) values (?, ?, ?, ?, ?, 1)";
        $stmt = $this->db->prepare($sql);

        $nome = $usuario->getNome();
        $username = $usuario->getUsername();
        $password = $usuario->getPassword();
        $apelido = $usuario->getApelido();
        $idPerfil = $usuario->getPerfil()->getId();

        $stmt->bind_param("ssssi", $nome, $username, $password, $apelido, $idPerfil);

        try {
            $result = $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            throw new Exception("Esse nome de utilizador já existe. Escolha outro username.");
        }

        if ($result) {
            $usuarioLogado = Sessao::obterUtilizador();
            if ($usuarioLogado != null) {
                $log = new Logs(null, "INSERT", $usuario->getPerfil()->getNome() . " " . $nome . " foi cadastrado", $usuarioLogado);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function getAll($username = null) {
        if ($username != null && strlen($username) > 0) {
            $sql = "select idUser, Usuario.nome, username, apelido, Perfil.nome as nome_perfil from Usuario
                    join Perfil on idPerfil = id
                    where username like ?";
            $stmt = $this->db->prepare($sql);
            $busca = "%" . $username . "%";
            $stmt->bind_param("s", $busca);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $sql = "select idUser, Usuario.nome, username, apelido, Perfil.nome as nome_perfil from Usuario
                    join Perfil on idPerfil = id";
            $result = mysqli_query($this->db, $sql);
        }

        $usuarios = [];
        while ($rs = $result->fetch_assoc()) {
            $perfil = new Perfil(null, $rs['nome_perfil']);
            $usuarios[] = new Usuario($rs['idUser'], $rs['nome'], $rs['username'], null, $rs['apelido'], $perfil);
        }
        return $usuarios;
    }

    public function getPerfis() {
        $sql = "select id, nome from Perfil order by nome";
        $result = mysqli_query($this->db, $sql);

        $perfis = [];
        while ($rs = $result->fetch_assoc()) {
            $perfis[] = new Perfil($rs['id'], $rs['nome']);
        }
        return $perfis;
    }

    public function getById($codigo) {
        return $this->obterUsuarioPorCodigo($codigo);
    }

    public function refinirSenha($novapassword, $codigo) {
        $sql = "update Usuario set password = ?, primeiroAcesso = 0 where idUser = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $novapassword, $codigo);
        return $stmt->execute();
    }

    public function autenticar($password) {
        $sql = "select idUser, password from Usuario where password = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $password);
        $stmt->execute();
        $result = $stmt->get_result();

        $usuario = null;
        while ($rs = $result->fetch_assoc()) {
            $usuario = new Usuario();
            $usuario->setPassword($rs['password']);
            $usuario->setCodigo($rs['idUser']);
        }
        return $usuario;
    }

    public function resetarSenha($senhaResetada, $codigo) {
        $sql = "update Usuario set password = ?, primeiroAcesso = 1 where idUser = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $senhaResetada, $codigo);
        $result = $stmt->execute();

        if ($result) {
            $logUser = Sessao::obterUtilizador();
            if ($logUser != null) {
                $log = new Logs(null, "UPDATE", "Senha do utilizador (ID: " . $codigo . ") foi resetada", $logUser);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function obterTodosUsuarios() {
        return $this->getAll();
    }

    public function obterUsuarioPorCodigo($codigo) {
        $sql = "select idUser, idPerfil, nome, username, apelido, password, primeiroAcesso from Usuario where idUser = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();

        if ($rs === null) {
            return null;
        }

        $perfil = new Perfil($rs['idPerfil'], null);
        return new Usuario(
            $rs['idUser'],
            $rs['nome'],
            $rs['username'],
            $rs['password'],
            $rs['apelido'],
            $perfil,
            (bool) $rs['primeiroAcesso']
        );
    }

    public function getByUsername($username) {
        $sql = "select idUser, idPerfil, nome, username, apelido, password, primeiroAcesso from Usuario where username = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();

        if ($rs === null) {
            return null;
        }

            $usuario = new Usuario(
            $rs['idUser'],
            $rs['idPerfil'],
            $rs['nome'],
            $rs['username'],
            $rs['apelido'],
            $rs['password'],
            (int) $rs['primeiroAcesso']
        );
        $usuario->setPerfil(new Perfil($rs['idPerfil'], null));
        return $usuario;
    }

    public function atualizarPassword($codigo, $password, $primeiroAcesso) {
        $sql = "update Usuario set password = ?, primeiroAcesso = ? where idUser = ?";
        $stmt = $this->db->prepare($sql);
        $primeiroAcesso = (int) $primeiroAcesso;
        $stmt->bind_param("sii", $password, $primeiroAcesso, $codigo);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function update(Usuario $usuario) {
        $sql = "update Usuario set idPerfil = ? where idUser = ?";

        $usernameAtual = "";
        $existente = $this->obterUsuarioPorCodigo($usuario->getCodigo());
        if ($existente != null) {
            $usernameAtual = $existente->getUsername();
        }

        $stmt = $this->db->prepare($sql);
        $idPerfil = $usuario->getPerfil()->getId();
        $codigo = $usuario->getCodigo();
        $stmt->bind_param("ii", $idPerfil, $codigo);
        $result = $stmt->execute();

        if ($result) {
            $logUser = Sessao::obterUtilizador();
            if ($logUser != null) {
                $log = new Logs(null, "UPDATE", "Utilizador " . $usernameAtual . " (ID: " . $codigo . ") teve o perfil atualizado para " . $usuario->getPerfil()->getNome(), $logUser);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function delete(Usuario $usuario) {
        $sql = "delete from Usuario where idUser = ?";
        $stmt = $this->db->prepare($sql);
        $codigo = $usuario->getCodigo();
        $stmt->bind_param("i", $codigo);
        $result = $stmt->execute();

        if ($result) {
            $logUser = Sessao::obterUtilizador();
            if ($logUser != null) {
                $log = new Logs(null, "DELETE", "Utilizador (ID: " . $codigo . ") foi removido", $logUser);
                $log->setData(date('Y-m-d H:i:s'));
                (new LogDao())->salvar($log);
            }
        }

        return $result;
    }

    public function registarLogout(Usuario $usuario) {
        if ($usuario != null) {
            $log = new Logs(null, "LOGOUT", "Utilizador " . $usuario->getUsername() . " terminou sessão", $usuario);
            $log->setData(date('Y-m-d H:i:s'));
            (new LogDao())->salvar($log);
        }
    }
}
?>
