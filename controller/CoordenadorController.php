<?php
require_once __DIR__ . '/../Dao/CoordenadorDao.php';
require_once __DIR__ . '/../model/Coordenador.php';

class CoordenadorController {
    private $dao;

    public function __construct() {
        $this->dao = new CoordenadorDao();
    }

    public function salvarCoordenador($formador, $codigoFormador) {
        if ($this->isCoordenador($codigoFormador)) {
            $this->atualizarCoordenador($formador, $codigoFormador);
        } else {
            $this->cadastrarCoordenador($formador);
        }
    }

    public function cadastrarCoordenador($formador) {
        if ($formador != null && $formador->getCodigo() != 0) {
            $coordenador = new Coordenador($formador);
            $this->dao->create($coordenador);
            return true;
        }
        return false;
    }

    public function listarCoordenador() {
        return $this->dao->getAll();
    }

    public function isCoordenador($codigoFormador) {
        if ($codigoFormador > 0) {
            return $this->dao->isCoordenador($codigoFormador);
        }
        return false;
    }

    public function atualizarCoordenador($formador, $codigoFormador) {
        if ($formador != null && $codigoFormador > 0) {
            $coordenador = new Coordenador(null, $formador);
            $this->dao->update($coordenador, $codigoFormador);
        }
    }

    public function apagarCoordenador($codigo) {
        if ($codigo != 0) {
            $this->dao->delete($codigo);
            return true;
        }
        return false;
    }

    public function existeCoordenadorEmQualificacao($codigoFormador) {
        return $this->dao->existeCoordenadorEmQualificacao($codigoFormador);
    }
}
?>
