<?php
require_once __DIR__ . '/../Dao/TurmaDao.php';
require_once __DIR__ . '/../model/Turma.php';
require_once __DIR__ . '/Quali_NivelController.php';
require_once __DIR__ . '/Diretor_TurmaController.php';
require_once __DIR__ . '/LicaoController.php';

class TurmaController {
    private $dao;

    public function __construct() {
        $this->dao = new TurmaDao();
    }

    public function cadastrarTurma($nome, $ano_ingresso, $turno, $diretorTurma, $qualificacao, $nivel) {
        if ($nome != null && strlen($nome) > 0 && $ano_ingresso > 0 && $turno != null && strlen($turno) > 0 && $diretorTurma != null) {

            if ($qualificacao != null && $nivel != null) {
                $qualiNivelController = new Quali_NivelController();
                $codigo = $qualiNivelController->buscarCodigo($qualificacao, $nivel);

                if ($codigo > 0) {
                    $qualiNivel = new Quali_Nivel($codigo, null, null);

                    $turma = new Turma(null, $nome, $ano_ingresso, $turno, $diretorTurma, $qualificacao, $qualiNivel);
                    $this->dao->create($turma);
                    return true;
                }
            }
        }
        return false;
    }

    public function listarTurma($nome) {
        return $this->dao->getAll($nome);
    }

    public function atualizarTurma($codigo, $nome, $ano_ingresso, $turno, $diretorTurma, $qualificacao, $nivel) {
        if ($nome != null && strlen($nome) > 0 && $codigo != 0 && $ano_ingresso > 0 && $turno != null && strlen($turno) > 0) {

            if ($qualificacao != null && $nivel != null) {
                $qualiNivelController = new Quali_NivelController();
                $codigoQualiNivel = $qualiNivelController->buscarCodigo($qualificacao, $nivel);

                if ($codigoQualiNivel > 0) {
                    $qualiNivel = new Quali_Nivel($codigoQualiNivel, null, null);

                    $turma = new Turma($codigo, $nome, $ano_ingresso, $turno, $diretorTurma, $qualificacao, $qualiNivel);
                    $this->dao->update($turma);
                    return true;
                }
            }
        }
        return false;
    }

    public function apagarTurma($codigo) {
        if ($codigo != 0) {
            $licaoController = new LicaoController();
            if ($licaoController->existeLicaoPorTurma($codigo)) {
                throw new Exception("Não é possível eliminar esta turma pois está sendo usada em uma ou mais lições.");
            }

            $this->dao->delete($codigo);
            return true;
        }
        return false;
    }

    public function existeTurmaComDiretor($codigoFormador) {
        return $this->dao->existeTurmaComDiretor($codigoFormador);
    }
}
?>
