<?php
require_once __DIR__ . '/../Dao/Quali_moduloDao.php';
require_once __DIR__ . '/../model/Quali_modulo.php';

class Quali_moduloController {
    private $dao;

    public function __construct() {
        $this->dao = new Quali_moduloDao();
    }

    public function cadastrarQuali_modulo($semestre, $modulo, $qualificacao) {
        if ($semestre != null && strlen($semestre) > 0 && $modulo != null && $qualificacao != null) {
            $qualiModulo = new Quali_modulo(null, $semestre, $modulo, $qualificacao);
            $this->dao->create($qualiModulo);
            return true;
        }
        return false;
    }

    public function listarQuali_modulo($semestre) {
        return $this->dao->getAll($semestre);
    }

    public function atualizarQuali_modulo($codigo, $semestre, $modulo, $qualificacao) {
        if ($semestre != null && strlen($semestre) > 0 && $codigo != 0 && $qualificacao != null) {
            $qualiModulo = new Quali_modulo($codigo, $semestre, $modulo, $qualificacao);
            return $this->dao->updateByModulo($qualiModulo);
        }
        return false;
    }

    public function apagarQuali_modulo($codigo) {
        if ($codigo != 0) {
            return $this->dao->deleteByModulo($codigo);
        }
        return false;
    }
}
?>
