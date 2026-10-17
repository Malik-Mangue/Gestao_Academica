<?php
require_once __DIR__ . '/../../services/Sessao.php';
Sessao::exigirLogin('../login/index.php');
require_once __DIR__ . '/../../controller/FormadorController.php';
require_once __DIR__ . '/../../controller/CampoController.php';

$controller = new FormadorController();
$campoController = new CampoController();
$campos = $campoController->listarCampo();

$generos       = ['Masculino', 'Feminino', 'Outro'];
$estados_civil = ['Solteiro(a)', 'Casado(a)', 'União estável', 'Divorciado(a)', 'Viúvo(a)'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['gravar'])) {
        Sessao::exigirAcao(Sessao::ACAO_CRIAR, Sessao::RECURSO_FORMADORES, 'index.php');

        $nome         = trim($_POST['nome'] ?? '');
        $apelido      = trim($_POST['apelido'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $genero       = trim($_POST['genero'] ?? '');
        $estadoCivil  = trim($_POST['estadoCivil'] ?? '');
        $contacto     = $_POST['contacto'] ?? '';
        $valorHora    = $_POST['valorHora'] ?? '';
        $horasMes     = $_POST['horasMes'] ?? '';
        $salario      = $_POST['salario'] ?? '';
        $isDiretor    = isset($_POST['isDiretor']);
        $isCoordenador = isset($_POST['isCoordenador']);
        $codCampo     = filter_input(INPUT_POST, 'codCampo', FILTER_VALIDATE_INT);

        if (!$controller->texto($nome, 40) || !$controller->texto($apelido, 40)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Nome e apelido são obrigatórios (até 40 caracteres).'];
        } elseif (!$controller->emailObrigatorio($email)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'O e-mail indicado não é válido.'];
        } elseif (!$controller->inteiroPositivo($contacto)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'O contacto deve ser um número positivo.'];
        } elseif (!$controller->inteiroPositivo($valorHora) || !$controller->inteiroPositivo($horasMes)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Valor por hora e horas por mês devem ser positivos.'];
        } elseif (!$codCampo) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione o campo (área) do formador.'];
        } else {
            $sucesso = $controller->cadastrarFormador(
                $nome, $apelido, $email, $genero, $estadoCivil,
                (int) $contacto, (int) $valorHora, (int) $horasMes,
                (int) $salario, $isDiretor, $isCoordenador, (int) $codCampo
            );
            $_SESSION['flash'] = $sucesso
                ? ['type' => 'success', 'msg' => 'Formador cadastrado com sucesso!']
                : ['type' => 'danger', 'msg' => 'Não foi possível gravar o registo. Verifique os dados.'];
        }
        header('Location: index.php');
        exit;
    }

    if (isset($_POST['editar'])) {
        Sessao::exigirAcao(Sessao::ACAO_EDITAR, Sessao::RECURSO_FORMADORES, 'index.php');

        $codigo        = filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT);
        $nome          = trim($_POST['nome'] ?? '');
        $apelido       = trim($_POST['apelido'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $genero        = trim($_POST['genero'] ?? '');
        $estadoCivil   = trim($_POST['estadoCivil'] ?? '');
        $contacto      = $_POST['contacto'] ?? '';
        $valorHora     = $_POST['valorHora'] ?? '';
        $horasMes      = $_POST['horasMes'] ?? '';
        $salario       = $_POST['salario'] ?? '';
        $isDiretor     = isset($_POST['isDiretor']);
        $isCoordenador = isset($_POST['isCoordenador']);
        $codCampo      = filter_input(INPUT_POST, 'codCampo', FILTER_VALIDATE_INT);

        if (!$codigo || !$controller->texto($nome, 40) || !$controller->texto($apelido, 40)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Dados inválidos para atualização do formador.'];
        } elseif (!$controller->emailObrigatorio($email)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'O e-mail indicado não é válido.'];
        } elseif (!$controller->inteiroPositivo($contacto)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'O contacto deve ser um número positivo.'];
        } elseif (!$codCampo) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Selecione o campo (área) do formador.'];
        } else {
            try {
                $sucesso = $controller->atualizarFormador(
                    $codigo, $nome, $apelido, $email, $genero, $estadoCivil,
                    (int) $contacto, (int) $valorHora, (int) $horasMes,
                    (int) $salario, $isDiretor, $isCoordenador, (int) $codCampo
                );
                $_SESSION['flash'] = $sucesso
                    ? ['type' => 'success', 'msg' => 'Formador atualizado com sucesso!']
                    : ['type' => 'danger', 'msg' => 'Não foi possível atualizar o registo.'];
            } catch (Exception $e) {
                $_SESSION['flash'] = ['type' => 'danger', 'msg' => $e->getMessage()];
            }
        }
        header('Location: index.php');
        exit;
    }

    if (isset($_POST['deletar'])) {
        Sessao::exigirAcao(Sessao::ACAO_REMOVER, Sessao::RECURSO_FORMADORES, 'index.php');

        $codigo = filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT);
        if (!$codigo) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Código inválido para remoção.'];
        } else {
            try {
                $sucesso = $controller->apagarFormador($codigo);
                $_SESSION['flash'] = $sucesso
                    ? ['type' => 'success', 'msg' => 'Formador removido com sucesso!']
                    : ['type' => 'danger', 'msg' => 'Não foi possível eliminar o registo.'];
            } catch (Exception $e) {
                $_SESSION['flash'] = ['type' => 'danger', 'msg' => $e->getMessage()];
            }
        }
        header('Location: index.php');
        exit;
    }
}

$pesquisa = trim($_GET['pesquisa'] ?? '');
$formadores = $controller->listar($pesquisa !== '' ? $pesquisa : null);
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pode_criar   = Sessao::podeCriar(Sessao::RECURSO_FORMADORES);
$pode_editar  = Sessao::podeEditar(Sessao::RECURSO_FORMADORES);
$pode_remover = Sessao::podeRemover(Sessao::RECURSO_FORMADORES);

$modaisDetalhes = '';
$url_turmas         = '../turma/index.php';
$url_qualificacoes  = '../qualificacao/index.php';
$page_title = 'Gestão de Formadores';
$active_menu = 'professor';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<main class="main-wrapper">
    <div class="content-header">
        <h1>Formadores</h1>
        <div class="breadcrumb">
            <a href="../../view/dashboard/index.php">Home</a>
            <span class="divider">/</span>
            <span>Formadores</span>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <span><?= htmlspecialchars($flash['msg']) ?></span>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Listagem de Formadores</h2>
            <?php if ($pode_criar): ?>
                <a href="#modal-novo" class="btn btn-primary">+ Novo Formador</a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="get" action="index.php" class="form-actions">
                <div class="form-group">
                    <label for="pesquisa">Pesquisar</label>
                    <input type="text" id="pesquisa" name="pesquisa" class="form-control"
                           maxlength="100" placeholder="Nome, apelido, e-mail, género, estado civil ou contacto"
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
                            <th>Nome</th>
                            <th>Apelido</th>
                            <th>Campo</th>
                            <th>E-mail</th>
                            <th>Género</th>
                            <th>Estado Civil</th>
                            <th>Contacto</th>
                            <th>Valor/Hora</th>
                            <th>Horas/Mês</th>
                            <th>Salário</th>
                            <?php if ($pode_editar || $pode_remover): ?>
                                <th class="col-opcoes">Opções</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($formadores)): ?>
                            <tr>
                                <td colspan="<?= $pode_editar || $pode_remover ? 12 : 11 ?>" class="table-empty">
                                    Nenhum formador encontrado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($formadores as $formador): ?>
                                <?php $status = $controller->getStatusFormador($formador->getCodigo()); ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($formador->getCodigo()) ?></strong></td>
                                    <td><?= htmlspecialchars($formador->getNome()) ?></td>
                                    <td><?= htmlspecialchars($formador->getApelido()) ?></td>
                                    <td><?= htmlspecialchars($formador->getCampo() ? $formador->getCampo()->getNome() : '-') ?></td>
                                    <td><?= htmlspecialchars($formador->getEmail() ?: '-') ?></td>
                                    <td><?= htmlspecialchars($formador->getGenero() ?: '-') ?></td>
                                    <td><?= htmlspecialchars($formador->getEstadoCivil() ?: '-') ?></td>
                                    <td><?= htmlspecialchars($formador->getContacto()) ?></td>
                                    <td><?= htmlspecialchars($formador->getValorHoras()) ?></td>
                                    <td><?= htmlspecialchars($formador->getHorasMes()) ?></td>
                                    <td><?= htmlspecialchars($formador->getSalario()) ?></td>
                                    <?php if ($pode_editar || $pode_remover): ?>
                                        <td class="col-opcoes">
                                            <div class="table-actions table-actions-end">
                                                <?php if ($status[0]): ?><span class="required">Diretor</span><?php endif; ?>
                                                <?php if ($status[1]): ?><span class="required">Coordenador</span><?php endif; ?>
                                                <a href="#modal-detalhes-<?= $formador->getCodigo() ?>" class="btn btn-sm btn-secondary">Ver detalhes</a>
                                                <?php if ($pode_editar): ?>
                                                    <a href="#modal-editar-<?= $formador->getCodigo() ?>" class="btn btn-sm btn-edit">Editar</a>
                                                <?php endif; ?>
                                                <?php if ($pode_remover): ?>
                                                    <a href="#modal-deletar-<?= $formador->getCodigo() ?>" class="btn btn-sm btn-delete">Remover</a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>

                                <?php
                                $cod = $formador->getCodigo();
                                $esc = function ($linha, $chave) {
                                    return htmlspecialchars((string) (($linha[$chave] ?? '') !== '' ? $linha[$chave] : '-'));
                                };
                                $turmasDirige = method_exists($controller, 'listarTurmasQueDirige')
                                    ? $controller->listarTurmasQueDirige($cod) : [];
                                $qualifsCoordena = method_exists($controller, 'listarQualificacoesQueCoordena')
                                    ? $controller->listarQualificacoesQueCoordena($cod) : [];
                                $modulosLeciona = method_exists($controller, 'listarModulosQueLeciona')
                                    ? $controller->listarModulosQueLeciona($cod) : [];
                                ob_start();
                                ?>
                                <div id="modal-detalhes-<?= $cod ?>" class="modal-overlay">
                                    <div class="modal-box" style="max-width:900px;width:95%;">
                                        <div class="modal-header">
                                            <h3>Detalhes do Formador #<?= htmlspecialchars($cod) ?></h3>
                                            <a href="#" class="modal-close">
                                                <img src="../../assets/icons/close.svg" alt="Fechar">
                                            </a>
                                        </div>
                                        <div class="modal-body" style="max-height:70vh;overflow-y:auto;">

                                            <h4>Dados Pessoais</h4>
                                            <div class="confirm-detail-box">
                                                <p><strong>Nome:</strong> <?= htmlspecialchars($formador->getNome() . ' ' . $formador->getApelido()) ?></p>
                                                <p><strong>Campo:</strong> <?= htmlspecialchars($formador->getCampo() ? $formador->getCampo()->getNome() : '-') ?></p>
                                                <p><strong>E-mail:</strong> <?= htmlspecialchars($formador->getEmail() ?: '-') ?></p>
                                                <p><strong>Género:</strong> <?= htmlspecialchars($formador->getGenero() ?: '-') ?></p>
                                                <p><strong>Estado civil:</strong> <?= htmlspecialchars($formador->getEstadoCivil() ?: '-') ?></p>
                                                <p><strong>Contacto:</strong> <?= htmlspecialchars($formador->getContacto()) ?></p>
                                                <p><strong>Valor/Hora:</strong> <?= htmlspecialchars($formador->getValorHoras()) ?></p>
                                                <p><strong>Horas/Mês:</strong> <?= htmlspecialchars($formador->getHorasMes()) ?></p>
                                                <p><strong>Salário:</strong> <?= htmlspecialchars($formador->getSalario()) ?></p>
                                                <p><strong>Funções:</strong> Formador<?= $status[0] ? ', Diretor de Turma' : '' ?><?= $status[1] ? ', Coordenador' : '' ?></p>
                                            </div>

                                            <h4>Turmas que dirige</h4>
                                            <div class="table-responsive">
                                                <table class="table-custom">
                                                    <thead>
                                                        <tr>
                                                            <th>Turma</th>
                                                            <th>Qualificação</th>
                                                            <th>Ano letivo</th>
                                                            <th>Turno</th>
                                                            <th class="col-opcoes">Opções</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (empty($turmasDirige)): ?>
                                                            <tr><td colspan="5" class="table-empty">Não dirige nenhuma turma.</td></tr>
                                                        <?php else: ?>
                                                            <?php foreach ($turmasDirige as $t): ?>
                                                                <tr>
                                                                    <td><strong><?= $esc($t, 'turma') ?></strong></td>
                                                                    <td><?= $esc($t, 'qualificacao') ?></td>
                                                                    <td><?= $esc($t, 'ano_letivo') ?></td>
                                                                    <td><?= $esc($t, 'turno') ?></td>
                                                                    <td class="col-opcoes">
                                                                        <a href="<?= $url_turmas ?>?pesquisa=<?= urlencode((string) ($t['turma'] ?? '')) ?>" class="btn btn-sm btn-edit">Ver turma</a>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <h4>Qualificações que coordena</h4>
                                            <div class="table-responsive">
                                                <table class="table-custom">
                                                    <thead>
                                                        <tr>
                                                            <th>Qualificação</th>
                                                            <th>Campo</th>
                                                            <th>Nível</th>
                                                            <th class="col-opcoes">Opções</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (empty($qualifsCoordena)): ?>
                                                            <tr><td colspan="4" class="table-empty">Não coordena nenhuma qualificação.</td></tr>
                                                        <?php else: ?>
                                                            <?php foreach ($qualifsCoordena as $q): ?>
                                                                <tr>
                                                                    <td><strong><?= $esc($q, 'qualificacao') ?></strong></td>
                                                                    <td><?= $esc($q, 'campo') ?></td>
                                                                    <td><?= $esc($q, 'niveis') ?></td>
                                                                    <td class="col-opcoes">
                                                                        <a href="<?= $url_qualificacoes ?>?pesquisa=<?= urlencode((string) ($q['qualificacao'] ?? '')) ?>" class="btn btn-sm btn-edit">Ver qualificação</a>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <h4>Módulos que leciona</h4>
                                            <div class="table-responsive">
                                                <table class="table-custom">
                                                    <thead>
                                                        <tr>
                                                            <th>Módulo</th>
                                                            <th>Qualificação</th>
                                                            <th>Turmas</th>
                                                            <th>Carga horária</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (empty($modulosLeciona)): ?>
                                                            <tr><td colspan="4" class="table-empty">Não leciona nenhum módulo.</td></tr>
                                                        <?php else: ?>
                                                            <?php foreach ($modulosLeciona as $m): ?>
                                                                <tr>
                                                                    <td><strong><?= $esc($m, 'modulo') ?></strong></td>
                                                                    <td><?= $esc($m, 'qualificacao') ?></td>
                                                                    <td><?= $esc($m, 'turmas') ?></td>
                                                                    <td><?= $esc($m, 'carga_horaria') ?> h</td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>
                                        <div class="modal-footer">
                                            <a href="#" class="btn btn-secondary">Fechar</a>
                                        </div>
                                    </div>
                                </div>

                                <?php $modaisDetalhes .= ob_get_clean(); ?>

                                <?php if ($pode_editar): ?>
                                    <div id="modal-editar-<?= $formador->getCodigo() ?>" class="modal-overlay">
                                        <div class="modal-box">
                                            <div class="modal-header">
                                                <h3>Editar Formador #<?= htmlspecialchars($formador->getCodigo()) ?></h3>
                                                <a href="#" class="modal-close">
                                                    <img src="../../assets/icons/close.svg" alt="Fechar">
                                                </a>
                                            </div>
                                            <form method="post" action="index.php">
                                                <div class="modal-body">
                                                    <input type="hidden" name="codigo" value="<?= htmlspecialchars($formador->getCodigo()) ?>">
                                                    <?php
                                                    $sufixo  = 'editar-' . $formador->getCodigo();
                                                    $v = function ($getter) use ($formador) {
                                                        return $formador !== null ? (string) $formador->$getter() : '';
                                                    };
                                                    ?>
                                                    <div class="form-group">
                                                        <label for="f-campo-<?= $sufixo ?>">Campo <span class="required">*</span></label>
                                                        <select id="f-campo-<?= $sufixo ?>" name="codCampo" class="form-control" required>
                                                            <option value="">-- Selecionar campo --</option>
                                                            <?php foreach ($campos as $campo): ?>
                                                                <option value="<?= htmlspecialchars($campo->getCodigo()) ?>"
                                                                    <?= ($formador !== null && $formador->getCodCampo() == $campo->getCodigo()) ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($campo->getNome()) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="f-nome-<?= $sufixo ?>">Nome <span class="required">*</span></label>
                                                        <input type="text" id="f-nome-<?= $sufixo ?>" name="nome" class="form-control"
                                                               placeholder="Ex: João" maxlength="40"
                                                               value="<?= htmlspecialchars($v('getNome')) ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="f-apelido-<?= $sufixo ?>">Apelido <span class="required">*</span></label>
                                                        <input type="text" id="f-apelido-<?= $sufixo ?>" name="apelido" class="form-control"
                                                               placeholder="Ex: Silva" maxlength="40"
                                                               value="<?= htmlspecialchars($v('getApelido')) ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="f-email-<?= $sufixo ?>">E-mail <span class="required">*</span></label>
                                                        <input type="email" id="f-email-<?= $sufixo ?>" name="email" class="form-control"
                                                               placeholder="Ex: joao.silva@email.com" maxlength="100"
                                                               value="<?= htmlspecialchars($v('getEmail')) ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="f-genero-<?= $sufixo ?>">Género</label>
                                                        <select id="f-genero-<?= $sufixo ?>" name="genero" class="form-control">
                                                            <?php foreach ($generos as $genero): ?>
                                                                <option value="<?= htmlspecialchars($genero) ?>"
                                                                    <?= ($formador !== null && $formador->getGenero() === $genero) ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($genero) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="f-estadoCivil-<?= $sufixo ?>">Estado Civil</label>
                                                        <select id="f-estadoCivil-<?= $sufixo ?>" name="estadoCivil" class="form-control">
                                                            <?php foreach ($estados_civil as $estado): ?>
                                                                <option value="<?= htmlspecialchars($estado) ?>"
                                                                    <?= ($formador !== null && $formador->getEstadoCivil() === $estado) ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($estado) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="f-contacto-<?= $sufixo ?>">Contacto <span class="required">*</span></label>
                                                        <input type="number" id="f-contacto-<?= $sufixo ?>" name="contacto" class="form-control"
                                                               min="1" placeholder="Ex: 912345678"
                                                               value="<?= htmlspecialchars($v('getContacto')) ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="f-valorHora-<?= $sufixo ?>">Valor por hora <span class="required">*</span></label>
                                                        <input type="number" id="f-valorHora-<?= $sufixo ?>" name="valorHora" class="form-control"
                                                               min="1" value="<?= htmlspecialchars($v('getValorHoras')) ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="f-horasMes-<?= $sufixo ?>">Horas por mês <span class="required">*</span></label>
                                                        <input type="number" id="f-horasMes-<?= $sufixo ?>" name="horasMes" class="form-control"
                                                               min="1" value="<?= htmlspecialchars($v('getHorasMes')) ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="f-funcao-<?= $sufixo ?>">Função</label>
                                                        <?php
                                                        $ehDiretor     = $formador !== null && $controller->getStatusFormador($formador->getCodigo())[0];
                                                        $ehCoordenador = $formador !== null && $controller->getStatusFormador($formador->getCodigo())[1];
                                                        ?>
                                                        <div class="form-actions">
                                                            <?php
                                                                  ?>
                                                            <label>
                                                                <input type="checkbox" name="isFormador" value="1" checked> Formador
                                                            </label>
                                                            <label>
                                                                <input type="checkbox" name="isDiretor" value="1"
                                                                    <?= $ehDiretor ? 'checked' : '' ?>> Diretor de Turma
                                                            </label>
                                                            <label>
                                                                <input type="checkbox" name="isCoordenador" value="1"
                                                                    <?= $ehCoordenador ? 'checked' : '' ?>> Coordenador
                                                            </label>
                                                        </div>
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
                                    <div id="modal-deletar-<?= $formador->getCodigo() ?>" class="modal-overlay">
                                        <div class="modal-box">
                                            <div class="modal-header modal-header-danger">
                                                <h3>Confirmar Exclusão de Registro</h3>
                                                <a href="#" class="modal-close">
                                                    <img src="../../assets/icons/close.svg" alt="Fechar">
                                                </a>
                                            </div>
                                            <form method="post" action="index.php">
                                                <div class="modal-body">
                                                    <input type="hidden" name="codigo" value="<?= htmlspecialchars($formador->getCodigo()) ?>">
                                                    <p class="confirm-question">
                                                        Tem certeza que deseja eliminar permanentemente este registro?
                                                    </p>
                                                    <div class="confirm-detail-box">
                                                        <p><strong>Código:</strong> #<?= htmlspecialchars($formador->getCodigo()) ?></p>
                                                        <p><strong>Nome:</strong> <?= htmlspecialchars($formador->getNome()) ?></p>
                                                        <p><strong>Apelido:</strong> <?= htmlspecialchars($formador->getApelido()) ?></p>
                                                        <p><strong>Contacto:</strong> <?= htmlspecialchars($formador->getContacto()) ?></p>
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

    <?= $modaisDetalhes ?>

    <?php if ($pode_criar): ?>
    <div id="modal-novo" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Cadastrar Novo Formador</h3>
                <a href="#" class="modal-close">
                    <img src="../../assets/icons/close.svg" alt="Fechar">
                </a>
            </div>
            <form method="post" action="index.php">
                <div class="modal-body">
                    <?php
                    $formador = null;
                    $sufixo = 'novo';
                    $v = function ($getter) use ($formador) {
                        return $formador !== null ? (string) $formador->$getter() : '';
                    };
                    ?>
                    <div class="form-group">
                        <label for="f-campo-<?= $sufixo ?>">Campo <span class="required">*</span></label>
                        <select id="f-campo-<?= $sufixo ?>" name="codCampo" class="form-control" required>
                            <option value="">-- Selecionar campo --</option>
                            <?php foreach ($campos as $campo): ?>
                                <option value="<?= htmlspecialchars($campo->getCodigo()) ?>">
                                    <?= htmlspecialchars($campo->getNome()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="f-nome-<?= $sufixo ?>">Nome <span class="required">*</span></label>
                        <input type="text" id="f-nome-<?= $sufixo ?>" name="nome" class="form-control"
                               placeholder="Ex: João" maxlength="40"
                               value="<?= htmlspecialchars($v('getNome')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="f-apelido-<?= $sufixo ?>">Apelido <span class="required">*</span></label>
                        <input type="text" id="f-apelido-<?= $sufixo ?>" name="apelido" class="form-control"
                               placeholder="Ex: Silva" maxlength="40"
                               value="<?= htmlspecialchars($v('getApelido')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="f-email-<?= $sufixo ?>">E-mail <span class="required">*</span></label>
                        <input type="email" id="f-email-<?= $sufixo ?>" name="email" class="form-control"
                               placeholder="Ex: joao.silva@email.com" maxlength="100"
                               value="<?= htmlspecialchars($v('getEmail')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="f-genero-<?= $sufixo ?>">Género</label>
                        <select id="f-genero-<?= $sufixo ?>" name="genero" class="form-control">
                            <?php foreach ($generos as $genero): ?>
                                <option value="<?= htmlspecialchars($genero) ?>"
                                    <?= ($formador !== null && $formador->getGenero() === $genero) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($genero) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="f-estadoCivil-<?= $sufixo ?>">Estado Civil</label>
                        <select id="f-estadoCivil-<?= $sufixo ?>" name="estadoCivil" class="form-control">
                            <?php foreach ($estados_civil as $estado): ?>
                                <option value="<?= htmlspecialchars($estado) ?>"
                                    <?= ($formador !== null && $formador->getEstadoCivil() === $estado) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($estado) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="f-contacto-<?= $sufixo ?>">Contacto <span class="required">*</span></label>
                        <input type="number" id="f-contacto-<?= $sufixo ?>" name="contacto" class="form-control"
                               min="1" placeholder="Ex: 912345678"
                               value="<?= htmlspecialchars($v('getContacto')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="f-valorHora-<?= $sufixo ?>">Valor por hora <span class="required">*</span></label>
                        <input type="number" id="f-valorHora-<?= $sufixo ?>" name="valorHora" class="form-control"
                               min="1" value="<?= htmlspecialchars($v('getValorHoras')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="f-horasMes-<?= $sufixo ?>">Horas por mês <span class="required">*</span></label>
                        <input type="number" id="f-horasMes-<?= $sufixo ?>" name="horasMes" class="form-control"
                               min="1" value="<?= htmlspecialchars($v('getHorasMes')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="f-funcao-<?= $sufixo ?>">Função</label>
                        <?php
                        $ehDiretor     = false;
                        $ehCoordenador = false;
                        ?>
                        <div class="form-actions">
                            <?php
                                  ?>
                            <label>
                                <input type="checkbox" name="isFormador" value="1" checked> Formador
                            </label>
                            <label>
                                <input type="checkbox" name="isDiretor" value="1"
                                    <?= $ehDiretor ? 'checked' : '' ?>> Diretor de Turma
                            </label>
                            <label>
                                <input type="checkbox" name="isCoordenador" value="1"
                                    <?= $ehCoordenador ? 'checked' : '' ?>> Coordenador
                            </label>
                        </div>
                    </div>
                </div>
                    <div class="modal-footer">
                        <a href="#" class="btn btn-secondary">Cancelar</a>
                        <button type="reset" class="btn btn-reset">Limpar</button>
                        <button type="submit" name="gravar" class="btn btn-success">Salvar Formador</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
