<?php
require_once __DIR__ . '/../Dao/QualificacaoDao.php';
require_once __DIR__ . '/../model/Qualificacao.php';
require_once __DIR__ . '/../model/Campo.php';
require_once __DIR__ . '/../model/Nivel.php';
require_once __DIR__ . '/ClassificacaoController.php';
require_once __DIR__ . '/Quali_NivelController.php';
require_once __DIR__ . '/Quali_moduloController.php';
require_once __DIR__ . '/LicaoController.php';
require_once __DIR__ . '/MatriculaController.php';
require_once __DIR__ . '/CampoController.php';
require_once __DIR__ . '/NivelController.php';
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
        $codigoCampo = filter_input(INPUT_POST, 'campo', FILTER_VALIDATE_INT);
        $niveis = $_POST['nivel'] ?? [];

        if (!Validador::texto($titulo, 60)) {
            return false;
        }
        if ($coordenador == null || $coordenador == false) {
            return false;
        }
        if ($codigoCampo == null || $codigoCampo == false) {
            return false;
        }
        if (!is_array($niveis) || count($niveis) === 0) {
            return false;
        }

        $qualificacao = new Qualificacao(null, $titulo, (int) $coordenador);
        if (!$this->dao->create($qualificacao) || $qualificacao->getCodigo() <= 0) {
            return false;
        }

        $campo = new Campo((int) $codigoCampo, null);
        $classificacaoController = new ClassificacaoController();
        if (!$classificacaoController->cadastrarClassificacao($campo, $qualificacao)) {
            $this->dao->delete($qualificacao->getCodigo());
            return false;
        }

        $qualiNivelController = new Quali_NivelController();
        $criados = [];
        foreach ($niveis as $codigoNivel) {
            $nivel = new Nivel((int) $codigoNivel, null);
            $qualiNivel = $qualiNivelController->cadastrarQuali_Nivel($nivel, $qualificacao);
            if ($qualiNivel == null || $qualiNivel->getCodigo() <= 0) {
                foreach ($criados as $codigoCriado) {
                    $qualiNivelController->delete($codigoCriado);
                }
                $this->dao->deleteCompleto($qualificacao->getCodigo());
                return false;
            }
            $criados[] = $qualiNivel->getCodigo();
        }

        return true;
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


    public function comboQualificacao() {
        return $this->listar();
    }


    public function listarCoordenadores() {
        return $this->dao->getCoordenadores();
    }

    public function listarCampos() {
        return (new CampoController())->listarCampo();
    }

    public function listarNiveis() {
        return (new NivelController())->listar();
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
