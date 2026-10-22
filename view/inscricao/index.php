<?php
require_once __DIR__ . '/../../services/Sessao.php';
Sessao::exigirLogin('../login/index.php');
require_once __DIR__ . '/../../controller/InscricaoController.php';
require_once __DIR__ . '/../../controller/FormandoController.php';
require_once __DIR__ . '/../../controller/ModuloController.php';
require_once __DIR__ . '/../../controller/Quali_NivelController.php';
require_once __DIR__ . '/../../controller/QualificacaoController.php';
require_once __DIR__ . '/../../controller/NivelController.php';
require_once __DIR__ . '/../../controller/CampoController.php';
require_once __DIR__ . '/../../controller/TurmaController.php';

$controller       = new InscricaoController();
$formandoCtrl     = new FormandoController();
$moduloCtrl       = new ModuloController();
$qualiNivelCtrl   = new Quali_NivelController();
$qualificacaoCtrl = new QualificacaoController();
$nivelCtrl        = new NivelController();
$turmaCtrl        = new TurmaController();

$semestres = ['1º Semestre', '2º Semestre'];
$tiposModulo = ['Genérico', 'Vocacional'];

/*
 * Inscrição de vários módulos a partir do modal "Adicionar módulo".
 *
 * A seleção temporária (módulos ainda não gravados) viaja em parâmetros GET
 * e campos ocultos, pelo que remover um módulo da tabela nunca elimina
 * inscrições já persistidas na base de dados.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_modulo'])) {
    $acaoModulo          = $_POST['acao_modulo'];
    $codFormandoModulo   = filter_input(INPUT_POST, 'codFormando', FILTER_VALIDATE_INT);
    $codCampoModulo      = filter_input(INPUT_POST, 'codCampo', FILTER_VALIDATE_INT);
    $idQualiNivelModulo  = filter_input(INPUT_POST, 'idQualiNivel', FILTER_VALIDATE_INT);
    $codTurmaModulo      = filter_input(INPUT_POST, 'codTurma', FILTER_VALIDATE_INT);
    $dataModuloPost      = trim($_POST['data_inscricao'] ?? '');
    $filtroSemestrePost  = trim($_POST['filtroSemestre'] ?? '');
    $filtroTipoPost      = trim($_POST['filtroTipo'] ?? '');
    $pesquisaModuloPost  = trim($_POST['pesquisaModulo'] ?? '');
    $codModuloAcao       = filter_input(INPUT_POST, 'codModulo', FILTER_VALIDATE_INT);

    $idsSelecionados = filter_input(
        INPUT_POST,
        'selecionados',
        FILTER_VALIDATE_INT,
        ['flags' => FILTER_REQUIRE_ARRAY]
    );
    if (!is_array($idsSelecionados)) {
        $idsSelecionados = [];
    }
    $idsSelecionados = array_values(array_filter(
        array_unique($idsSelecionados),
        function ($codigoModulo) {
            return (int) $codigoModulo > 0;
        }
    ));

    $parametrosModulo = [
        'modal'          => 'adicionar-modulo',
        'codFormando'    => $codFormandoModulo ? (int) $codFormandoModulo : null,
        'codCampo'       => $codCampoModulo ? (int) $codCampoModulo : null,
        'idQualiNivel'   => $idQualiNivelModulo ? (int) $idQualiNivelModulo : null,
        'codTurma'       => $codTurmaModulo ? (int) $codTurmaModulo : null,
        'data_inscricao' => $dataModuloPost,
        'filtroSemestre' => $filtroSemestrePost,
        'filtroTipo'     => $filtroTipoPost,
        'pesquisaModulo' => $pesquisaModuloPost,
    ];

    if ($acaoModulo === 'adicionar') {
        if ($codModuloAcao && !in_array((int) $codModuloAcao, $idsSelecionados, true)) {
            $idsSelecionados[] = (int) $codModuloAcao;
        }
    } elseif ($acaoModulo === 'remover') {
        $idsSelecionados = array_values(array_diff($idsSelecionados, [(int) $codModuloAcao]));
    }

    if (!empty($idsSelecionados)) {
        $parametrosModulo['selecionados'] = $idsSelecionados;
    }
    foreach ($parametrosModulo as $chave => $valor) {
        if ($valor === null || $valor === '') {
            unset($parametrosModulo[$chave]);
        }
    }

    header('Location: index.php?' . http_build_query($parametrosModulo) . '#modal-adicionar-modulo');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = isset($_POST['gravar']) ? 'gravar'
          : (isset($_POST['editar']) ? 'editar' : (isset($_POST['deletar']) ? 'deletar' : null));

    if ($acao === 'deletar') {
        Sessao::exigirAcao(Sessao::ACAO_REMOVER, Sessao::RECURSO_INSCRICOES, 'index.php');
        $codigo = filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT);
        $_SESSION['flash'] = $controller->apagarInscricao($codigo)
            ? ['type' => 'success', 'msg' => 'Inscrição removida com sucesso!']
            : ['type' => 'danger', 'msg' => 'Não foi possível eliminar a inscrição.'];
        header('Location: index.php');
        exit;
    }

    if ($acao === 'gravar') {
        Sessao::exigirAcao(Sessao::ACAO_CRIAR, Sessao::RECURSO_INSCRICOES, 'index.php');

        $codFormando = filter_input(INPUT_POST, 'codFormando', FILTER_VALIDATE_INT);
        $codCampo    = filter_input(INPUT_POST, 'codCampo', FILTER_VALIDATE_INT);
        $idQualiNivel = filter_input(INPUT_POST, 'idQualiNivel', FILTER_VALIDATE_INT);
        $codTurma    = filter_input(INPUT_POST, 'codTurma', FILTER_VALIDATE_INT);
        $data        = trim($_POST['data_inscricao'] ?? '');

        $idsSelecionados = filter_input(
            INPUT_POST,
            'selecionados',
            FILTER_VALIDATE_INT,
            ['flags' => FILTER_REQUIRE_ARRAY]
        );
        if (!is_array($idsSelecionados)) {
            $idsSelecionados = [];
        }
        $idsSelecionados = array_values(array_filter(
            array_unique($idsSelecionados),
            function ($codigoModulo) {
                return (int) $codigoModulo > 0;
            }
        ));

        $formando   = $codFormando ? $formandoCtrl->buscar($codFormando) : null;
        $qualiNivel = $idQualiNivel ? $qualiNivelCtrl->buscar($idQualiNivel) : null;
        $turma      = $codTurma ? $turmaCtrl->buscarTurma($codTurma) : null;

        $modulosParaGravar = [];
        $erroValidacao = null;

        if ($formando === null) {
            $erroValidacao = 'Selecione um formando válido.';
        } elseif ($qualiNivel === null) {
            $erroValidacao = 'Selecione uma qualificação/nível válida.';
        } elseif (!$controller->data($data)) {
            $erroValidacao = 'Indique uma data de inscrição válida.';
        } elseif (empty($idsSelecionados)) {
            $erroValidacao = 'Adicione pelo menos um módulo antes de gravar a inscrição.';
        } elseif ($turma === null
            || $turma->getQualiNivel() === null
            || (int) $turma->getQualiNivel()->getCodigo() !== (int) $qualiNivel->getCodigo()) {
            $erroValidacao = 'Selecione uma turma pertencente à qualificação/nível escolhida.';
        } else {
            foreach ($idsSelecionados as $codigoModulo) {
                $modulo = $moduloCtrl->buscarModulo($codigoModulo);

                if ($modulo === null
                    || $modulo->getQualiNivel() === null
                    || (int) $modulo->getQualiNivel()->getCodigo() !== (int) $qualiNivel->getCodigo()
                    || $modulo->getQualiModulo() === null
                    || trim((string) $modulo->getQualiModulo()->getSemestre()) === ''
                    || $controller->existeInscricaoPorFormandoModulo($codFormando, $codigoModulo)) {
                    $erroValidacao = 'Um ou mais módulos selecionados já não estão disponíveis para esta qualificação ou formando.';
                    break;
                }

                $modulosParaGravar[] = $modulo;
            }
        }

        if ($erroValidacao !== null) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => $erroValidacao];

            $parametrosNovo = [
                'modal'          => 'novo',
                'carregar'       => 1,
                'codFormando'    => $codFormando ?: null,
                'codCampo'       => $codCampo ?: null,
                'idQualiNivel'   => $idQualiNivel ?: null,
                'codTurma'       => $codTurma ?: null,
                'data_inscricao' => $data,
            ];
            if (!empty($idsSelecionados)) {
                $parametrosNovo['selecionados'] = $idsSelecionados;
            }
            foreach ($parametrosNovo as $chave => $valor) {
                if ($valor === null || $valor === '') {
                    unset($parametrosNovo[$chave]);
                }
            }

            header('Location: index.php?' . http_build_query($parametrosNovo) . '#modal-novo');
            exit;
        }

        $sucesso = $controller->cadastrarInscricoes($formando, $modulosParaGravar, null, $data);

        if ($sucesso) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'msg'  => count($modulosParaGravar) . ' inscrição(ões) registada(s) com sucesso!',
            ];
        } else {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg'  => 'Não foi possível registar as inscrições. Verifique se os módulos já se encontram inscritos.',
            ];
        }

        header('Location: index.php');
        exit;
    }

    if ($acao === 'editar') {
        Sessao::exigirAcao(Sessao::ACAO_EDITAR, Sessao::RECURSO_INSCRICOES, 'index.php');

        $codigo      = filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT);
        $codFormando = filter_input(INPUT_POST, 'codFormando', FILTER_VALIDATE_INT);
        $codModulo   = filter_input(INPUT_POST, 'codModulo', FILTER_VALIDATE_INT);
        $semestre    = trim($_POST['semestre'] ?? '');
        $data        = trim($_POST['data_inscricao'] ?? '');

        if (!$codFormando) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione o formando.'];
        } elseif (!$codModulo) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione o módulo.'];
        } elseif (!in_array($semestre, $semestres, true)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione um semestre válido.'];
        } elseif (!$controller->data($data)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Indique uma data de inscrição válida.'];
        } else {
            $formando = new Formando($codFormando, null, null, null, null, null);
            $modulo   = new Modulo($codModulo, null, null);

            $sucesso = $controller->atualizarInscricao($codigo, $formando, $modulo, $semestre, $data);

            $_SESSION['flash'] = $sucesso
                ? ['type' => 'success', 'msg' => 'Inscrição atualizada com sucesso!']
                : ['type' => 'danger', 'msg' => 'Não foi possível gravar a inscrição.'];
        }
        header('Location: index.php');
        exit;
    }
}

$pesquisa  = trim($_GET['pesquisa'] ?? '');
$inscricoes = $controller->listarInscricao($pesquisa);
$formandos  = $formandoCtrl->listar();
$modulos    = $moduloCtrl->listarModulos();
$pares      = $qualiNivelCtrl->listarPares();
$campos     = (new CampoController())->listarCampo();

// Contexto do "Carregar" do modal de edição (sem JavaScript).
$carregar      = isset($_GET['carregar']);
$modalAtivo    = $_GET['modal'] ?? '';
$ctxCampo      = filter_input(INPUT_GET, 'codCampo', FILTER_VALIDATE_INT);
$ctxQualiNivel = filter_input(INPUT_GET, 'idQualiNivel', FILTER_VALIDATE_INT);
$ctxTurma      = filter_input(INPUT_GET, 'codTurma', FILTER_VALIDATE_INT);
$ctxTipo       = trim($_GET['tipoModulo'] ?? '');
$ctxSemestre   = trim($_GET['semestre'] ?? '');
$ctxModulo     = filter_input(INPUT_GET, 'codModulo', FILTER_VALIDATE_INT);
$ctxFormando   = filter_input(INPUT_GET, 'codFormando', FILTER_VALIDATE_INT);

/*
 * Contexto do modal "Nova Inscrição" e do seletor "Adicionar módulo".
 */
$campoNovo = $ctxCampo ?: 0;
$paresNovo = $campoNovo ? $qualiNivelCtrl->listarPares($campoNovo) : $pares;

$qnNovoSel = $ctxQualiNivel ?: null;
if ($campoNovo && $qnNovoSel !== null) {
    $parValido = false;
    foreach ($paresNovo as $par) {
        if ((int) $par['codigo'] === (int) $qnNovoSel) {
            $parValido = true;
            break;
        }
    }
    if (!$parValido) {
        $qnNovoSel = null;
    }
}

$formandoNovoSel = $ctxFormando ?: null;

$dataNovo = trim($_GET['data_inscricao'] ?? '');
if ($dataNovo === '') {
    $dataNovo = date('Y-m-d');
}

$formandosNovo = $campoNovo
    ? $formandoCtrl->listarPorCampo($campoNovo)
    : array_map(function ($formando) {
        return ['codigo' => $formando->getCodigo(), 'descricao' => $formando->getNome() . ' ' . $formando->getApelido()];
    }, $formandos);

$turmasNovo = $qnNovoSel ? $turmaCtrl->getByQualiNivel($qnNovoSel) : [];
$turmaNovoSel = null;
if ($ctxTurma) {
    foreach ($turmasNovo as $turmaDisponivel) {
        if ((int) $turmaDisponivel->getCodigo() === (int) $ctxTurma) {
            $turmaNovoSel = $turmaDisponivel;
            break;
        }
    }
}

/*
 * Estado do seletor de módulos.
 */
$codFormandoModulo  = $formandoNovoSel ? (int) $formandoNovoSel : 0;
$codCampoModulo     = $campoNovo;
$idQualiNivelModulo = $qnNovoSel ? (int) $qnNovoSel : 0;
$dataModulo         = $dataNovo;

$semestreModulo = trim($_GET['filtroSemestre'] ?? '');
if ($semestreModulo === 'todos' || !in_array($semestreModulo, $semestres, true)) {
    $semestreModulo = '';
}

$tipoModuloFiltro = trim($_GET['filtroTipo'] ?? '');
if ($tipoModuloFiltro === 'todos' || !in_array($tipoModuloFiltro, $tiposModulo, true)) {
    $tipoModuloFiltro = '';
}

$pesquisaModulo = trim($_GET['pesquisaModulo'] ?? '');
if (mb_strlen($pesquisaModulo) > 100) {
    $pesquisaModulo = mb_substr($pesquisaModulo, 0, 100);
}

$idsSelecionados = filter_input(
    INPUT_GET,
    'selecionados',
    FILTER_VALIDATE_INT,
    ['flags' => FILTER_REQUIRE_ARRAY]
);
if (!is_array($idsSelecionados)) {
    $idsSelecionados = [];
}
$idsSelecionados = array_values(array_filter(
    array_unique($idsSelecionados),
    function ($codigoModulo) {
        return (int) $codigoModulo > 0;
    }
));

$qualiNivelModulo = $idQualiNivelModulo ? $qualiNivelCtrl->buscar($idQualiNivelModulo) : null;
if ($qualiNivelModulo === null) {
    $idQualiNivelModulo = 0;
}

$qualificacaoModulo = null;
$nivelModulo = null;
if ($qualiNivelModulo !== null) {
    $qualificacaoModulo = $qualificacaoCtrl->buscar($qualiNivelModulo->getCod_quali());
    $nivelModulo = $nivelCtrl->buscar($qualiNivelModulo->getCod_nivel());
}

$tituloModalModulo = $qualificacaoModulo !== null
    ? $qualificacaoModulo->getTitulo()
    : 'Adicionar módulo';
if ($nivelModulo !== null) {
    $tituloModalModulo .= ' — ' . $nivelModulo->getNome();
}

$modulosSelecionados = [];
if ($idQualiNivelModulo) {
    foreach ($idsSelecionados as $codigoModuloSelecionado) {
        $moduloSelecionado = $moduloCtrl->buscarModulo($codigoModuloSelecionado);

        if ($moduloSelecionado !== null
            && $moduloSelecionado->getQualiNivel() !== null
            && (int) $moduloSelecionado->getQualiNivel()->getCodigo() === (int) $idQualiNivelModulo) {
            $modulosSelecionados[(int) $moduloSelecionado->getCodigo()] = $moduloSelecionado;
        }
    }
}

$modulosDisponiveis = [];
if ($idQualiNivelModulo) {
    $modulosDisponiveis = $moduloCtrl->listarModulosFiltrados(
        null,
        $tipoModuloFiltro !== '' ? $tipoModuloFiltro : null,
        $semestreModulo !== '' ? $semestreModulo : null,
        $pesquisaModulo !== '' ? $pesquisaModulo : null,
        $idQualiNivelModulo,
        $codFormandoModulo
    );

    $modulosDisponiveis = array_values(array_filter(
        $modulosDisponiveis,
        function ($moduloDisponivel) use ($modulosSelecionados) {
            return !isset($modulosSelecionados[(int) $moduloDisponivel['codigo']]);
        }
    ));
}

$totalModulosSelecionados = count($modulosSelecionados);
$codFormandoSelecionado  = $codFormandoModulo ?: 0;
$idQualiNivelSelecionado = $qualiNivelModulo !== null ? (int) $qualiNivelModulo->getCodigo() : 0;
$codTurmaSelecionada     = $turmaNovoSel !== null ? (int) $turmaNovoSel->getCodigo() : 0;

$estadoModulo = [
    'modal'          => 'adicionar-modulo',
    'codFormando'    => $codFormandoSelecionado ?: null,
    'codCampo'       => $codCampoModulo ?: null,
    'idQualiNivel'   => $idQualiNivelSelecionado ?: null,
    'codTurma'       => $codTurmaSelecionada ?: null,
    'data_inscricao' => $dataModulo,
    'filtroSemestre' => $semestreModulo !== '' ? $semestreModulo : 'todos',
    'filtroTipo'     => $tipoModuloFiltro !== '' ? $tipoModuloFiltro : 'todos',
    'pesquisaModulo' => $pesquisaModulo,
];
if (!empty($modulosSelecionados)) {
    $estadoModulo['selecionados'] = [];
    foreach ($modulosSelecionados as $moduloSelecionado) {
        $estadoModulo['selecionados'][] = (int) $moduloSelecionado->getCodigo();
    }
}

$urlModulo = function (array $alteracoes = []) use ($estadoModulo) {
    $parametros = array_merge($estadoModulo, $alteracoes);
    foreach ($parametros as $chave => $valor) {
        if ($valor === null || $valor === '') {
            unset($parametros[$chave]);
        }
    }
    return 'index.php?' . http_build_query($parametros) . '#modal-adicionar-modulo';
};

$urlLimparPesquisaModulo = $urlModulo(['pesquisaModulo' => '']);

$parametrosFormulario = [
    'modal'          => 'novo',
    'carregar'       => 1,
    'codFormando'    => $codFormandoSelecionado ?: null,
    'codCampo'       => $codCampoModulo ?: null,
    'idQualiNivel'   => $idQualiNivelSelecionado ?: null,
    'codTurma'       => $codTurmaSelecionada ?: null,
    'data_inscricao' => $dataNovo,
];
if (!empty($modulosSelecionados)) {
    $parametrosFormulario['selecionados'] = [];
    foreach ($modulosSelecionados as $moduloSelecionado) {
        $parametrosFormulario['selecionados'][] = (int) $moduloSelecionado->getCodigo();
    }
}
foreach ($parametrosFormulario as $chave => $valor) {
    if ($valor === null || $valor === '') {
        unset($parametrosFormulario[$chave]);
    }
}
$urlVoltarFormulario = 'index.php?' . http_build_query($parametrosFormulario) . '#modal-novo';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pode_criar   = Sessao::podeCriar(Sessao::RECURSO_INSCRICOES);
$pode_editar  = Sessao::podeEditar(Sessao::RECURSO_INSCRICOES);
$pode_remover = Sessao::podeRemover(Sessao::RECURSO_INSCRICOES);

$page_title = 'Inscrições em Módulos';
$active_menu = 'inscricao';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>
<main class="main-wrapper">
    <div class="content-header">
        <h1>Inscrições em Módulos</h1>
        <div class="breadcrumb">
            <a href="../../view/dashboard/index.php">Home</a>
            <span class="divider">/</span>
            <span>Inscrições</span>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <span><?= htmlspecialchars($flash['msg']) ?></span>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Listagem de Inscrições</h2>
            <?php if ($pode_criar): ?>
                <div class="table-actions">
                    <a href="#modal-novo" class="btn btn-primary">+ Nova Inscrição</a>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="get" action="index.php" class="form-actions">
                <div class="form-group">
                    <label for="pesquisa">Pesquisar</label>
                    <input type="text" id="pesquisa" name="pesquisa" class="form-control"
                           maxlength="100" placeholder="Formando, módulo, qualificação, semestre ou data"
                           value="<?= htmlspecialchars($pesquisa) ?>">
                </div>
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="index.php" class="btn btn-secondary">Limpar</a>
            </form>

            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th class="col-codigo">Código</th>
                            <th>Formando</th>
                            <th>Módulo</th>
                            <th>Qualificação</th>
                            <th>Semestre</th>
                            <th>Data de Inscrição</th>
                            <?php if ($pode_editar || $pode_remover): ?>
                                <th class="col-opcoes">Opções</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inscricoes)): ?>
                            <tr>
                                <td colspan="<?= $pode_editar || $pode_remover ? 7 : 6 ?>" class="table-empty">
                                    Nenhuma inscrição encontrada.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inscricoes as $inscricao): ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($inscricao->getCodigo()) ?></strong></td>
                                    <td><?= htmlspecialchars($inscricao->getFormando()->getNome() . ' ' . $inscricao->getFormando()->getApelido()) ?></td>
                                    <td><?= htmlspecialchars($inscricao->getModulo()->getNome()) ?></td>
                                    <td><?= htmlspecialchars(
                                            $inscricao->getModulo()->getQualiNivel() && $inscricao->getModulo()->getQualiNivel()->getQualificacao()
                                                ? $inscricao->getModulo()->getQualiNivel()->getQualificacao()->getTitulo()
                                                : '-'
                                        ) ?></td>
                                    <td><?= htmlspecialchars($inscricao->getSemestre()) ?></td>
                                    <td><?= htmlspecialchars($inscricao->getDataInscricao() ?: '-') ?></td>
                                    <?php if ($pode_editar || $pode_remover): ?>
                                        <td class="col-opcoes">
                                            <div class="table-actions table-actions-end">
                                                <?php if ($pode_editar): ?>
                                                    <a href="#modal-editar-<?= $inscricao->getCodigo() ?>" class="btn btn-sm btn-edit">Editar</a>
                                                <?php endif; ?>
                                                <?php if ($pode_remover): ?>
                                                    <a href="#modal-deletar-<?= $inscricao->getCodigo() ?>" class="btn btn-sm btn-delete">Remover</a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>

                                <?php if ($pode_editar): ?>
                                    <div id="modal-editar-<?= $inscricao->getCodigo() ?>" class="modal-overlay">
                                        <div class="modal-box">
                                            <div class="modal-header">
                                                <h3>Editar Inscrição #<?= htmlspecialchars($inscricao->getCodigo()) ?></h3>
                                                <a href="#" class="modal-close">
                                                    <img src="../../assets/icons/close.svg" alt="Fechar">
                                                </a>
                                            </div>
                                            <form method="post" action="index.php">
                                                <div class="modal-body">
                                                    <input type="hidden" name="codigo" value="<?= htmlspecialchars($inscricao->getCodigo()) ?>">
                                                    <?php
                                                    $sufixo = 'editar-' . $inscricao->getCodigo();
                                                    $editAtivo = $carregar && $modalAtivo === $sufixo;
                                                    $qnInscricao = $inscricao->getModulo()->getQualiNivel();
                                                    $campoReg = ($qnInscricao !== null && $qnInscricao->getQualificacao() !== null)
                                                        ? $qnInscricao->getQualificacao()->getCodCampo() : null;
                                                    $campoEdit = $editAtivo ? (int) $ctxCampo : (int) $campoReg;
                                                    $paresEdit = $campoEdit ? $qualiNivelCtrl->listarPares($campoEdit) : $pares;
                                                    $qnEditSel = $editAtivo ? $ctxQualiNivel : ($qnInscricao !== null ? (int) $qnInscricao->getCodigo() : null);
                                                    $tipoEditSel = $editAtivo ? $ctxTipo : '';
                                                    $semEditSel = $editAtivo ? $ctxSemestre : $inscricao->getSemestre();
                                                    $codQualiEdit = null;
                                                    if ($qnEditSel) {
                                                        $qnObj = $qualiNivelCtrl->buscar($qnEditSel);
                                                        $codQualiEdit = $qnObj !== null ? $qnObj->getCod_quali() : null;
                                                    }
                                                    $modulosEdit = $editAtivo
                                                        ? $moduloCtrl->listarModulosFiltrados(
                                                            $codQualiEdit ?: null,
                                                            $tipoEditSel !== '' ? $tipoEditSel : null,
                                                            $semEditSel !== '' ? $semEditSel : null
                                                          )
                                                        : $modulos;
                                                    $moduloEditSel = $editAtivo ? $ctxModulo : (int) $inscricao->getModulo()->getCodigo();
                                                    $formandosEdit = $campoEdit ? $formandoCtrl->listarPorCampo($campoEdit) : array_map(function ($f) {
                                                        return ['codigo' => $f->getCodigo(), 'descricao' => $f->getNome() . ' ' . $f->getApelido()];
                                                    }, $formandos);
                                                    $formandoEditSel = $editAtivo ? $ctxFormando : (int) $inscricao->getFormando()->getCodigo();
                                                    $dataEdit = $editAtivo ? trim($_GET['data_inscricao'] ?? '') : $inscricao->getDataInscricao();
                                                    ?>
                                                    <input type="hidden" name="modal" value="<?= htmlspecialchars($sufixo) ?>">
                                                    <input type="hidden" name="pesquisa" value="<?= htmlspecialchars($pesquisa) ?>">
                                                    <div class="form-group">
                                                        <label for="e-c-<?= $inscricao->getCodigo() ?>">Campo / Área</label>
                                                        <select id="e-c-<?= $inscricao->getCodigo() ?>" name="codCampo" class="form-control">
                                                            <option value="">Selecione o campo...</option>
                                                            <?php foreach ($campos as $c): ?>
                                                                <option value="<?= (int) $c->getCodigo() ?>"
                                                                    <?= $campoEdit === (int) $c->getCodigo() ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($c->getNome()) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <button type="submit" name="carregar" value="1"
                                                                formmethod="get" formnovalidate
                                                                formaction="index.php#modal-editar-<?= (int) $inscricao->getCodigo() ?>"
                                                                class="btn btn-secondary btn-sm">Carregar</button>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="e-qn-<?= $inscricao->getCodigo() ?>">Qualificação / Nível</label>
                                                        <select id="e-qn-<?= $inscricao->getCodigo() ?>" name="idQualiNivel" class="form-control">
                                                            <option value="">Selecione a qualificação e o nível...</option>
                                                            <?php foreach ($paresEdit as $p): ?>
                                                                <option value="<?= (int) $p['codigo'] ?>"
                                                                    <?= $qnEditSel !== null && (int) $qnEditSel === (int) $p['codigo'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($p['descricao']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="e-t-<?= $inscricao->getCodigo() ?>">Tipo de Módulo</label>
                                                        <select id="e-t-<?= $inscricao->getCodigo() ?>" name="tipoModulo" class="form-control">
                                                            <option value="">Todos os tipos</option>
                                                            <?php foreach ($tiposModulo as $tp): ?>
                                                                <option value="<?= htmlspecialchars($tp) ?>"
                                                                    <?= $tipoEditSel === $tp ? 'selected' : '' ?>><?= htmlspecialchars($tp) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <button type="submit" name="carregar" value="1"
                                                                formmethod="get" formnovalidate
                                                                formaction="index.php#modal-editar-<?= (int) $inscricao->getCodigo() ?>"
                                                                class="btn btn-secondary btn-sm">Carregar</button>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="e-s-<?= $inscricao->getCodigo() ?>">Semestre <span class="required">*</span></label>
                                                        <select id="e-s-<?= $inscricao->getCodigo() ?>" name="semestre" class="form-control" required>
                                                            <?php foreach ($semestres as $s): ?>
                                                                <option value="<?= htmlspecialchars($s) ?>"
                                                                    <?= $semEditSel === $s ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($s) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <button type="submit" name="carregar" value="1"
                                                                formmethod="get" formnovalidate
                                                                formaction="index.php#modal-editar-<?= (int) $inscricao->getCodigo() ?>"
                                                                class="btn btn-secondary btn-sm">Carregar</button>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="e-m-<?= $inscricao->getCodigo() ?>">Módulo <span class="required">*</span></label>
                                                        <select id="e-m-<?= $inscricao->getCodigo() ?>" name="codModulo" class="form-control" required>
                                                            <option value="">Selecione o módulo...</option>
                                                            <?php foreach ($modulosEdit as $op): ?>
                                                                <option value="<?= (int) $op['codigo'] ?>"
                                                                    <?= (int) $moduloEditSel === (int) $op['codigo'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($op['descricao']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="e-d-<?= $inscricao->getCodigo() ?>">Data de Inscrição <span class="required">*</span></label>
                                                        <input type="date" id="e-d-<?= $inscricao->getCodigo() ?>" name="data_inscricao" class="form-control"
                                                               value="<?= htmlspecialchars($dataEdit) ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="e-f-<?= $inscricao->getCodigo() ?>">Formando <span class="required">*</span></label>
                                                        <select id="e-f-<?= $inscricao->getCodigo() ?>" name="codFormando" class="form-control" required>
                                                            <?php foreach ($formandosEdit as $f): ?>
                                                                <option value="<?= (int) $f['codigo'] ?>"
                                                                    <?= (int) $formandoEditSel === (int) $f['codigo'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($f['descricao']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="#" class="btn btn-secondary">Cancelar</a>
                                                    <button type="submit" name="editar" class="btn btn-success">Salvar Alterações</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($pode_remover): ?>
                                    <div id="modal-deletar-<?= $inscricao->getCodigo() ?>" class="modal-overlay">
                                        <div class="modal-box">
                                            <div class="modal-header modal-header-danger">
                                                <h3>Confirmar Exclusão de Registro</h3>
                                                <a href="#" class="modal-close">
                                                    <img src="../../assets/icons/close.svg" alt="Fechar">
                                                </a>
                                            </div>
                                            <form method="post" action="index.php">
                                                <div class="modal-body">
                                                    <input type="hidden" name="codigo" value="<?= htmlspecialchars($inscricao->getCodigo()) ?>">
                                                    <p class="confirm-question">
                                                        Tem certeza que deseja eliminar permanentemente esta inscrição?
                                                    </p>
                                                    <div class="confirm-detail-box">
                                                        <p><strong>Código:</strong> #<?= htmlspecialchars($inscricao->getCodigo()) ?></p>
                                                        <p><strong>Formando:</strong> <?= htmlspecialchars($inscricao->getFormando()->getNome()) ?></p>
                                                        <p><strong>Módulo:</strong> <?= htmlspecialchars($inscricao->getModulo()->getNome()) ?></p>
                                                        <p><strong>Semestre:</strong> <?= htmlspecialchars($inscricao->getSemestre()) ?></p>
                                                    </div>
                                                    <p class="confirm-warning">
                                                        Atenção: esta ação não poderá ser desfeita.
                                                    </p>
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="#" class="btn btn-secondary">Cancelar</a>
                                                    <button type="submit" name="deletar" class="btn btn-delete">Sim, Deletar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php if ($pode_criar): ?>
        <div id="modal-novo" class="modal-overlay">
            <div class="modal-box">
                <div class="modal-header">
                    <h3>Registar Nova Inscrição</h3>
                    <a href="#" class="modal-close">
                        <img src="../../assets/icons/close.svg" alt="Fechar">
                    </a>
                </div>
                <form method="post" action="index.php">
                    <div class="modal-body">
                        <input type="hidden" name="modal" value="novo">
                        <input type="hidden" name="pesquisa" value="<?= htmlspecialchars($pesquisa) ?>">
                        <?php foreach ($modulosSelecionados as $moduloSelecionado): ?>
                            <input type="hidden" name="selecionados[]" value="<?= (int) $moduloSelecionado->getCodigo() ?>">
                        <?php endforeach; ?>

                        <div class="form-group">
                            <label for="n-c">Campo / Área</label>
                            <select id="n-c" name="codCampo" class="form-control">
                                <option value="">Selecione o campo...</option>
                                <?php foreach ($campos as $campo): ?>
                                    <option value="<?= (int) $campo->getCodigo() ?>"
                                        <?= $campoNovo === (int) $campo->getCodigo() ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($campo->getNome()) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="carregar" value="1"
                                    formmethod="get" formnovalidate
                                    formaction="index.php#modal-novo"
                                    class="btn btn-secondary btn-sm">Carregar</button>
                        </div>

                        <div class="form-group">
                            <label for="n-qn">Qualificação / Nível</label>
                            <select id="n-qn" name="idQualiNivel" class="form-control">
                                <option value="">Selecione a qualificação e o nível...</option>
                                <?php foreach ($paresNovo as $par): ?>
                                    <option value="<?= (int) $par['codigo'] ?>"
                                        <?= $qnNovoSel !== null && (int) $qnNovoSel === (int) $par['codigo'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($par['descricao']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="carregar" value="1"
                                    formmethod="get" formnovalidate
                                    formaction="index.php#modal-novo"
                                    class="btn btn-secondary btn-sm">Carregar</button>
                        </div>

                        <div class="form-group">
                            <label>Módulo(s) <span class="required">*</span></label>
                            <button type="submit" name="abrir_modulos" value="1"
                                    formmethod="get" formnovalidate
                                    formaction="index.php#modal-adicionar-modulo"
                                    class="btn btn-primary">
                                Adicionar módulo
                            </button>
                        </div>

                        <div class="form-group">
                            <label for="n-turma">Turma <span class="required">*</span></label>
                            <select id="n-turma" name="codTurma" class="form-control" required>
                                <option value="">Selecione a turma...</option>
                                <?php foreach ($turmasNovo as $turma): ?>
                                    <option value="<?= (int) $turma->getCodigo() ?>"
                                        <?= $turmaNovoSel !== null && (int) $turmaNovoSel->getCodigo() === (int) $turma->getCodigo() ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($turma->getNome() . ' — ' . $turma->getTurno() . ' (' . $turma->getAnoIngresso() . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($qnNovoSel === null): ?>
                                <small class="form-help">Selecione primeiro a qualificação/nível e clique em «Carregar».</small>
                            <?php elseif (empty($turmasNovo)): ?>
                                <small class="form-help">Não existem turmas associadas à qualificação/nível escolhida.</small>
                            <?php endif; ?>
                        </div>

                        <?php if ($totalModulosSelecionados > 0): ?>
                            <div class="form-group">
                                <label>Módulos selecionados (<?= $totalModulosSelecionados ?>)</label>
                                <div class="table-responsive">
                                    <table class="table-custom">
                                        <thead>
                                            <tr>
                                                <th>Nome</th>
                                                <th>Semestre</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($modulosSelecionados as $moduloSelecionado): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($moduloSelecionado->getNome()) ?></td>
                                                    <td><?= htmlspecialchars(
                                                        $moduloSelecionado->getQualiModulo() !== null
                                                            ? $moduloSelecionado->getQualiModulo()->getSemestre()
                                                            : '-'
                                                    ) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="n-d">Data <span class="required">*</span></label>
                            <input type="date" id="n-d" name="data_inscricao" class="form-control"
                                   value="<?= htmlspecialchars($dataNovo) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="n-f">Formando <span class="required">*</span></label>
                            <select id="n-f" name="codFormando" class="form-control" required>
                                <option value="">Selecione o formando...</option>
                                <?php foreach ($formandosNovo as $formando): ?>
                                    <option value="<?= (int) $formando['codigo'] ?>"
                                        <?= $formandoNovoSel !== null && (int) $formandoNovoSel === (int) $formando['codigo'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($formando['descricao']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" class="btn btn-secondary">Cancelar</a>
                        <button type="reset" class="btn btn-reset">Limpar</button>
                        <button type="submit" name="gravar" class="btn btn-success">Salvar Inscrição</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="modal-adicionar-modulo" class="modal-overlay">
            <div class="modal-box modal-box-wide">
                <div class="modal-header">
                    <h3><?= htmlspecialchars($tituloModalModulo) ?></h3>
                    <a href="#" class="modal-close">
                        <img src="../../assets/icons/close.svg" alt="Fechar">
                    </a>
                </div>
                <div class="modal-body">
                    <h4 class="module-section-title">Módulos disponíveis</h4>

                    <form method="get" action="index.php#modal-adicionar-modulo" class="module-search">
                        <input type="hidden" name="modal" value="adicionar-modulo">
                        <input type="hidden" name="codFormando" value="<?= $codFormandoSelecionado ?: '' ?>">
                        <input type="hidden" name="codCampo" value="<?= $codCampoModulo ?: '' ?>">
                        <input type="hidden" name="idQualiNivel" value="<?= $idQualiNivelSelecionado ?: '' ?>">
                        <input type="hidden" name="codTurma" value="<?= $codTurmaSelecionada ?: '' ?>">
                        <input type="hidden" name="data_inscricao" value="<?= htmlspecialchars($dataModulo) ?>">
                        <input type="hidden" name="filtroSemestre" value="<?= htmlspecialchars($semestreModulo !== '' ? $semestreModulo : 'todos') ?>">
                        <input type="hidden" name="filtroTipo" value="<?= htmlspecialchars($tipoModuloFiltro !== '' ? $tipoModuloFiltro : 'todos') ?>">
                        <?php foreach ($modulosSelecionados as $moduloSelecionado): ?>
                            <input type="hidden" name="selecionados[]" value="<?= (int) $moduloSelecionado->getCodigo() ?>">
                        <?php endforeach; ?>
                        <div class="form-group">
                            <label for="m-pesquisa">Pesquisar módulo</label>
                            <input type="text" id="m-pesquisa" name="pesquisaModulo" class="form-control"
                                   maxlength="100" placeholder="Nome do módulo"
                                   value="<?= htmlspecialchars($pesquisaModulo) ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Pesquisar</button>
                        <a href="<?= htmlspecialchars($urlLimparPesquisaModulo) ?>" class="btn btn-secondary">Limpar</a>
                    </form>

                    <div class="filter-pills" role="group" aria-label="Filtrar por semestre">
                        <a href="<?= htmlspecialchars($urlModulo(['filtroSemestre' => 'todos'])) ?>"
                           class="filter-pill <?= $semestreModulo === '' ? 'is-active' : '' ?>">Todos</a>
                        <?php foreach ($semestres as $semestreOpcao): ?>
                            <a href="<?= htmlspecialchars($urlModulo(['filtroSemestre' => $semestreOpcao])) ?>"
                               class="filter-pill <?= $semestreModulo === $semestreOpcao ? 'is-active' : '' ?>">
                                <?= htmlspecialchars($semestreOpcao) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <div class="filter-pills" role="group" aria-label="Filtrar por categoria">
                        <a href="<?= htmlspecialchars($urlModulo(['filtroTipo' => 'todos'])) ?>"
                           class="filter-pill <?= $tipoModuloFiltro === '' ? 'is-active' : '' ?>">Todos</a>
                        <?php foreach ($tiposModulo as $tipoOpcao): ?>
                            <a href="<?= htmlspecialchars($urlModulo(['filtroTipo' => $tipoOpcao])) ?>"
                               class="filter-pill <?= $tipoModuloFiltro === $tipoOpcao ? 'is-active' : '' ?>">
                                <?= htmlspecialchars($tipoOpcao) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <div class="module-picker">
                        <?php if (empty($modulosDisponiveis)): ?>
                            <div class="module-picker-empty">
                                Nenhum módulo disponível para os filtros indicados.
                            </div>
                        <?php else: ?>
                            <?php foreach ($modulosDisponiveis as $moduloDisponivel): ?>
                                <div class="module-picker-item">
                                    <div class="module-picker-info">
                                        <strong><?= htmlspecialchars($moduloDisponivel['nome']) ?></strong>
                                        <span class="module-picker-meta">
                                            <?= htmlspecialchars($moduloDisponivel['semestre'] ?: '-') ?>
                                            · <?= htmlspecialchars($moduloDisponivel['categoria']) ?>
                                        </span>
                                    </div>
                                    <form method="post" action="index.php">
                                        <input type="hidden" name="acao_modulo" value="adicionar">
                                        <input type="hidden" name="codModulo" value="<?= (int) $moduloDisponivel['codigo'] ?>">
                                        <input type="hidden" name="codFormando" value="<?= $codFormandoSelecionado ?: '' ?>">
                                        <input type="hidden" name="codCampo" value="<?= $codCampoModulo ?: '' ?>">
                                        <input type="hidden" name="idQualiNivel" value="<?= $idQualiNivelSelecionado ?: '' ?>">
                                        <input type="hidden" name="codTurma" value="<?= $codTurmaSelecionada ?: '' ?>">
                                        <input type="hidden" name="data_inscricao" value="<?= htmlspecialchars($dataModulo) ?>">
                                        <input type="hidden" name="filtroSemestre" value="<?= htmlspecialchars($semestreModulo !== '' ? $semestreModulo : 'todos') ?>">
                                        <input type="hidden" name="filtroTipo" value="<?= htmlspecialchars($tipoModuloFiltro !== '' ? $tipoModuloFiltro : 'todos') ?>">
                                        <input type="hidden" name="pesquisaModulo" value="<?= htmlspecialchars($pesquisaModulo) ?>">
                                        <?php foreach ($modulosSelecionados as $moduloSelecionado): ?>
                                            <input type="hidden" name="selecionados[]" value="<?= (int) $moduloSelecionado->getCodigo() ?>">
                                        <?php endforeach; ?>
                                        <button type="submit" class="btn btn-sm btn-primary">Adicionar</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <h4 class="module-section-title">Módulos selecionados (<?= $totalModulosSelecionados ?>)</h4>
                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th class="col-codigo">Código</th>
                                    <th>Nome</th>
                                    <th>Semestre</th>
                                    <th>Categoria</th>
                                    <th class="col-opcoes">Opções</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($modulosSelecionados)): ?>
                                    <tr>
                                        <td colspan="5" class="table-empty">
                                            Ainda não há módulos selecionados. Pesquise e clique em «Adicionar».
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($modulosSelecionados as $moduloSelecionado): ?>
                                        <tr>
                                            <td><strong><?= (int) $moduloSelecionado->getCodigo() ?></strong></td>
                                            <td><?= htmlspecialchars($moduloSelecionado->getNome()) ?></td>
                                            <td><?= htmlspecialchars(
                                                    $moduloSelecionado->getQualiModulo() !== null
                                                        ? $moduloSelecionado->getQualiModulo()->getSemestre()
                                                        : '-'
                                                ) ?></td>
                                            <td><?= htmlspecialchars($moduloSelecionado->getCategoria()) ?></td>
                                            <td class="col-opcoes">
                                                <form method="post" action="index.php" class="table-actions table-actions-end">
                                                    <input type="hidden" name="acao_modulo" value="remover">
                                                    <input type="hidden" name="codModulo" value="<?= (int) $moduloSelecionado->getCodigo() ?>">
                                                    <input type="hidden" name="codFormando" value="<?= $codFormandoSelecionado ?: '' ?>">
                                                    <input type="hidden" name="codCampo" value="<?= $codCampoModulo ?: '' ?>">
                                                    <input type="hidden" name="idQualiNivel" value="<?= $idQualiNivelSelecionado ?: '' ?>">
                                                    <input type="hidden" name="codTurma" value="<?= $codTurmaSelecionada ?: '' ?>">
                                                    <input type="hidden" name="data_inscricao" value="<?= htmlspecialchars($dataModulo) ?>">
                                                    <input type="hidden" name="filtroSemestre" value="<?= htmlspecialchars($semestreModulo !== '' ? $semestreModulo : 'todos') ?>">
                                                    <input type="hidden" name="filtroTipo" value="<?= htmlspecialchars($tipoModuloFiltro !== '' ? $tipoModuloFiltro : 'todos') ?>">
                                                    <input type="hidden" name="pesquisaModulo" value="<?= htmlspecialchars($pesquisaModulo) ?>">
                                                    <?php foreach ($modulosSelecionados as $moduloParaManter): ?>
                                                        <input type="hidden" name="selecionados[]" value="<?= (int) $moduloParaManter->getCodigo() ?>">
                                                    <?php endforeach; ?>
                                                    <button type="submit" class="btn btn-sm btn-delete">Remover</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="<?= htmlspecialchars($urlVoltarFormulario) ?>" class="btn btn-secondary">Voltar ao formulário</a>
                    <a href="<?= htmlspecialchars($urlVoltarFormulario) ?>" class="btn btn-success">Confirmar seleção</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>
