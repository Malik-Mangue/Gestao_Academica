<?php
require_once __DIR__ . '/../../services/Sessao.php';
Sessao::exigirLogin('../login/index.php');
require_once __DIR__ . '/../../services/Validador.php';
require_once __DIR__ . '/../../controller/FormadorController.php';

$controller = new FormadorController();

$generos       = ['Masculino', 'Feminino', 'Outro'];
$estados_civil = ['Solteiro(a)', 'Casado(a)', 'União estável', 'Divorciado(a)', 'Viúvo(a)'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // O controller revalida o perfil: a view esconde os botoes nao permitidos,
    // mas quem salta a interface continua a ser barrado aqui.
    if (isset($_POST['gravar'])) {
        Sessao::exigirAcao(Sessao::ACAO_CRIAR, 'index.php');

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

        if (!Validador::texto($nome, 40) || !Validador::texto($apelido, 40)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Nome e apelido são obrigatórios (até 40 caracteres).'];
        } elseif (!Validador::emailObrigatorio($email)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'O e-mail indicado não é válido.'];
        } elseif (!Validador::inteiroPositivo($contacto)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'O contacto deve ser um número positivo.'];
        } elseif (!Validador::inteiroPositivo($valorHora) || !Validador::inteiroPositivo($horasMes)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Valor por hora e horas por mês devem ser positivos.'];
        } else {
            $sucesso = $controller->cadastrarFormador(
                $nome, $apelido, $email, $genero, $estadoCivil,
                (int) $contacto, (int) $valorHora, (int) $horasMes,
                (int) $salario, $isDiretor, $isCoordenador
            );
            $_SESSION['flash'] = $sucesso
                ? ['type' => 'success', 'msg' => 'Formador cadastrado com sucesso!']
                : ['type' => 'danger', 'msg' => 'Não foi possível gravar o registo. Verifique os dados.'];
        }
        header('Location: index.php');
        exit;
    }

    if (isset($_POST['editar'])) {
        Sessao::exigirAcao(Sessao::ACAO_EDITAR, 'index.php');

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

        if (!$codigo || !Validador::texto($nome, 40) || !Validador::texto($apelido, 40)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Dados inválidos para atualização do formador.'];
        } elseif (!Validador::emailObrigatorio($email)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'O e-mail indicado não é válido.'];
        } elseif (!Validador::inteiroPositivo($contacto)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'O contacto deve ser um número positivo.'];
        } else {
            try {
                $sucesso = $controller->atualizarFormador(
                    $codigo, $nome, $apelido, $email, $genero, $estadoCivil,
                    (int) $contacto, (int) $valorHora, (int) $horasMes,
                    (int) $salario, $isDiretor, $isCoordenador
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
        Sessao::exigirAcao(Sessao::ACAO_REMOVER, 'index.php');

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

$formadores = $controller->listar();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pode_criar   = Sessao::pode(Sessao::ACAO_CRIAR);
$pode_editar  = Sessao::pode(Sessao::ACAO_EDITAR);
$pode_remover = Sessao::pode(Sessao::ACAO_REMOVER);

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
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th class="col-codigo">Código</th>
                            <th>Nome</th>
                            <th>Apelido</th>
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
                                <td colspan="<?= $pode_editar || $pode_remover ? 11 : 10 ?>" class="table-empty">
                                    Nenhum formador cadastrado no momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($formadores as $formador): ?>
                                <?php $status = $controller->getStatusFormador($formador->getCodigo()); ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($formador->getCodigo()) ?></strong></td>
                                    <td><?= htmlspecialchars($formador->getNome()) ?></td>
                                    <td><?= htmlspecialchars($formador->getApelido()) ?></td>
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
                                                    $acao    = 'Salvar Alterações';
                                                    require __DIR__ . '/_campos.php';
                                                    ?>
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
                    $acao   = 'Salvar Formador';
                    require __DIR__ . '/_campos.php';
                    ?>
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
