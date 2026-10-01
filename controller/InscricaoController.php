<?php
require_once __DIR__ . '/../Dao/InscricaoDao.php';
require_once __DIR__ . '/../model/Inscricao.php';

class InscricaoController {
    private $dao;

    public function __construct() {
        $this->dao = new InscricaoDao();
    }

    public function cadastrarInscricao($formando, $modulo, $semestre, $data_inscricao) {
        if ($formando != null && $modulo != null && $semestre != null && strlen($semestre) > 0
            && $data_inscricao != null && strlen($data_inscricao) > 0) {

            $inscricao = new Inscricao(null, $formando, $modulo, $semestre, $data_inscricao);
            $this->dao->create($inscricao);
            return true;
        }
        return false;
    }

    public function listarInscricao($semestre) {
        return $this->dao->getAll($semestre);
    }

    public function atualizarInscricao($codigo, $formando, $modulo, $semestre, $data_inscricao) {
        if ($codigo != 0 && $formando != null && $modulo != null && $semestre != null && strlen($semestre) > 0
            && $data_inscricao != null && strlen($data_inscricao) > 0) {

            $inscricao = new Inscricao($codigo, $formando, $modulo, $semestre, $data_inscricao);
            $this->dao->update($inscricao);
            return true;
        }
        return false;
    }

    public function apagarInscricao($codigo) {
        if ($codigo != 0) {
            $this->dao->delete($codigo);
            return true;
        }
        return false;
    }

    public function existeInscricaoPorFormando($codigoFormando) {
        return $this->dao->existeInscricaoPorFormando($codigoFormando);
    }

    public function existeInscricaoPorModulo($codigoModulo) {
        return $this->dao->existeInscricaoPorModulo($codigoModulo);
    }
}
?>
