<?php
require_once __DIR__ . '/../Dao/LicaoDao.php';
require_once __DIR__ . '/../model/Licao.php';
require_once __DIR__ . '/../services/Validador.php';

class LicaoController {
    private $dao;

    public function __construct() {
        $this->dao = new LicaoDao();
    }

    // Constroi a lição a partir de códigos validados e persiste-a.
    public function cadastrarLicao($modulo, $formador, $sala, $turma, $data, $hora_inicio, $hora_fim) {
        if (!$this->dadosValidos($modulo, $formador, $sala, $turma, $data, $hora_inicio, $hora_fim)) {
            return false;
        }

        $licao = new Licao(null, $modulo, $formador, $sala, $turma, $data, $hora_inicio, $hora_fim);

        return (bool) $this->dao->create($licao);
    }

    public function atualizarLicao($codigo, $modulo, $formador, $sala, $turma, $data, $hora_inicio, $hora_fim) {
        if (!$codigo || !$this->dadosValidos($modulo, $formador, $sala, $turma, $data, $hora_inicio, $hora_fim)) {
            return false;
        }

        $licao = new Licao($codigo, $modulo, $formador, $sala, $turma, $data, $hora_inicio, $hora_fim);

        return (bool) $this->dao->update($licao);
    }

    private function dadosValidos($modulo, $formador, $sala, $turma, $data, $hora_inicio, $hora_fim) {
        return $modulo !== null && $formador !== null && $sala !== null && $turma !== null
            && Validador::data($data)
            && Validador::hora($hora_inicio)
            && Validador::hora($hora_fim)
            // A hora de fim tem de ser posterior a hora de inicio.
            && $hora_fim > $hora_inicio;
    }

    public function listarLicao($texto = '') {
        return $this->dao->getAll($texto ?? '');
    }

    public function listarModulos() {
        return $this->dao->getModulos();
    }

    public function listarFormadores() {
        return $this->dao->getFormadores();
    }

    public function listarSalas() {
        return $this->dao->getSalas();
    }

    public function listarTurmas() {
        return $this->dao->getTurmas();
    }

    public function existeConflito($codSala, $codFormador, $data, $horaInicio, $horaFim, $codigoIgnorado = null) {
        return $this->dao->existeConflito($codSala, $codFormador, $data, $horaInicio, $horaFim, $codigoIgnorado);
    }

    public function apagarLicao($codigo) {
        if ($codigo) {
            return (bool) $this->dao->delete($codigo);
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
