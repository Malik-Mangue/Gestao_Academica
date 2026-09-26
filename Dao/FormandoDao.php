<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../model/Formando.php';

class FormandoDAO {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAll() {
        $sql = "select codigo_formando, nome_formando, apelido_formando, contacto_formando, email, BI from Formando order by nome_formando, apelido_formando";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $formandos = [];
        while ($rs = $result->fetch_assoc()) {
            $formandos[] = new Formando($rs['codigo_formando'], $rs['nome_formando'], $rs['apelido_formando'], $rs['contacto_formando'], $rs['email'], $rs['BI']);
        }
        return $formandos;
    }

    public function getById($codigo) {
        $sql = "select codigo_formando, nome_formando, apelido_formando, contacto_formando, email, BI from Formando where codigo_formando = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        if ($rs === null) {
            return null;
        }
        return new Formando($rs['codigo_formando'], $rs['nome_formando'], $rs['apelido_formando'], $rs['contacto_formando'], $rs['email'], $rs['BI']);
    }

    public function create(Formando $formando) {
        $sql = "insert into Formando (nome_formando, apelido_formando, contacto_formando, email, BI) values (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $nome = $formando->getNome();
        $apelido = $formando->getApelido();
        $contacto = $formando->getContacto();
        $email = $formando->getEmail();
        $bi = $formando->getBi();
        $stmt->bind_param("ssiss", $nome, $apelido, $contacto, $email, $bi);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function update(Formando $formando) {
        $sql = "update Formando set nome_formando = ?, apelido_formando = ?, contacto_formando = ?, email = ?, BI = ? where codigo_formando = ?";
        $stmt = $this->db->prepare($sql);
        $nome = $formando->getNome();
        $apelido = $formando->getApelido();
        $contacto = $formando->getContacto();
        $email = $formando->getEmail();
        $bi = $formando->getBi();
        $codigo = $formando->getCodigo();
        $stmt->bind_param("ssissi", $nome, $apelido, $contacto, $email, $bi, $codigo);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function delete($codigo) {
        $sql = "delete from Formando where codigo_formando = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
}
?>
