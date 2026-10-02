<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../controller/UsuarioController.php';
require_once __DIR__ . '/../../services/Sessao.php';

Sessao::iniciar();

$autenticado = Sessao::esta_logado();
$username    = $autenticado ? ($_SESSION['username'] ?? '') : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$autenticado) {
        $username = trim($_POST['username'] ?? '');
    }
    $senhaAntiga = $_POST['senha_antiga'] ?? '';
    $senhaNova   = $_POST['senha_nova'] ?? '';

    if ($username === '' || $senhaAntiga === '' || $senhaNova === '') {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Preencha todos os campos.'];
    } elseif (strlen($senhaNova) < 6) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'A nova senha deve ter pelo menos 6 caracteres.'];
    } elseif ($senhaNova === $senhaAntiga) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'A nova senha deve ser diferente da senha antiga.'];
    } else {
        $controller = new UsuarioController();
        if ($controller->redefinirSenha($username, $senhaAntiga, $senhaNova)) {
            // Primeiro acesso concluído: tratado internamente pelo sistema
            unset($_SESSION['primeiro_acesso']);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Senha alterada com sucesso!'];
            header('Location: ' . ($autenticado ? '../dashboard/index.php' : 'index.php'));
            exit;
        }
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Não foi possível alterar a senha. Verifique os dados.'];
    }

    header('Location: redefinir.php');
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha | Sistema de Gestão Académica</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="auth-body">

<main class="auth-main">
    <section class="auth-card">
        <h1 class="auth-title">Redefinir a sua senha</h1>

        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
                <span><?= htmlspecialchars($flash['msg']) ?></span>
            </div>
        <?php endif; ?>

        <form method="post" action="redefinir.php">
            <?php if (!$autenticado): ?>
                <div class="form-group">
                    <label for="username">Nome de usuario <span class="required">*</span></label>
                    <input type="text" id="username" name="username" class="form-control"
                           maxlength="60" autocomplete="username" required autofocus>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="senha_antiga">Senha Antiga <span class="required">*</span></label>
                <input type="password" id="senha_antiga" name="senha_antiga" class="form-control"
                       maxlength="60" autocomplete="current-password" required>
            </div>

            <div class="form-group">
                <label for="senha_nova">Senha Nova <span class="required">*</span></label>
                <input type="password" id="senha_nova" name="senha_nova" class="form-control"
                       maxlength="60" autocomplete="new-password" required>
            </div>

            <button type="submit" class="btn btn-success auth-submit">Submeter</button>
        </form>

        <div class="auth-links auth-links-center">
            <a href="index.php">&lt; Voltar para o login</a>
        </div>
    </section>
</main>

</body>
</html>
