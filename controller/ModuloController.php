<?php
require_once __DIR__ . '/../Dao/ModuloDao.php';
require_once __DIR__ . '/../model/Modulo.php';
require_once __DIR__ . '/Quali_NivelController.php';
require_once __DIR__ . '/Quali_moduloController.php';
require_once __DIR__ . '/LicaoController.php';
require_once __DIR__ . '/InscricaoController.php';

class ModuloController {
    private $dao;

    public function __construct() {
        $this->dao = new ModuloDao();
    }

    public function cadastrarModulo($nome, $carga_horaria, $semestre, $qualificacao, $nivel) {
        if ($nome != null && strlen($nome) > 0 && preg_match('/^[a-zA-Z0-9 ]+$/', $nome)
            && $carga_horaria > 0 && $qualificacao != null && $nivel != null) {

            $modulo = new Modulo(null, $nome, $carga_horaria);

            if ($qualificacao != null && $nivel != null) {
                $qualiNivelController = new Quali_NivelController();
                $codigoQualiNivel = $qualiNivelController->buscarCodigo($qualificacao, $nivel);

                if ($codigoQualiNivel > 0) {
                    $modulo->setQualiNivelCodigo($codigoQualiNivel);
                    $this->dao->create($modulo);

                    if ($modulo->getCodigo() > 0) {
                        $qualiModuloController = new Quali_moduloController();
                        $sucesso = $qualiModuloController->cadastrarQuali_modulo($semestre, $modulo, $qualificacao);
                        if ($sucesso) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    public function listarModulo($nome) {
        return $this->dao->getAll($nome);
    }

    public function atualizarModulo($nome, $carga_horaria, $codigo, $qualificacao, $nivel, $semestre) {
        if ($semestre != null && strlen($semestre) > 0 && $qualificacao != null && $nivel != null
            && $nome != null && strlen($nome) > 0 && preg_match('/^[a-zA-Z0-9 ]+$/', $nome)
            && $codigo != 0 && $carga_horaria > 0) {

            $modulo = new Modulo($codigo, $nome, $carga_horaria);

            if ($qualificacao != null && $nivel != null) {
                $qualiNivelController = new Quali_NivelController();
                $codigoQualiNivel = $qualiNivelController->buscarCodigo($qualificacao, $nivel);

                if ($codigoQualiNivel > 0) {
                    $modulo->setQualiNivelCodigo($codigoQualiNivel);
                    $this->dao->update($modulo);

                    $qualiModuloController = new Quali_moduloController();
                    $qualiModuloController->atualizarQuali_modulo($codigo, $semestre, $modulo, $qualificacao);

                    return true;
                }
            }
        }
        return false;
    }

    public function apagarModulo($codigo) {
        if ($codigo != 0) {
            $licaoController = new LicaoController();
            if ($licaoController->existeLicaoPorModulo($codigo)) {
                throw new Exception("Não é possível eliminar este módulo pois está sendo usado em uma ou mais lições.");
            }

            $inscricaoController = new InscricaoController();
            if ($inscricaoController->existeInscricaoPorModulo($codigo)) {
                throw new Exception("Não é possível eliminar este módulo pois está sendo usado em uma ou mais inscrições.");
            }

            $qualiModuloController = new Quali_moduloController();
            $sucesso = $qualiModuloController->apagarQuali_modulo($codigo);

            if ($sucesso) {
                $this->dao->delete($codigo);
            }

            return true;
        }
        return false;
    }
}
?>
