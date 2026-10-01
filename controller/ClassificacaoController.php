<?php
require_once __DIR__ . '/../Dao/ClassificacaoDao.php';
require_once __DIR__ . '/../model/Classificacao.php';

class ClassificacaoController {
    private $dao;

    public function __construct() {
        $this->dao = new ClassificacaoDao();
    }

    public function cadastrarClassificacao($campo, $qualificacao) {
        if ($campo != null && $qualificacao != null) {
            $classificacao = new Classificacao(null, $campo, $qualificacao);
            $this->dao->create($classificacao);
            return true;
        }
        return false;
    }

    public function listarClassificacao($campo) {
        return $this->dao->getAll($campo);
    }

    public function atualizarClassificacao($codigo, $campo) {
        if ($codigo != 0 && $campo != null) {
            $classificacao = new Classificacao($codigo, $campo, null);
            $this->dao->update($classificacao);
            return true;
        }
        return false;
    }

    public function apagarClassificacao($codigo) {
        if ($codigo != 0) {
            $this->dao->delete($codigo);
            return true;
        }
        return false;
    }
}
?>
