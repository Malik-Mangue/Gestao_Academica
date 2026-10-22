<?php
require_once __DIR__ . '/../Dao/TurmaDao.php';
require_once __DIR__ . '/../model/Turma.php';
require_once __DIR__ . '/../model/Quali_Nivel.php';
require_once __DIR__ . '/Validacao.php';
require_once __DIR__ . '/Quali_NivelController.php';
require_once __DIR__ . '/Diretor_turmaController.php';
require_once __DIR__ . '/LicaoController.php';

class TurmaController {
    use Validacao;

    private $dao;

    public function __construct() {
        $this->dao = new TurmaDao();
    }

    public function cadastrarTurma($nome, $ano_ingresso, $turno, $diretorTurma, $qualificacao, $nivel, $qualiNivel = null, $capacidade_maxima = 30) {
        if (!$this->texto($nome, 40) || !$this->inteiroPositivo($ano_ingresso)
            || $turno === null || $turno === '' || $diretorTurma === null
            || !$this->inteiroPositivo($capacidade_maxima)) {
            return false;
        }

        if ($qualiNivel === null) {
            if ($qualificacao === null || $nivel === null) {
                return false;
            }
            $codigo = (new Quali_NivelController())->buscarCodigo($qualificacao, $nivel);
            if (!$codigo) {
                return false;
            }
            $qualiNivel = new Quali_Nivel($codigo, null, null);
        }

        $turma = new Turma(null, $nome, (int) $ano_ingresso, $turno, $diretorTurma, $qualificacao, $qualiNivel, (int) $capacidade_maxima);

        return (bool) $this->dao->create($turma);
    }

    public function listarTurma($nome = '', $codCampo = null) {
        return $this->dao->getAll($nome ?? '', $codCampo);
    }

    public function buscarTurma($codigo) {
        return $codigo ? $this->dao->getById($codigo) : null;
    }

    public function listarDiretores($codCampo = null) {
        return $this->dao->getDiretores($codCampo);
    }

    public function listarQualificacoes() {
        return $this->dao->getQualificacoes();
    }

    public function listarNiveis() {
        return $this->dao->getNiveis();
    }

    public function listarParesQualiNivel($codCampo = null) {
        return $this->dao->getParesQualiNivel($codCampo);
    }

    public function atualizarTurma($codigo, $nome, $ano_ingresso, $turno, $diretorTurma, $qualificacao, $nivel, $qualiNivel = null, $capacidade_maxima = 30) {
        if (!$codigo || !$this->texto($nome, 40) || !$this->inteiroPositivo($ano_ingresso)
            || $turno === null || $turno === '' || $diretorTurma === null
            || !$this->inteiroPositivo($capacidade_maxima)) {
            return false;
        }

        if ($qualiNivel === null) {
            if ($qualificacao === null || $nivel === null) {
                return false;
            }
            $codigoQualiNivel = (new Quali_NivelController())->buscarCodigo($qualificacao, $nivel);
            if (!$codigoQualiNivel) {
                return false;
            }
            $qualiNivel = new Quali_Nivel($codigoQualiNivel, null, null);
        }

        $turma = new Turma($codigo, $nome, (int) $ano_ingresso, $turno, $diretorTurma, $qualificacao, $qualiNivel, (int) $capacidade_maxima);

        return (bool) $this->dao->update($turma);
    }

    public function apagarTurma($codigo) {
        if ($codigo) {
            if ((new LicaoController())->existeLicaoPorTurma($codigo)) {
                throw new Exception("Não é possível eliminar esta turma pois está sendo usada em uma ou mais lições.");
            }

            return (bool) $this->dao->delete($codigo);
        }
        return false;
    }

    public function existeTurmaComDiretor($codigoFormador) {
        return $this->dao->existeTurmaComDiretor($codigoFormador);
    }

    public function getByQualiNivel($codigoQualiNivel) {
        return $this->dao->getByQualiNivel($codigoQualiNivel);
    }
}
?>
