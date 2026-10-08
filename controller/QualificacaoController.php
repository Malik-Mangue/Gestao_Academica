<?php
require_once __DIR__ . '/../Dao/QualificacaoDao.php';
require_once __DIR__ . '/../model/Qualificacao.php';
require_once __DIR__ . '/ClassificacaoController.php';
require_once __DIR__ . '/Quali_NivelController.php';
require_once __DIR__ . '/Quali_moduloController.php';
require_once __DIR__ . '/LicaoController.php';
require_once __DIR__ . '/MatriculaController.php';
require_once __DIR__ . '/../services/Validador.php';

class QualificacaoController {
    private $dao;

    public function __construct() {
        $this->dao = new QualificacaoDao();
    }

    public function listar($titulo = '') {
        return $this->dao->getAll($titulo !== '' ? $titulo : null);
    }

    public function buscar($codigo) {
        return $this->dao->getById($codigo);
    }

    public function store() {
        $titulo = trim($_POST['titulo'] ?? '');
        $coordenador = filter_input(INPUT_POST, 'coordenador', FILTER_VALIDATE_INT);

        if (!Validador::texto($titulo, 60)) {
            return false;
        }
        if ($coordenador == null || $coordenador == false) {
            return false;
        }

        $qualificacao = new Qualificacao(null, $titulo, (int) $coordenador);
        return (bool) $this->dao->create($qualificacao);
    }

    public function update($codigo) {
        if ($codigo == null || $codigo == false) {
            return false;
        }
        $codigo = (int) $codigo;

        $titulo = trim($_POST['titulo'] ?? '');
        $coordenador = filter_input(INPUT_POST, 'coordenador', FILTER_VALIDATE_INT);

        if (!Validador::texto($titulo, 60)) {
            return false;
        }
        if ($coordenador == null || $coordenador == false) {
            return false;
        }

        $qualificacao = new Qualificacao($codigo, $titulo, (int) $coordenador);
        return (bool) $this->dao->update($qualificacao);
    }

    public function delete($codigo) {
        $codigo = (int) $codigo;
        if ($codigo <= 0) {
            return false;
        }

        // Verificar dependencias antes de eliminar
        if ((new LicaoController())->existeLicaoPorQualificacao($codigo)) {
            throw new Exception("Não é possível eliminar esta qualificação pois está sendo usada em uma ou mais lições.");
        }

        if ((new MatriculaController())->existeMatriculaPorQualificacao($codigo)) {
            throw new Exception("Não é possível eliminar esta qualificação pois está sendo usada em uma ou mais matrículas.");
        }

        if ($this->dao->existeQualificacaoEmQualiModulo($codigo)) {
            throw new Exception("Não é possível eliminar esta qualificação pois está associada a um ou mais módulos.");
        }

        return (bool) $this->dao->delete($codigo);
    }

    // Compatibilidade: as views de modulo/matricula/inscricao esperam
    // objetos Qualificacao (getCodigo/getTitulo) para os selects.
    public function comboQualificacao() {
        return $this->listar();
    }

    // Helpers para listados de opcoes nos formularios
    public function listarCoordenadores() {
        return $this->dao->getCoordenadores();
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
