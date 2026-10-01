<?php
require_once __DIR__ . '/../Dao/TurmaDao.php';

class TurmaController {
    private $dao;

    public function __construct() {
        $this->dao = new TurmaDao();
    }

    public function listar() {
        return $this->dao->getAll();
    }

    public function listarDiretores() {
        return $this->dao->getDiretores();
    }

    public function listarQualiNiveis() {
        return $this->dao->getQualiNiveis();
    }

    public function buscar($codigo) {
        return $this->dao->getById($codigo);
    }

    public function store() {
        $id_quali_nivel = !empty($_POST['id_quali_nivel']) ? (int) $_POST['id_quali_nivel'] : null;
        $turma = new Turma(null, trim($_POST['nome']), (int) $_POST['ano_lectivo'], trim($_POST['turno']), (int) $_POST['id_diretor_turma'], $id_quali_nivel);
        return $this->dao->create($turma);
    }

    public function update($codigo) {
        $id_quali_nivel = !empty($_POST['id_quali_nivel']) ? (int) $_POST['id_quali_nivel'] : null;
        $turma = new Turma($codigo, trim($_POST['nome']), (int) $_POST['ano_lectivo'], trim($_POST['turno']), (int) $_POST['id_diretor_turma'], $id_quali_nivel);
        return $this->dao->update($turma);
    }

    public function delete($codigo) {
        return $this->dao->delete($codigo);
    }
}
?>
