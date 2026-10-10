<?php
require_once __DIR__ . '/../../services/Sessao.php';

// Visualizacao de descricao de perfil. Esta seccao esta preparada para a
// logica futura do utilizador; atualmente mostra os dados do perfil e um
// espaco reservado para a descricao (ver docs/modelo_perfil_permissoes).
require_once __DIR__ . '/../../Dao/PerfilDao.php';
require_once __DIR__ . '/../../Dao/PerfilPermissaoDao.php';

$perfilDao = new PerfilDao();
$ppDao     = new PerfilPermissaoDao();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'ID de perfil invalido.'];
    header('Location: ../../view/perfil/index.php');
    exit;
}

$perfil = $perfilDao->getById($id);
if ($perfil === null) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Perfil nao encontrado.'];
    header('Location: ../../view/perfil/index.php');
    exit;
}

$concessoes = $ppDao->getConcessoesByPerfil($perfil->getId());

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$page_title = 'Descricao do Perfil';
$active_menu = 'perfil';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>
<main class="main-wrapper">
    <div class="content-header">
        <h1>Descricao do Perfil</h1>
        <div class="breadcrumb">
            <a href="../../view/dashboard/index.php">Home</a><span class="divider">/</span><span>Perfis</span>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <span><?= htmlspecialchars($flash['msg']) ?></span>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header"><h2>Identidade</h2></div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label for="perfil-id">ID</label>
                    <input type="text" id="perfil-id" class="form-control"
                           value="<?= (int) $perfil->getId() ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="perfil-nome">Nome</label>
                    <input type="text" id="perfil-nome" class="form-control"
                           value="<?= htmlspecialchars($perfil->getNome()) ?>" readonly>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h2>Descricao do Perfil</h2></div>
        <div class="card-body">
            <p class="note">
                Em breve: ecrano dedicado a mostrar toda a descricao do perfil
                (atributos, direitos e responsabilidades). A geracao do texto
                fica por preparar na construcao da parte de logica.
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h2>Permissoes associadas</h2></div>
        <div class="card-body">
            <?php if (empty($concessoes)): ?>
                <p class="note">
                    Sem permissoes explicitas cadastradas para este perfil.
                    O sistema aplica o padrao definido nos perfis legados.
                </p>
            <?php else: ?>
                <table class="table-custom associated-table">
                    <thead>
                        <tr>
                            <th style="width:240px;">Recurso</th>
                            <th style="width:180px;">Permissao</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($concessoes as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['recurso']) ?></td>
                                <td><?= htmlspecialchars($c['permissao']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <div class="card" style="margin-top:16px;">
        <div class="card-header">
            <a href="../../view/perfil/index.php" class="btn btn-secondary">Voltar a lista de perfis</a>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
