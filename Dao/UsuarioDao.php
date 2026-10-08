<?php
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

    // Ordem canonica do construtor Usuario:
    // (codigo, idPerfil, nome, username, apelido, password, primeiroAcesso)
    private function montarUsuario($rs) {
        $usuario = new Usuario(
            $rs['idUser'],
            $rs['idPerfil'],
            $rs['nome'],
            $rs['username'],
            $rs['apelido'],
            (int) $rs['primeiroAcesso'] === 1
        );
        $usuario->setPerfil(new Perfil($rs['idPerfil'], $rs['nome_perfil'] ?? null));

        return $usuario;
    }

    // Regista uma acao de auditoria em nome do utilizador autenticado.
    public function registarLog($acao, $descricao) {
        $usuarioLogado = Sessao::obterUtilizador();
        if ($usuarioLogado === null) {
            return;
        }
        $log = new Logs(null, $acao, $descricao, $usuarioLogado);
        $log->setData(date('Y-m-d H:i:s'));
        (new LogDao())->salvar($log);
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
            $this->registarLog("INSERT", $apelido . " " . $nome . " foi cadastrado");
        }

        return $result;
    }

    public function getAll($username = null) {
        $sql = "select Usuario.idUser, Usuario.idPerfil, Usuario.nome, Usuario.username,
                       Usuario.apelido, Usuario.primeiroAcesso, Perfil.nome as nome_perfil
                from Usuario
                join Perfil on Usuario.idPerfil = Perfil.id";

        if ($username !== null && strlen($username) > 0) {
            $sql .= " where Usuario.username like ? order by Usuario.nome";
            $stmt = $this->db->prepare($sql);
            $busca = "%" . $username . "%";
            $stmt->bind_param("s", $busca);
        } else {
            $sql .= " order by Usuario.nome";
            $stmt = $this->db->prepare($sql);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $usuarios = [];
        while ($rs = $result->fetch_assoc()) {
            $usuarios[] = $this->montarUsuario($rs);
        }
        return $usuarios;
    }

    public function getPerfis() {
        $sql = "select id, nome from Perfil order by nome";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        $perfis = [];
        while ($rs = $result->fetch_assoc()) {
            $perfis[] = new Perfil($rs['id'], $rs['nome']);
        }
        return $perfis;
    }

    public function getById($codigo) {
        return $this->obterUsuarioPorCodigo($codigo);
    }

    public function obterTodosUsuarios() {
        return $this->getAll();
    }

    public function obterUsuarioPorCodigo($codigo) {
        $sql = "select Usuario.idUser, Usuario.idPerfil, Usuario.nome, Usuario.username,
                       Usuario.apelido, Usuario.password, Usuario.primeiroAcesso,
                       Perfil.nome as nome_perfil
                from Usuario
                join Perfil on Usuario.idPerfil = Perfil.id
                where Usuario.idUser = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();

        if ($rs === null) {
            return null;
        }

        return $this->montarUsuario($rs);
    }

    public function getByUsername($username) {
        $sql = "select Usuario.idUser, Usuario.idPerfil, Usuario.nome, Usuario.username,
                       Usuario.apelido, Usuario.password, Usuario.primeiroAcesso,
                       Perfil.nome as nome_perfil
                from Usuario
                join Perfil on Usuario.idPerfil = Perfil.id
                where Usuario.username = ?";
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
        $sql = "update Usuario set nome = ?, username = ?, apelido = ?, idPerfil = ?,
                       password = ?, primeiroAcesso = ?
                where idUser = ?";

        $existente = $this->obterUsuarioPorCodigo($usuario->getCodigo());
        if ($existente === null) {
            return false;
        }

        // A password so e reescrita quando chega preenchida (ja com hash).
        $password = ($usuario->getPassword() !== null && $usuario->getPassword() !== '')
            ? $usuario->getPassword()
            : $existente->getPassword();

        $stmt = $this->db->prepare($sql);
        $nome = $usuario->getNome();
        $username = $usuario->getUsername();
        $apelido = $usuario->getApelido();
        $idPerfil = $usuario->getIdPerfil();
        $primeiroAcesso = (int) $usuario->getPrimeiroAcesso();
        $codigo = $usuario->getCodigo();

        $stmt->bind_param("sssssii", $nome, $username, $apelido, $idPerfil, $password, $primeiroAcesso, $codigo);

        try {
            $result = $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            throw new Exception("Esse nome de utilizador já existe. Escolha outro username.");
        }

        if ($result) {
            $this->registarLog("UPDATE", "Utilizador " . $username . " (ID: " . $codigo . ") foi atualizado");
        }

        return $result;
    }

    public function delete($codigo) {
        $sql = "delete from Usuario where idUser = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);

        try {
            $result = $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }

        if ($result) {
            $this->registarLog("DELETE", "Utilizador (ID: " . $codigo . ") foi removido");
        }

        return $result;
    }

    // Regista o login apos autenticacao bem sucedida.
    public function registarLogin(Usuario $usuario) {
        $log = new Logs(null, "LOGIN", "Utilizador " . $usuario->getUsername() . " iniciou sessão", $usuario);
        $log->setData(date('Y-m-d H:i:s'));
        (new LogDao())->salvar($log);
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
