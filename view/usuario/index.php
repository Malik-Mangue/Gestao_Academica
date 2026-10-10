<?php
require_once __DIR__ . '/../../services/Sessao.php';

// Gestao de utilizadores: guarda por recurso (por defeito, exclusiva do
// Administrador). A view esconde os controlos, mas o guard protege o acesso direto.
Sessao::exigirLeitura(Sessao::RECURSO_UTILIZADORES, '../dashboard/index.php');

require_once __DIR__ . '/../../services/Validador.php';
require_once __DIR__ . '/../../controller/UsuarioController.php';

$controller = new UsuarioController();

$generos       = ['Masculino', 'Feminino', 'Outro'];
$estados_civil = ['Solteiro(a)', 'Casado(a)', 'União estável', 'Divorciado(a)', 'Viúvo(a)'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['gravar'])) {
        $nome = trim($_POST['nome'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $apelido = trim($_POST['apelido'] ?? '');
        $estadoCivil = trim($_POST['estadoCivil'] ?? '');
        $genero = trim($_POST['genero'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $bi = trim($_POST['bi'] ?? '');
        $idPerfil = filter_input(INPUT_POST, 'idPerfil', FILTER_VALIDATE_INT);

        if ($nome === '' || $username === '' || $apelido === '' || $estadoCivil === ''
            || $genero === '' || $telefone === '' || $email === '' || $bi === '' || !$idPerfil) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Todos os campos são obrigatórios.'];
        } elseif (!in_array($estadoCivil, $estados_civil, true) || !in_array($genero, $generos, true)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Estado civil ou género inválidos.'];
        } elseif (mb_strlen($nome) > 100 || mb_strlen($username) > 60 || mb_strlen($apelido) > 60
            || mb_strlen($estadoCivil) > 40 || mb_strlen($genero) > 20 || mb_strlen($telefone) > 20
            || mb_strlen($email) > 100 || mb_strlen($bi) > 20) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Não foi possível gravar o registo. Verifique os tamanhos dos campos.'];
        } elseif (!Validador::email($email)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'O e-mail indicado não é válido.'];
        } elseif (!Validador::digitos($telefone, 20)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'O telefone deve conter apenas dígitos.'];
        } else {
            try {
                $usuario = new Usuario(
                    null,
                    $idPerfil,
                    $nome,
                    $username,
                    $apelido,
                    null,
                    1,
                    $estadoCivil,
                    $genero,
                    $telefone,
                    $email,
                    $bi
                );
                $usuario->setPerfil(new Perfil($idPerfil, null));
                $sucesso = $controller->store($usuario);
                $_SESSION['flash'] = $sucesso
                    ? ['type' => 'success', 'msg' => 'Utilizador cadastrado com sucesso!']
                    : ['type' => 'danger', 'msg' => 'Não foi possível gravar o registo. O username poderá já existir.'];
            } catch (Exception $e) {
                $_SESSION['flash'] = ['type' => 'danger', 'msg' => $e->getMessage()];
            }
        }
        header('Location: index.php');
        exit;
    }

    if (isset($_POST['editar'])) {
        $codigo = filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT);
        $nome = trim($_POST['nome'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $apelido = trim($_POST['apelido'] ?? '');
        $estadoCivil = trim($_POST['estadoCivil'] ?? '');
        $genero = trim($_POST['genero'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $bi = trim($_POST['bi'] ?? '');
        $idPerfil = filter_input(INPUT_POST, 'idPerfil', FILTER_VALIDATE_INT);

        if (!$codigo || $nome === '' || $username === '' || $apelido === '' || $estadoCivil === ''
            || $genero === '' || $telefone === '' || $email === '' || $bi === '' || !$idPerfil) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Dados inválidos para atualização.'];
        } elseif (!in_array($estadoCivil, $estados_civil, true) || !in_array($genero, $generos, true)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Estado civil ou género inválidos.'];
        } elseif (mb_strlen($nome) > 100 || mb_strlen($username) > 60 || mb_strlen($apelido) > 60
            || mb_strlen($estadoCivil) > 40 || mb_strlen($genero) > 20 || mb_strlen($telefone) > 20
            || mb_strlen($email) > 100 || mb_strlen($bi) > 20) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Não foi possível atualizar o registo. Verifique os tamanhos dos campos.'];
        } elseif (!Validador::email($email)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'O e-mail indicado não é válido.'];
        } elseif (!Validador::digitos($telefone, 20)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'O telefone deve conter apenas dígitos.'];
        } else {
            try {
                $sucesso = $controller->update($codigo);
                $_SESSION['flash'] = $sucesso
                    ? ['type' => 'success', 'msg' => 'Utilizador atualizado com sucesso!']
                    : ['type' => 'danger', 'msg' => 'Não foi possível atualizar o registo.'];
            } catch (Exception $e) {
                $_SESSION['flash'] = ['type' => 'danger', 'msg' => $e->getMessage()];
            }
        }
        header('Location: index.php');
        exit;
    }

    if (isset($_POST['resetar'])) {
        $codigo = filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT);
        if (!$codigo) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Código inválido para redefinição de senha.'];
        } elseif ($controller->resetSenha($codigo)) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'msg'  => 'Senha redefinida para "' . UsuarioController::SENHA_PADRAO . '". '
                    . 'O utilizador deverá trocá-la no próximo acesso.'
            ];
        } else {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Não foi possível redefinir a senha.'];
        }
        header('Location: index.php');
        exit;
    }
    if (isset($_POST['deletar'])) {

        $codigo = filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT);
        if (!$codigo) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'Código inválido para remoção.'
            ];
        } elseif ($controller->delete($codigo)) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'msg' => 'Usuario removido com sucesso!'
            ];
        } else {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg' => 'Não foi possível eliminar o registo.'
            ];
        }
        header('Location: index.php');
        exit;
    }
}

$pesquisa = trim($_GET['pesquisa'] ?? '');
$usuarios = $controller->listar($pesquisa !== '' ? $pesquisa : null);
$perfis = $controller->listarPerfis();

// Perfis existentes rastreados na base de dados através da classe Perfil
$nomesPerfil = [];
foreach ($perfis as $perfil) {
    $nomesPerfil[(int) $perfil->getId()] = $perfil->getNome();
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$page_title = 'Gestão de Utilizadores';
$active_menu = 'usuario';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<main class="main-wrapper">
    <div class="content-header">
        <h1>Gestão de Utilizadores</h1>
        <div class="breadcrumb">
            <a href="../../view/dashboard/index.php">Home</a>
            <span class="divider">/</span>
            <span>Utilizadores</span>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <span><?= htmlspecialchars($flash['msg']) ?></span>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Listagem de Utilizadores</h2>
            <a href="#modal-novo" class="btn btn-primary">+ Novo Utilizador</a>
        </div>
        <div class="card-body">
            <form method="get" action="index.php" class="form-actions">
                <div class="form-group">
                    <label for="pesquisa">Pesquisar</label>
                    <input type="text" id="pesquisa" name="pesquisa" class="form-control"
                           maxlength="100" placeholder="Nome, username, e-mail, telefone, BI ou perfil"
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
                            <th>Username</th>
                            <th>Estado Civil</th>
                            <th>Género</th>
                            <th>Telefone</th>
                            <th>E-mail</th>
                            <th>BI</th>
                            <th>Perfil</th>
                            <th class="col-opcoes">Opções</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="10" class="table-empty">Nenhum utilizador encontrado.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td class="col-codigo">#<?= htmlspecialchars($usuario->getCodigo()) ?></td>
                                    <td><?= htmlspecialchars($usuario->getNome() . ' ' . $usuario->getApelido()) ?></td>
                                    <td><?= htmlspecialchars($usuario->getUsername()) ?></td>
                                    <td><?= htmlspecialchars($usuario->getEstadoCivil() ?: '—') ?></td>
                                    <td><?= htmlspecialchars($usuario->getGenero() ?: '—') ?></td>
                                    <td><?= htmlspecialchars($usuario->getTelefone() ?: '—') ?></td>
                                    <td><?= htmlspecialchars($usuario->getEmail() ?: '—') ?></td>
                                    <td><?= htmlspecialchars($usuario->getBi() ?: '—') ?></td>
                                    <td><?= htmlspecialchars($nomesPerfil[(int) $usuario->getIdPerfil()] ?? '—') ?></td>
                                    <td class="col-opcoes">
                                        <div class="table-actions table-actions-end">
                                            <a href="#modal-editar-<?= (int) $usuario->getCodigo() ?>" class="btn btn-sm btn-edit">Editar</a>
                                            <a href="#modal-deletar-<?= (int) $usuario->getCodigo() ?>" class="btn btn-sm btn-delete">Remover</a>
                                            <a href="#modal-reset-<?= (int) $usuario->getCodigo() ?>" class="btn btn-reset">Reset senha</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php if (!empty($usuarios)): ?>
        <?php foreach ($usuarios as $usuario): ?>
            <div id="modal-reset-<?= (int) $usuario->getCodigo() ?>" class="modal-overlay">
                <div class="modal-box">
                    <div class="modal-header">
                        <h3>Redefinir Senha</h3>
                        <a href="#" class="modal-close">
                            <img src="../../assets/icons/close.svg" alt="Fechar">
                        </a>
                    </div>
                    <form method="post" action="index.php">
                        <div class="modal-body">
                            <div class="confirm-question">
                                <p>Redefinir a senha do utilizador?</p>
                            </div>
                            <div class="confirm-detail-box">
                                <p><strong>Nome:</strong> <?= htmlspecialchars($usuario->getNome() . ' ' . $usuario->getApelido()) ?></p>
                                <p><strong>Username:</strong> <?= htmlspecialchars($usuario->getUsername()) ?></p>
                                <p><strong>Perfil:</strong> <?= htmlspecialchars($nomesPerfil[(int) $usuario->getIdPerfil()] ?? '—') ?></p>
                            </div>
                            <p class="confirm-warning">
                                A senha passará a ser "<?= htmlspecialchars(UsuarioController::SENHA_PADRAO) ?>" e ficará com primeiro acesso obrigatório.
                            </p>
                            <input type="hidden" name="codigo" value="<?= (int) $usuario->getCodigo() ?>">
                        </div>
                        <div class="modal-footer">
                            <a href="#" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" name="resetar" class="btn btn-reset">Sim, Resetar Senha</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        <?php foreach ($usuarios as $usuario): ?>
            <div id="modal-editar-<?= (int) $usuario->getCodigo() ?>" class="modal-overlay">
                <div class="modal-box">
                    <div class="modal-header">
                        <h3>Editar Utilizador #<?= (int) $usuario->getCodigo() ?></h3>
                        <a href="#" class="modal-close">
                            <img src="../../assets/icons/close.svg" alt="Fechar">
                        </a>
                    </div>
                    <form method="post" action="index.php">
                        <div class="modal-body">
                            <input type="hidden" name="codigo" value="<?= (int) $usuario->getCodigo() ?>">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="edit-nome-<?= (int) $usuario->getCodigo() ?>">Nome <span class="required">*</span></label>
                                    <input type="text" id="edit-nome-<?= (int) $usuario->getCodigo() ?>" name="nome" class="form-control"
                                        maxlength="100" value="<?= htmlspecialchars($usuario->getNome()) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit-apelido-<?= (int) $usuario->getCodigo() ?>">Apelido <span class="required">*</span></label>
                                    <input type="text" id="edit-apelido-<?= (int) $usuario->getCodigo() ?>" name="apelido" class="form-control"
                                        maxlength="60" value="<?= htmlspecialchars($usuario->getApelido()) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit-username-<?= (int) $usuario->getCodigo() ?>">Username <span class="required">*</span></label>
                                    <input type="text" id="edit-username-<?= (int) $usuario->getCodigo() ?>" name="username" class="form-control"
                                        maxlength="60" autocomplete="username" value="<?= htmlspecialchars($usuario->getUsername()) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit-estadoCivil-<?= (int) $usuario->getCodigo() ?>">Estado Civil <span class="required">*</span></label>
                                    <select id="edit-estadoCivil-<?= (int) $usuario->getCodigo() ?>" name="estadoCivil" class="form-control" required>
                                        <?php foreach ($estados_civil as $estado): ?>
                                            <option value="<?= htmlspecialchars($estado) ?>"
                                                <?= ($usuario->getEstadoCivil() === $estado) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($estado) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="edit-genero-<?= (int) $usuario->getCodigo() ?>">Género <span class="required">*</span></label>
                                    <select id="edit-genero-<?= (int) $usuario->getCodigo() ?>" name="genero" class="form-control" required>
                                        <?php foreach ($generos as $genero): ?>
                                            <option value="<?= htmlspecialchars($genero) ?>"
                                                <?= ($usuario->getGenero() === $genero) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($genero) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="edit-telefone-<?= (int) $usuario->getCodigo() ?>">Telefone <span class="required">*</span></label>
                                    <input type="text" id="edit-telefone-<?= (int) $usuario->getCodigo() ?>" name="telefone" class="form-control"
                                        maxlength="20" value="<?= htmlspecialchars($usuario->getTelefone() ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit-email-<?= (int) $usuario->getCodigo() ?>">E-mail <span class="required">*</span></label>
                                    <input type="email" id="edit-email-<?= (int) $usuario->getCodigo() ?>" name="email" class="form-control"
                                        maxlength="100" value="<?= htmlspecialchars($usuario->getEmail() ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit-bi-<?= (int) $usuario->getCodigo() ?>">Nº do BI <span class="required">*</span></label>
                                    <input type="text" id="edit-bi-<?= (int) $usuario->getCodigo() ?>" name="bi" class="form-control"
                                        maxlength="20" value="<?= htmlspecialchars($usuario->getBi() ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit-perfil-<?= (int) $usuario->getCodigo() ?>">Perfil <span class="required">*</span></label>
                                    <select id="edit-perfil-<?= (int) $usuario->getCodigo() ?>" name="idPerfil" class="form-control" required>
                                        <?php foreach ($perfis as $perfil): ?>
                                            <option value="<?= (int) $perfil->getId() ?>"
                                                <?= ((int) $usuario->getIdPerfil() === (int) $perfil->getId()) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($perfil->getNome()) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <p class="confirm-warning">
                                Preencher a password repõe o primeiro acesso obrigatório: o utilizador
                                terá de a trocar no próximo login.
                            </p>
                        </div>
                        <div class="modal-footer">
                            <a href="#" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" name="editar" class="btn btn-success">Salvar Alterações</button>
                        </div>
                    </form>
                </div>
            </div>
            <div id="modal-deletar-<?= $usuario->getCodigo() ?>" class="modal-overlay">
                <div class="modal-box">
                    <div class="modal-header modal-header-danger">
                        <h3>Confirmar Exclusão de Registro</h3>
                        <a href="#" class="modal-close">
                            <img src="../../assets/icons/close.svg" alt="Fechar">
                        </a>
                    </div>
                    <form method="post" action="index.php">
                        <div class="modal-body">
                            <input type="hidden" name="codigo" value="<?= htmlspecialchars($usuario->getCodigo()) ?>">
                            <p class="confirm-question">
                                Tem certeza que deseja eliminar permanentemente este registro?
                            </p>
                            <div class="confirm-detail-box">
                                <p>
                                    <strong>Código:</strong> #<?= htmlspecialchars($usuario->getCodigo()) ?>
                                </p>
                                <p>
                                    <strong>Nome:</strong> <?= htmlspecialchars($usuario->getNome()) ?>
                                </p>
                                <p>
                                    <strong>Username:</strong> <?= htmlspecialchars($usuario->getUsername()) ?>
                                </p>
                                <p>
                                    <strong>Perfil:</strong> <?= htmlspecialchars($nomesPerfil[(int) $usuario->getIdPerfil()]) ?>
                                </p>
                            </div>
                            <p class="confirm-warning">
                                Atenção: Esta ação não poderá ser desfeita.
                            </p>
                        </div>
                        <div class="modal-footer">
                            <a href="#" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" name="deletar" class="btn btn-delete">Sim, Deletar</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div id="modal-novo" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Cadastrar Novo Utilizador</h3>
                <a href="#" class="modal-close">
                    <img src="../../assets/icons/close.svg" alt="Fechar">
                </a>
            </div>
            <form method="post" action="index.php">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="novo-nome">Nome <span class="required">*</span></label>
                            <input type="text" id="novo-nome" name="nome" class="form-control"
                                maxlength="100" required>
                        </div>
                        <div class="form-group">
                            <label for="novo-apelido">Apelido <span class="required">*</span></label>
                            <input type="text" id="novo-apelido" name="apelido" class="form-control"
                                maxlength="60" required>
                        </div>
                        <div class="form-group">
                            <label for="novo-username">Username <span class="required">*</span></label>
                            <input type="text" id="novo-username" name="username" class="form-control"
                                maxlength="60" autocomplete="username" required>
                        </div>
                        <!-- <div class="form-group">
                            <label for="novo-password">Password <span class="required">*</span></label>
                            <input type="password" id="novo-password" name="password" class="form-control"
                                   maxlength="60" autocomplete="new-password" required>
                        </div> -->
                        <div class="form-group">
                            <label for="novo-estadoCivil">Estado Civil <span class="required">*</span></label>
                            <select id="novo-estadoCivil" name="estadoCivil" class="form-control" required>
                                <?php foreach ($estados_civil as $estado): ?>
                                    <option value="<?= htmlspecialchars($estado) ?>"><?= htmlspecialchars($estado) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="novo-genero">Género <span class="required">*</span></label>
                            <select id="novo-genero" name="genero" class="form-control" required>
                                <?php foreach ($generos as $genero): ?>
                                    <option value="<?= htmlspecialchars($genero) ?>"><?= htmlspecialchars($genero) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="novo-telefone">Telefone <span class="required">*</span></label>
                            <input type="text" id="novo-telefone" name="telefone" class="form-control"
                                maxlength="9" placeholder="Ex: 834323726" required>
                        </div>
                        <div class="form-group">
                            <label for="novo-email">E-mail <span class="required">*</span></label>
                            <input type="email" id="novo-email" name="email" class="form-control"
                                maxlength="100" placeholder="Ex: nome@email.com" required>
                        </div>
                        <div class="form-group">
                            <label for="novo-bi">Nº do BI <span class="required">*</span></label>
                            <input type="text" id="novo-bi" name="bi" class="form-control"
                                maxlength="13" required>
                        </div>
                        <div class="form-group">
                            <label for="novo-perfil">Perfil <span class="required">*</span></label>
                            <select id="novo-perfil" name="idPerfil" class="form-control" required>
                                <?php foreach ($perfis as $perfil): ?>
                                    <option value="<?= (int) $perfil->getId() ?>">
                                        <?= htmlspecialchars($perfil->getNome()) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <p class="confirm-warning">
                        So admin cria
                    </p>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-secondary">Cancelar</a>
                    <button type="reset" class="btn btn-reset">Limpar</button>
                    <button type="submit" name="gravar" class="btn btn-success">Salvar Utilizador</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>