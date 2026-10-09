<?php
require_once __DIR__ . '/../Dao/MatriculaDao.php';
require_once __DIR__ . '/../model/Matricula.php';

class MatriculaController {
    private $dao;

    public function __construct() {
        $this->dao = new MatriculaDao();
    }

    public function cadastrarMatricula($formando, $qualificacao, $nivel, $id_quali_nivel, $data_matricula) {
        if ($formando != null && $qualificacao != null && $nivel != null && $id_quali_nivel > 0 && strlen($data_matricula) > 0) {
            $matricula = new Matricula(null, $formando, $qualificacao, $nivel, $id_quali_nivel, $data_matricula);
            $this->dao->create($matricula);
            return true;
        }
        return false;
    }

    public function listarMatricula($nome) {
        return $this->dao->getAll($nome);
    }

    public function atualizarMatricula($codigo, $formando, $qualificacao, $nivel, $id_quali_nivel, $data_matricula) {
        if ($codigo > 0 && $formando != null && $qualificacao != null && $nivel != null && $id_quali_nivel > 0 && strlen($data_matricula) > 0) {
            $matricula = new Matricula($codigo, $formando, $qualificacao, $nivel, $id_quali_nivel, $data_matricula);
            $this->dao->update($matricula);
            return true;
        }
        return false;
    }

    public function apagarMatricula($codigo) {
        if ($codigo != 0) {
            $this->dao->delete($codigo);
            return true;
        }
        return false;
    }

    public function existeMatriculaPorFormando($codigoFormando) {
        return $this->dao->existeMatriculaPorFormando($codigoFormando);
    }

    public function existeMatriculaPorQualificacao($codigoQualificacao) {
        return $this->dao->existeMatriculaPorQualificacao($codigoQualificacao);
    }

    public function existeMatriculaPorIdQuaLiNivel($idQualiNivel) {
        return $this->dao->existeMatriculaPorIdQuaLiNivel($idQualiNivel);
    }

    public function buscarCodigoQualiNivel($qualificacao, $nivel) {
        if ($qualificacao != null && $nivel != null) {
            return (new Quali_NivelController())->buscarCodigo($qualificacao, $nivel);
        }
        return 0;
    }
}
?>
