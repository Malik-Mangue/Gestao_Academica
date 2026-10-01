<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../model/Usuario.php';

class UsuarioDAO {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAll() {
        $sql = "select idUser, idPerfil, nome, username, apelido, password, primeiroAcesso from Usuario order by idUser";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuarios = [];
        while ($rs = $result->fetch_assoc()) {
            $usuarios[] = new Usuario($rs['idUser'], $rs['idPerfil'], $rs['nome'], $rs['username'], $rs['apelido'], $rs['password'], $rs['primeiroAcesso']);
        }
        return $usuarios;
    }

    public function getById($codigo) {
        $sql = "select idUser, idPerfil, nome, username, apelido, password, primeiroAcesso from Usuario where idUser = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        if ($rs === null) {
            return null;
        }
        return new Usuario($rs['idUser'], $rs['idPerfil'], $rs['nome'], $rs['username'], $rs['apelido'], $rs['password'], $rs['primeiroAcesso']);
    }

    public function create(Usuario $usuario) {
        $sql = "insert into Usuario (idPerfil, nome, username, apelido, password, primeiroAcesso) values (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $idPerfil = $usuario->getIdPerfil();
        $nome = $usuario->getNome();
        $username = $usuario->getUsername();
        $apelido = $usuario->getApelido();
        $password = $usuario->getPassword();
        $primeiroAcesso = $usuario->getPrimeiroAcesso();
        $stmt->bind_param("issssi", $idPerfil, $nome, $username, $apelido, $password, $primeiroAcesso);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function update(Usuario $usuario) {
        $sql = "update Usuario set idPerfil = ?, nome = ?, username = ?, apelido = ?, password = ?, primeiroAcesso = ? where idUser = ?";
        $stmt = $this->db->prepare($sql);
        $idPerfil = $usuario->getIdPerfil();
        $nome = $usuario->getNome();
        $username = $usuario->getUsername();
        $apelido = $usuario->getApelido();
        $password = $usuario->getPassword();
        $primeiroAcesso = $usuario->getPrimeiroAcesso();
        $codigo = $usuario->getCodigo();
        $stmt->bind_param("issssii", $idPerfil, $nome, $username, $apelido, $password, $primeiroAcesso, $codigo);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function delete($codigo) {
        $sql = "delete from Usuario where idUser = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    private function listaOpcoes($sql) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $opcoes = [];
        while ($rs = $result->fetch_assoc()) {
            $opcoes[] = ['codigo' => $rs['codigo'], 'descricao' => $rs['descricao']];
        }
        return $opcoes;
    }

    public function getPerfis() {
        return $this->listaOpcoes("select id as codigo, nome as descricao from Perfil order by id");
    }
}
?>
