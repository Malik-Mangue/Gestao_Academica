<?php
require_once __DIR__ . '/../Dao/Diretor_TurmaDao.php';
require_once __DIR__ . '/../model/Diretor_turma.php';

class Diretor_TurmaController {
    private $dao;

    public function __construct() {
        $this->dao = new Diretor_TurmaDao();
    }

    public function salvarDiretor($formador, $codigoFormador) {
        if ($this->isDiretor($codigoFormador)) {
            $this->atualizarDiretor_Turma($formador, $codigoFormador);
        } else {
            $this->cadastrarDiretor_Turma($formador);
        }
    }

    public function cadastrarDiretor_Turma($formador) {
        if ($formador != null) {
            $diretorTurma = new Diretor_turma(null, $formador);
            $this->dao->create($diretorTurma);
            return true;
        }
        return false;
    }

    public function comboDiretor_Turma() {
        return $this->dao->getAll();
    }

    public function atualizarDiretor_Turma($formador, $codigo) {
        if ($formador != null) {
            $diretorTurma = new Diretor_turma($codigo, $formador);
            $this->dao->update($diretorTurma, $codigo);
            return true;
        }
        return false;
    }

    public function isDiretor($codigoFormador) {
        if ($codigoFormador > 0) {
            return $this->dao->isDiretor($codigoFormador);
        }
        return false;
    }

    public function apagarDiretor($codigoFormador) {
        if ($codigoFormador > 0) {
            $this->dao->apagarDiretor($codigoFormador);
            return true;
        }
        return false;
    }
}
?>
