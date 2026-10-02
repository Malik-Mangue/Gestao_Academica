<?php
require_once __DIR__ . '/../Dao/QualificacaoDao.php';
require_once __DIR__ . '/../model/Qualificacao.php';
require_once __DIR__ . '/ClassificacaoController.php';
require_once __DIR__ . '/Quali_NivelController.php';
require_once __DIR__ . '/LicaoController.php';
require_once __DIR__ . '/MatriculaController.php';
require_once __DIR__ . '/../services/Validador.php';

class QualificacaoController {
    private $dao;

    public function __construct() {
        $this->dao = new QualificacaoDao();
    }

    public function cadastrarQualificacao($titulo, $coordenador, $campo, $nivel) {
        if (Validador::texto($titulo, 60)
            && $coordenador != null && $campo != null) {

            $qualificacao = new Qualificacao(null, $titulo, $coordenador);
            $this->dao->create($qualificacao);

            if ($qualificacao->getCodigo() > 0) {
                $classificacaoController = new ClassificacaoController();
                $sucesso = $classificacaoController->cadastrarClassificacao($campo, $qualificacao);

                if ($sucesso) {
                    $qualiNivelController = new Quali_NivelController();
                    $qualiNivelController->cadastrarQuali_Nivel($nivel, $qualificacao);
                    return true;
                }
            }
        }
        return false;
    }

    public function comboQualificacao() {
        return $this->dao->getAll();
    }

    public function listarQualificacao($titulo) {
        return $this->dao->getAll($titulo);
    }

    public function buscar($codigo) {
        return $this->dao->getById($codigo);
    }

    // A atualizacao preserva o coordenador ja associado: sem o parametro
    // opcional, o titulo e alterado sem perder a chave estrangeira.
    public function atualizarQualificacao($codigo, $titulo, $coordenador = null) {
        if ($codigo != 0 && Validador::texto($titulo, 60)) {
            $atual = $this->dao->getById($codigo);
            if ($atual === null) {
                return false;
            }
            $codigoCoordenador = $coordenador !== null ? $coordenador : $atual->getCod_coordenador();

            $qualificacao = new Qualificacao($codigo, $titulo, $codigoCoordenador);
            return $this->dao->update($qualificacao);
        }
        return false;
    }

    public function apagarQualificacao($codigo) {
        if ($codigo != 0) {
            $licaoController = new LicaoController();
            if ($licaoController->existeLicaoPorQualificacao($codigo)) {
                throw new Exception("Não é possível eliminar esta qualificação pois está sendo usada em uma ou mais lições.");
            }

            $matriculaController = new MatriculaController();
            if ($matriculaController->existeMatriculaPorQualificacao($codigo)) {
                throw new Exception("Não é possível eliminar esta qualificação pois está sendo usada em uma ou mais matrículas.");
            }

            if ($this->existeQualificacaoEmQualiModulo($codigo)) {
                throw new Exception("Não é possível eliminar esta qualificação pois está associada a um ou mais módulos.");
            }

            $this->dao->delete($codigo);
            return true;
        }
        return false;
    }

    public function existeQualificacaoEmClassificacao($codigoQualificacao) {
        return $this->dao->existeQualificacaoEmClassificacao($codigoQualificacao);
    }

    public function existeQualificacaoEmQualiNivel($codigoQualificacao) {
        return $this->dao->existeQualificacaoEmQualiNivel($codigoQualificacao);
    }

    public function existeQualificacaoEmQualiModulo($codigoQualificacao) {
        return $this->dao->existeQualificacaoEmQualiModulo($codigoQualificacao);
    }
}
?>
