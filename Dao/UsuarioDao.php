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
    // (codigo, idPerfil, nome, username, apelido, password, primeiroAcesso,
    //  estadoCivil, genero, telefone, email, bi, perfil)
    private function montarUsuario($rs) {
        $usuario = new Usuario(
            $rs['idUser'],
            $rs['idPerfil'],
            $rs['nome'],
            $rs['username'],
            $rs['apelido'],
            $rs['password'] ?? null,
            (int) $rs['primeiroAcesso'] === 1,
            $rs['estadoCivil'] ?? null,
            $rs['genero'] ?? null,
            $rs['telefone'] ?? null,
            $rs['email'] ?? null,
            $rs['bi'] ?? null
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
        $sql = "insert into Usuario (nome, username, password, apelido, idPerfil, primeiroAcesso,
                                     estadoCivil, genero, telefone, email, BI)
                values (?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        $nome = $usuario->getNome();
        $username = $usuario->getUsername();
        $password = $usuario->getPassword();
        $apelido = $usuario->getApelido();
        $idPerfil = $usuario->getPerfil()->getId();
        $estadoCivil = $usuario->getEstadoCivil();
        $genero = $usuario->getGenero();
        $telefone = $usuario->getTelefone();
        $email = $usuario->getEmail();
        $bi = $usuario->getBi();

        $stmt->bind_param("ssssisssss", $nome, $username, $password, $apelido, $idPerfil,
                          $estadoCivil, $genero, $telefone, $email, $bi);

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

    public function getAll($pesquisa = null) {
        $sql = "select Usuario.idUser, Usuario.idPerfil, Usuario.nome, Usuario.username,
                       Usuario.apelido, Usuario.primeiroAcesso, Usuario.estadoCivil,
                       Usuario.genero, Usuario.telefone, Usuario.email, Usuario.BI,
                       Perfil.nome as nome_perfil
                from Usuario
                join Perfil on Usuario.idPerfil = Perfil.id";

        if ($pesquisa !== null && strlen($pesquisa) > 0) {
            $sql .= " where Usuario.nome like ? or Usuario.apelido like ? or Usuario.username like ?
                            or Usuario.email like ? or Usuario.telefone like ? or Usuario.BI like ?
                            or Perfil.nome like ?
                      order by Usuario.nome";
            $stmt = $this->db->prepare($sql);
            $busca = "%" . $pesquisa . "%";
            $stmt->bind_param("sssssss", $busca, $busca, $busca, $busca, $busca, $busca, $busca);
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
                       Usuario.estadoCivil, Usuario.genero, Usuario.telefone,
                       Usuario.email, Usuario.BI,
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
                       Usuario.estadoCivil, Usuario.genero, Usuario.telefone,
                       Usuario.email, Usuario.BI,
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

        return $this->montarUsuario($rs);
    }

    // Quantos utilizadores estao associados a um perfil. Usado para impedir
    // remover um perfil ainda atribuido a alguem.
    public function contarPorPerfil($idPerfil) {
        $sql = "select count(*) as total from Usuario where idPerfil = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idPerfil);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs !== null ? (int) $rs['total'] : 0;
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
                       estadoCivil = ?, genero = ?, telefone = ?, email = ?, BI = ?,
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
        $idPerfil = (int) $usuario->getIdPerfil();
        $estadoCivil = $usuario->getEstadoCivil();
        $genero = $usuario->getGenero();
        $telefone = $usuario->getTelefone();
        $email = $usuario->getEmail();
        $bi = $usuario->getBi();
        $primeiroAcesso = (int) $usuario->getPrimeiroAcesso();
        $codigo = $usuario->getCodigo();

        $stmt->bind_param("sssissssssii", $nome, $username, $apelido, $idPerfil,
                          $estadoCivil, $genero, $telefone, $email, $bi,
                          $password, $primeiroAcesso, $codigo);

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
