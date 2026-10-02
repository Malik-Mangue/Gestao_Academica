<?php
require_once __DIR__ . '/../Dao/LicaoDao.php';
require_once __DIR__ . '/../model/Licao.php';

class LicaoController {
    private $dao;

    public function __construct() {
        $this->dao = new LicaoDao();
    }

    public function cadastrarLicao($modulo, $formador, $sala, $turma, $data, $hora_inicio, $hora_fim) {
        if ($modulo != null && $formador != null && $sala != null && $turma != null
            && $data != null && strlen($data) > 0
            && $hora_inicio != null && strlen($hora_inicio) > 0
            && $hora_fim != null && strlen($hora_fim) > 0) {

            $licao = new Licao(null, $modulo, $formador, $sala, $turma, $data, $hora_inicio, $hora_fim);
            $this->dao->create($licao);
            return true;
        }
        return false;
    }

    public function listarLicao($texto) {
        return $this->dao->getAll($texto);
    }

    public function apagarLicao($codigo) {
        if ($codigo != 0) {
            $this->dao->delete($codigo);
            return true;
        }
        return false;
    }

    public function existeLicaoPorModulo($codigoModulo) {
        return $this->dao->existeLicaoPorModulo($codigoModulo);
    }

    public function existeLicaoPorFormador($codigoFormador) {
        return $this->dao->existeLicaoPorFormador($codigoFormador);
    }

    public function existeLicaoPorSala($codigoSala) {
        return $this->dao->existeLicaoPorSala($codigoSala);
    }

    public function existeLicaoPorTurma($codigoTurma) {
        return $this->dao->existeLicaoPorTurma($codigoTurma);
    }

    public function existeLicaoPorQualificacao($codigoQualificacao) {
        return $this->dao->existeLicaoPorQualificacao($codigoQualificacao);
    }
}
?>
