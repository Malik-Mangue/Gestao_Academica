<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../model/Formador.php';

class FormadorDao {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function create(Formador $formador) {
        $sql = "insert into Formador (nome, apelido, email, genero, estadoCivil, contacto, valor_horas, horas_mes, salario) values (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        $nome = $formador->getNome();
        $apelido = $formador->getApelido();
        $email = $formador->getEmail();
        $genero = $formador->getGenero();
        $estadoCivil = $formador->getEstadoCivil();
        $contacto = $formador->getContacto();
        $valorHoras = $formador->getValor_Horas();
        $horasMes = $formador->getHoras_Mes();
        $salario = $formador->getSalario();

        $stmt->bind_param("sssssiiid", $nome, $apelido, $email, $genero, $estadoCivil, $contacto, $valorHoras, $horasMes, $salario);
        return $stmt->execute();
    }

    public function getAll($nome = null) {
        if ($nome != null && strlen($nome) > 0) {
            $sql = "select * from Formador where nome like ?";
            $stmt = $this->db->prepare($sql);
            $busca = "%" . $nome . "%";
            $stmt->bind_param("s", $busca);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $sql = "select * from Formador";
            $result = mysqli_query($this->db, $sql);
        }

        $formadores = [];
        while ($rs = mysqli_fetch_assoc($result)) {
            $formadores[] = $this->montarFormador($rs);
        }
        return $formadores;
    }

    public function getById($codigo) {
        $sql = "select * from Formador where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();

        if ($rs == null) {
            return null;
        }
        return $this->montarFormador($rs);
    }

    public function update(Formador $formador) {
        $sql = "update Formador set nome = ?, apelido = ?, email = ?, genero = ?, estadoCivil = ?, contacto = ?, valor_horas = ?, horas_mes = ?, salario = ? where codigo = ?";
        $stmt = $this->db->prepare($sql);

        $nome = $formador->getNome();
        $apelido = $formador->getApelido();
        $email = $formador->getEmail();
        $genero = $formador->getGenero();
        $estadoCivil = $formador->getEstadoCivil();
        $contacto = $formador->getContacto();
        $valorHoras = $formador->getValor_Horas();
        $horasMes = $formador->getHoras_Mes();
        $salario = $formador->getSalario();
        $codigo = $formador->getCodigo();

        $stmt->bind_param("sssssiidi", $nome, $apelido, $email, $genero, $estadoCivil, $contacto, $valorHoras, $horasMes, $salario, $codigo);
        return $stmt->execute();
    }

    public function delete($codigo) {
        $sql = "delete from Formador where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        return $stmt->execute();
    }

    private function montarFormador($rs) {
        return new Formador(
            $rs['codigo'],
            $rs['nome'],
            $rs['apelido'],
            $rs['email'],
            $rs['genero'],
            $rs['estadoCivil'],
            $rs['contacto'],
            $rs['valor_horas'],
            $rs['horas_mes'],
            $rs['salario']
        );
    }
}
?>
