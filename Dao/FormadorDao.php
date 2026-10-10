<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../services/Sessao.php';
require_once __DIR__ . '/../model/Formador.php';
require_once __DIR__ . '/../model/Logs.php';
require_once __DIR__ . '/LogDao.php';

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
        $result = $stmt->execute();
        if ($result) {
            $this->registarLog("INSERT", "Formador " . $nome . " " . $apelido . " foi cadastrado");
        }
        return $result;

    }

    public function getAll($pesquisa = null) {
        $sql = "select codigo, nome, apelido, email, genero, estadoCivil, contacto,
                       valor_hora, horas_mes, salario
                from Formador";
        if ($pesquisa !== null && strlen($pesquisa) > 0) {
            $sql .= " where nome like ? or apelido like ? or email like ? or genero like ?
                            or estadoCivil like ? or contacto like ?
                      order by nome";
            $stmt = $this->db->prepare($sql);
            $busca = "%" . $pesquisa . "%";
            $stmt->bind_param("ssssss", $busca, $busca, $busca, $busca, $busca, $busca);
        } else {
            $sql .= " order by nome";
            $stmt = $this->db->prepare($sql);
        }
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
        $result = $stmt->execute();
        if ($result) {
            $this->registarLog("UPDATE", "Formador " . $nome . " " . $apelido . " (ID: " . $codigo . ") foi atualizado");
        }
        return $result;
    }

    public function delete($codigo) {
        $sql = "delete from Formador where codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $result = $stmt->execute();
        if ($result) {
            $this->registarLog("DELETE", "Formador (ID: " . $codigo . ") foi removido");
        }
        return $result;
    }

    // Regista uma acao de auditoria em nome do utilizador autenticado.
    private function registarLog($acao, $descricao) {
        $usuario = Sessao::obterUtilizador();
        if ($usuario != null) {
            $log = new Logs(null, $acao, $descricao, $usuario);
            $log->setData(date('d/m/Y H:i:s'));
            (new LogDao())->salvar($log);
        }
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
