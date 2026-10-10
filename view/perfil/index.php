<?php
require_once __DIR__ . '/../../services/Sessao.php';

// Gestao de perfis: restricao por recurso (exclusiva do Administrador).
Sessao::exigirLeitura(Sessao::RECURSO_PERFIS, '../dashboard/index.php');

require_once __DIR__ . '/../../Dao/PerfilDao.php';

$perfilDao = new PerfilDao();

// Processamento de POST (unica acao disponivel nesta fase e a criacao).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gravar'])) {
    $nome = trim($_POST['nome'] ?? '');

    if ($nome === '') {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'O nome e obrigatorio.'];
    } elseif (mb_strlen($nome) > 60) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'O nome nao pode exceder 60 caracteres.'];
    } else {
        try {
            $sucesso = $perfilDao->create(new Perfil(null, $nome));
            $_SESSION['flash'] = $sucesso
                ? ['type' => 'success', 'msg' => 'Perfil cadastrado com sucesso!']
                : ['type' => 'danger', 'msg' => 'Nao foi possivel gravar o registo.'];
        } catch (Exception $e) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => $e->getMessage()];
        }
    }

    header('Location: index.php');
    exit;
}

$perfis = $perfilDao->getAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$page_title = 'Gestao de Perfis';
$active_menu = 'perfil';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>
<main class="main-wrapper">
    <div class="content-header">
        <h1>Gestao de Perfis</h1>
        <div class="breadcrumb">
            <a href="../../view/dashboard/index.php">Home</a>
            <span class="divider">/</span>
            <span>Perfis</span>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <span><?= htmlspecialchars($flash['msg']) ?></span>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Lista de Perfis</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th class="col-codigo">ID</th>
                            <th>Nome</th>
                            <th class="col-opcoes">Opcoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($perfis)): ?>
                            <tr>
                                <td colspan="3" class="table-empty">Nenhum perfil cadastrado no momento.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($perfis as $perfil): ?>
                                <tr>
                                    <td class="col-codigo"><?= (int) $perfil->getId() ?></td>
                                    <td><?= htmlspecialchars($perfil->getNome()) ?></td>
                                    <td class="col-opcoes">
                                        <div class="table-actions table-actions-end">
                                            <a href="../../view/perfil/visualizar.php?id=<?= (int) $perfil->getId() ?>"
                                               class="btn btn-sm btn-edit">Ver descricao</a>
                                            <a href="../../view/permissoes/index.php?perfil=<?= (int) $perfil->getId() ?>"
                                               class="btn btn-sm btn-edit">Permissoes</a>
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

    <?php if (Sessao::pode(Sessao::ACAO_CRIAR, Sessao::RECURSO_PERFIS)): ?>
        <div id="modal-novo" class="modal-overlay">
            <div class="modal-box">
                <div class="modal-header">
                    <h3>Cadastrar Novo Perfil</h3>
                    <a href="#" class="modal-close">
                        <img src="../../assets/icons/close.svg" alt="Fechar">
                    </a>
                </div>
                <form method="post" action="index.php">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="novo-nome">Nome <span class="required">*</span></label>
                            <input type="text" id="novo-nome" name="nome" class="form-control"
                                   maxlength="60" placeholder="Ex: Professor" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" class="btn btn-secondary">Cancelar</a>
                        <button type="reset" class="btn btn-reset">Limpar</button>
                        <button type="submit" name="gravar" class="btn btn-success">Salvar Perfil</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
