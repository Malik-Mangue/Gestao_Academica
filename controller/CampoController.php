<?php
require_once __DIR__ . '/../Dao/CampoDao.php';
require_once __DIR__ . '/../model/Campo.php';

class CampoController {
    private $dao;

    public function __construct() {
        $this->dao = new CampoDAO();
    }

    public function cadastrarCampo($nome) {
        if ($nome != null && strlen($nome) > 0) {
            $campo = new Campo(null, $nome);
            $this->dao->create($campo);
            return true;
        }
        return false;
    }

    public function listarCampo() {
        return $this->dao->getAll();
    }

    public function buscarCampo($codigo) {
        if ($codigo > 0) {
            return $this->dao->getById($codigo);
        }
        return null;
    }

    public function atualizarCampo($codigo, $nome) {
        if ($codigo > 0 && $nome != null && strlen($nome) > 0) {
            $campo = new Campo($codigo, $nome);
            $this->dao->update($campo);
            return true;
        }
        return false;
    }

    public function apagarCampo($codigo) {
        if ($codigo != 0) {
            $this->dao->delete($codigo);
            return true;
        }
        return false;
    }
}
?>
