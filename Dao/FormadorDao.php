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
        $sql = "insert into Formador (nome, apelido, email, genero, estadoCivil, contacto, valor_hora, horas_mes, salario) values (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        $nome = $formador->getNome();
        $apelido = $formador->getApelido();
        $email = $formador->getEmail();
        $genero = $formador->getGenero();
        $estadoCivil = $formador->getEstadoCivil();
        $contacto = $formador->getContacto();
        $valorHoras = $formador->getValorHoras();
        $horasMes = $formador->getHorasMes();
        $salario = $formador->getSalario();

        $stmt->bind_param("sssssiiid", $nome, $apelido, $email, $genero, $estadoCivil, $contacto, $valorHoras, $horasMes, $salario);
        return $stmt->execute();

    }

    public function getAll() {
        $sql = "select codigo, nome, apelido, email, genero, estadoCivil, contacto,
                       valor_hora, horas_mes, salario
                from Formador order by nome";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        $formadores = [];
        while ($rs = $result->fetch_assoc()) {
            $formadores[] = $this->montarFormador($rs);
        }
        return $formadores;
    }

    public function getById($codigo) {
        $sql = "select codigo, nome, apelido, email, genero, estadoCivil, contacto,
                       valor_hora, horas_mes, salario
                from Formador where codigo = ?";
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
        $sql = "update Formador set nome = ?, apelido = ?, email = ?, genero = ?, estadoCivil = ?, contacto = ?, valor_hora = ?, horas_mes = ?, salario = ? where codigo = ?";
        $stmt = $this->db->prepare($sql);

        $nome = $formador->getNome();
        $apelido = $formador->getApelido();
        $email = $formador->getEmail();
        $genero = $formador->getGenero();
        $estadoCivil = $formador->getEstadoCivil();
        $contacto = $formador->getContacto();
        $valorHoras = $formador->getValorHoras();
        $horasMes = $formador->getHorasMes();
        $salario = $formador->getSalario();
        $codigo = $formador->getCodigo();

        $stmt->bind_param("sssssiiidi", $nome, $apelido, $email, $genero, $estadoCivil, $contacto, $valorHoras, $horasMes, $salario, $codigo);
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
            $rs['valor_hora'],
            $rs['horas_mes'],
            $rs['salario']
        );
    }
}
?>
