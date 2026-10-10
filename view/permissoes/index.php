<?php
require_once __DIR__ . '/../../services/Sessao.php';

// Gestao de permissoes: restricao por recurso (exclusiva do Administrador).
Sessao::exigirLeitura(Sessao::RECURSO_PERFIS, '../dashboard/index.php');

require_once __DIR__ . '/../../controller/PerfilController.php';

$controller = new PerfilController();

$perfis    = $controller->listar();
$recursos  = $controller->listarRecursos();
$permissoes = $controller->listarPermissoes();

// Ordem canonica de apresentacao das acoes: Criar, Consultar, Editar, Eliminar.
// Permissoes futuras (novos INSERT em `permissao`) aparecem no fim.
$ordem = ['criar', 'consultar', 'editar', 'eliminar'];
usort($permissoes, function ($a, $b) use ($ordem) {
    $ia = array_search($a->getNome(), $ordem, true);
    $ib = array_search($b->getNome(), $ordem, true);
    return ($ia === false ? PHP_INT_MAX : $ia) <=> ($ib === false ? PHP_INT_MAX : $ib);
});

// Gravacao da matriz de permissoes do perfil selecionado.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    Sessao::exigirAcao(Sessao::ACAO_EDITAR, Sessao::RECURSO_PERFIS, '../dashboard/index.php');

    $idPerfilPost = filter_input(INPUT_POST, 'idPerfil', FILTER_VALIDATE_INT);

    $concessoes = [];
    foreach (($_POST['permissoes'] ?? []) as $recursoId => $lista) {
        if (!is_array($lista)) {
            continue;
        }
        foreach ($lista as $permissaoId => $valor) {
            if (!$valor) {
                continue;
            }
            $concessoes[] = ['recurso_id' => (int) $recursoId, 'permissao_id' => (int) $permissaoId];
        }
    }

    // Anti-lockout: ao alterar o proprio perfil, e obrigatorio manter a leitura
    // do recurso `perfis`, sob pena de ninguem voltar a gerir permissoes.
    $idRecursoPerfis = 0;
    foreach ($recursos as $r) {
        if ($r->getNome() === Sessao::RECURSO_PERFIS) {
            $idRecursoPerfis = (int) $r->getId();
            break;
        }
    }
    $idPermConsultar = 0;
    foreach ($permissoes as $p) {
        if ($p->getNome() === 'consultar') {
            $idPermConsultar = (int) $p->getId();
            break;
        }
    }

    $ehProprio = ((int) $idPerfilPost === (int) ($_SESSION['idPerfil'] ?? 0));
    $mantemLeitura = false;
    foreach ($concessoes as $c) {
        if ($c['recurso_id'] === $idRecursoPerfis && $c['permissao_id'] === $idPermConsultar) {
            $mantemLeitura = true;
            break;
        }
    }

    if (!$idPerfilPost) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Perfil invalido.'];
        header('Location: index.php');
        exit;
    } elseif ($ehProprio && !$mantemLeitura) {
        $_SESSION['flash'] = [
            'type' => 'danger',
            'msg'  => 'Nao pode retirar a consulta ao recurso "perfis" do seu proprio perfil.'
        ];
        header('Location: index.php?perfil=' . (int) $idPerfilPost);
        exit;
    } elseif ($controller->guardarPermissoes($idPerfilPost, $concessoes)) {
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Permissoes guardadas com sucesso!'];
    } else {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Nao foi possivel guardar as permissoes.'];
    }

    header('Location: index.php?perfil=' . (int) $idPerfilPost);
    exit;
}

// Determina o perfil em exibicao.
$idPerfil = filter_input(INPUT_GET, 'perfil', FILTER_VALIDATE_INT);
if (!$idPerfil && $perfis) {
    $idPerfil = (int) $perfis[0]->getId();
}

$perfil = $idPerfil ? $controller->buscar($idPerfil) : null;
if ($perfil === null) {
    $idPerfil = 0;
}
$perfilNome = $perfil !== null ? $perfil->getNome() : '';

// Estado actual das concessoes (recurso_id => permissao_id => true).
$ativadas = [];
if ($idPerfil) {
    foreach ($controller->concessoes($idPerfil) as $c) {
        $ativadas[(int) $c['recurso_id']][(int) $c['permissao_id']] = true;
    }
}

// Recursos agrupados por grupo (Academico, Administrativos, ...).
$grupos = [];
foreach ($recursos as $recurso) {
    $grupos[$recurso->getGrupo()][] = $recurso;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$page_title = 'Gestao de Permissoes';
$active_menu = 'perfil';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>
<main class="main-wrapper">
    <div class="content-header">
        <h1>Gestao de Permissoes</h1>
        <div class="breadcrumb">
            <a href="../../view/dashboard/index.php">Home</a><span class="divider">/</span><span>Permissoes</span>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <span><?= htmlspecialchars($flash['msg']) ?></span>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Selecionar Perfil</h2>
        </div>
        <div class="card-body">
            <form method="get" action="index.php" class="form-actions">
                <div class="form-group">
                    <label for="perfil">Perfil</label>
                    <select id="perfil" name="perfil" class="form-control">
                        <?php foreach ($perfis as $p): ?>
                            <option value="<?= (int) $p->getId() ?>"
                                <?= $idPerfil === (int) $p->getId() ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p->getNome()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Seleccionar</button>
                <a href="../../view/perfil/index.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>

    <?php if ($idPerfil): ?>
        <form method="post" action="index.php">
            <input type="hidden" name="idPerfil" value="<?= (int) $idPerfil ?>">
            <div class="card">
                <div class="card-header">
                    <h2>Permissoes de <?= htmlspecialchars($perfilNome) ?></h2>
                    <div class="table-actions table-actions-end">
                        <a href="../../view/perfil/visualizar.php?id=<?= (int) $idPerfil ?>"
                           class="btn btn-sm btn-info">Ver detalhes</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table-custom permissao-matrix">
                            <thead>
                                <tr>
                                    <th class="col-recurso">Recurso</th>
                                    <?php foreach ($permissoes as $p): ?>
                                        <th class="col-permissao"><?= htmlspecialchars(ucfirst($p->getNome())) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($grupos as $grupo => $listaRecursos): ?>
                                    <tr class="permissao-grupo">
                                        <td colspan="<?= 1 + count($permissoes) ?>">
                                            <span class="grupo-tag"><?= htmlspecialchars($grupo) ?></span>
                                        </td>
                                    </tr>
                                    <?php foreach ($listaRecursos as $recurso): ?>
                                        <tr>
                                            <td class="col-recurso"><?= htmlspecialchars($recurso->getNome()) ?></td>
                                            <?php foreach ($permissoes as $p): ?>
                                                <td>
                                                    <input type="checkbox"
                                                           name="permissoes[<?= (int) $recurso->getId() ?>][<?= (int) $p->getId() ?>]"
                                                           value="1"
                                                           <?= isset($ativadas[(int) $recurso->getId()][(int) $p->getId()]) ? 'checked' : '' ?>>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="note">
                        C = Criar &middot; R = Consultar &middot; U = Editar &middot; D = Eliminar.
                        Perfis sem registos em perfil_permissao usam o padrao dos perfis legados.
                    </p>

                    <div class="card-footer">
                        <button type="submit" name="guardar" class="btn btn-success">Guardar</button>
                        <a href="?perfil=<?= (int) $idPerfil ?>" class="btn btn-secondary">Fazer novo</a>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
