<?php
require_once __DIR__ . '/../Dao/TurmaDao.php';
require_once __DIR__ . '/../model/Turma.php';
require_once __DIR__ . '/Quali_NivelController.php';
require_once __DIR__ . '/Diretor_turmaController.php';
require_once __DIR__ . '/LicaoController.php';

class TurmaController {
    private $dao;

    public function __construct() {
        $this->dao = new TurmaDao();
    }

    public function cadastrarTurma($nome, $ano_ingresso, $turno, $diretorTurma, $qualificacao, $nivel, $qualiNivel = null) {
        if (!Validador::texto($nome, 40) || !Validador::inteiroPositivo($ano_ingresso)
            || $turno === null || $turno === '' || $diretorTurma === null) {
            return false;
        }

        // A view escolhe o par Qualificacao/Nivel ja definido em Quali_Nivel.
        // Sem esse par, tenta resolver a partir da qualificacao e nivel.
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

        $turma = new Turma(null, $nome, (int) $ano_ingresso, $turno, $diretorTurma, $qualificacao, $qualiNivel);

        return (bool) $this->dao->create($turma);
    }

    public function listarTurma($nome = '') {
        return $this->dao->getAll($nome ?? '');
    }

    public function buscarTurma($codigo) {
        return $codigo ? $this->dao->getById($codigo) : null;
    }

    public function listarDiretores() {
        return $this->dao->getDiretores();
    }

    public function listarQualificacoes() {
        return $this->dao->getQualificacoes();
    }

    public function listarNiveis() {
        return $this->dao->getNiveis();
    }

    public function listarParesQualiNivel() {
        return $this->dao->getParesQualiNivel();
    }

    public function atualizarTurma($codigo, $nome, $ano_ingresso, $turno, $diretorTurma, $qualificacao, $nivel, $qualiNivel = null) {
        if (!$codigo || !Validador::texto($nome, 40) || !Validador::inteiroPositivo($ano_ingresso)
            || $turno === null || $turno === '' || $diretorTurma === null) {
            return false;
        }

        if ($qualiNivel === null) {
            if ($qualificacao === null || $nivel === null) {
                return false;
            }
            $qualiNivel = new Quali_Nivel(
                (new Quali_NivelController())->buscarCodigo($qualificacao, $nivel),
                null,
                null
            );
        }

        $turma = new Turma($codigo, $nome, (int) $ano_ingresso, $turno, $diretorTurma, $qualificacao, $qualiNivel);

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
}
?>
