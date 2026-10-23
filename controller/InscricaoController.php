<?php
require_once __DIR__ . '/../Dao/InscricaoDao.php';
require_once __DIR__ . '/../model/Inscricao.php';
require_once __DIR__ . '/Validacao.php';

class InscricaoController {
    use Validacao;

    private $dao;

    public function __construct() {
        $this->dao = new InscricaoDao();
    }

    public function cadastrarInscricao($formando, $modulo, $semestre, $data_inscricao, $turma = null) {
        if ($formando != null && $modulo != null && $semestre != null && strlen($semestre) > 0
            && $data_inscricao != null && strlen($data_inscricao) > 0) {

            if ($this->dao->existeInscricaoPorFormandoModulo($formando->getCodigo(), $modulo->getCodigo())) {
                return false;
            }

            $inscricao = new Inscricao(null, $formando, $modulo, $semestre, $data_inscricao);
            $inscricao->setTurma($turma);
            return $this->dao->create($inscricao);
        }
        return false;
    }

    /**
     * Regista vários módulos numa única transação.
     *
     * Quando $semestre é null, o semestre de cada módulo é obtido a partir da
     * respetiva associação Quali_modulo, permitindo selecionar módulos de
     * semestres diferentes no mesmo pedido.
     *
     * $turma (opcional) é associada a todas as inscrições criadas.
     */
    public function cadastrarInscricoes($formando, $modulos, $semestre, $data_inscricao, $turma = null) {
        if ($formando == null || !is_array($modulos) || empty($modulos)
            || $data_inscricao == null || strlen($data_inscricao) === 0) {
            return false;
        }
        if ($semestre !== null && strlen($semestre) === 0) {
            return false;
        }

        $inscricoes = [];
        $modulosVistos = [];
        foreach ($modulos as $modulo) {
            if ($modulo == null) {
                return false;
            }

            $codigoModulo = (int) $modulo->getCodigo();
            if ($codigoModulo <= 0 || isset($modulosVistos[$codigoModulo])) {
                return false;
            }
            if ($this->dao->existeInscricaoPorFormandoModulo($formando->getCodigo(), $codigoModulo)) {
                return false;
            }

            $semestreModulo = $semestre;
            if ($semestreModulo === null) {
                $qualiModulo = $modulo->getQualiModulo();
                $semestreModulo = $qualiModulo !== null ? $qualiModulo->getSemestre() : null;
            }
            if ($semestreModulo === null || strlen((string) $semestreModulo) === 0) {
                return false;
            }

            $modulosVistos[$codigoModulo] = true;

            $inscricao = new Inscricao(null, $formando, $modulo, $semestreModulo, $data_inscricao);
            $inscricao->setTurma($turma);
            $inscricoes[] = $inscricao;
        }

        return $this->dao->createMany($inscricoes);
    }

    public function listarInscricao($pesquisa = null, $codCampo = null) {
        return $this->dao->getAll($pesquisa, $codCampo);
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

    public function existeInscricaoPorFormandoModulo($codigoFormando, $codigoModulo) {
        return $this->dao->existeInscricaoPorFormandoModulo($codigoFormando, $codigoModulo);
    }
}
?>
