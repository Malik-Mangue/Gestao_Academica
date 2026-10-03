<?php
require_once __DIR__ . '/../Dao/FormadorDao.php';
require_once __DIR__ . '/../model/Formador.php';
require_once __DIR__ . '/Diretor_turmaController.php';
require_once __DIR__ . '/CoordenadorController.php';
require_once __DIR__ . '/LicaoController.php';
require_once __DIR__ . '/TurmaController.php';
require_once __DIR__ . '/../services/Validador.php';

class FormadorController {
    private $dao;

    public function __construct() {
        $this->dao = new FormadorDao();
    }

    public function cadastrarFormador($nome, $apelido, $email, $genero, $estadoCivil, $contacto, $valor_horas, $horas_mes, $salario, $isDiretor, $isCoordenador) {
        if (Validador::texto($nome, 40) && Validador::texto($apelido, 40)
            && Validador::emailObrigatorio($email)
            && Validador::inteiroPositivo($contacto)
            && Validador::inteiroPositivo($valor_horas)
            && Validador::inteiroPositivo($horas_mes)) {

            $formador = new Formador(null, $nome, $apelido, $email, $genero, $estadoCivil, $contacto, $valor_horas, $horas_mes, $salario);
            $this->dao->create($formador);

            if ($isDiretor) {
                $diretorTurmaController = new Diretor_TurmaController();
                $diretorTurmaController->cadastrarDiretor_Turma($formador);
            } elseif ($isCoordenador) {
                $coordenadorController = new CoordenadorController();
                $coordenadorController->cadastrarCoordenador($formador);
            }

            return true;
        }
        return false;
    }

    public function listar() {
        return $this->dao->getAll();
    }

    public function atualizarFormador($codigo, $nome, $apelido, $email, $genero, $estadoCivil, $contacto, $valor_horas, $horas_mes, $salario, $isDiretor, $isCoordenador) {
        if ($codigo > 0 && Validador::texto($nome, 40) && Validador::texto($apelido, 40)
            && Validador::emailObrigatorio($email)
            && Validador::inteiroPositivo($contacto)
            && Validador::inteiroPositivo($valor_horas)
            && Validador::inteiroPositivo($horas_mes)) {

            $formador = new Formador($codigo, $nome, $apelido, $email, $genero, $estadoCivil, $contacto, $valor_horas, $horas_mes, $salario);
            $this->dao->update($formador);

            $diretorTurmaController = new Diretor_TurmaController();
            $isDiretorNow = $diretorTurmaController->isDiretor($codigo);

            if ($isDiretor && !$isDiretorNow) {
                $diretorTurmaController->cadastrarDiretor_Turma($formador);
            } elseif (!$isDiretor && $isDiretorNow) {
                $diretorTurmaController->apagarDiretor($codigo);
            }

            $coordenadorController = new CoordenadorController();
            $isCoordenadorNow = $coordenadorController->isCoordenador($codigo);

            if ($isCoordenador && !$isCoordenadorNow) {
                $coordenadorController->cadastrarCoordenador($formador);
            } elseif (!$isCoordenador && $isCoordenadorNow) {
                $coordenadorController->apagarCoordenador($codigo);
            }

            return true;
        }
        return false;
    }

    public function apagarFormador($codigo) {
        if ($codigo != 0) {

            $licaoController = new LicaoController();
            if ($licaoController->existeLicaoPorFormador($codigo)) {
                throw new Exception("Não é possível eliminar este formador pois está sendo usado em uma ou mais lições.");
            }


            $coordenadorController = new CoordenadorController();
            if ($coordenadorController->existeCoordenadorEmQualificacao($codigo)) {
                throw new Exception("Não é possível eliminar este formador pois é coordenador de uma ou mais qualificações.");
            }


            $turmaController = new TurmaController();
            if ($turmaController->existeTurmaComDiretor($codigo)) {
                throw new Exception("Não é possível eliminar este formador pois é diretor de uma ou mais turmas.");
            }

            $coordenadorController = new CoordenadorController();
            $diretorTurmaController = new Diretor_TurmaController();

            $coordenadorController->apagarCoordenador($codigo);
            $diretorTurmaController->apagarDiretor($codigo);

            $this->dao->delete($codigo);
            return true;
        }
        return false;
    }

    public function getStatusFormador($codigo) {
        $coordenadorController = new CoordenadorController();
        $diretorTurmaController = new Diretor_TurmaController();

        $status = [];
        $status[0] = $diretorTurmaController->isDiretor($codigo);
        $status[1] = $coordenadorController->isCoordenador($codigo);
        return $status;
    }
}
?>
