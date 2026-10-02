<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../controller/UsuarioController.php';
require_once __DIR__ . '/../../services/Sessao.php';

Sessao::iniciar();

// Já autenticado: segue para a área privada
if (Sessao::esta_logado()) {
    header('Location: ../dashboard/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $_SESSION['flash'] = [
            'type' => 'danger',
            'msg'  => 'Preencha o nome de utilizador e a password.'
        ];
    } else {
        $controller = new UsuarioController();
        $usuario = $controller->autenticar($username, $password);

        if ($usuario instanceof Usuario) {
            Sessao::login($usuario);

            // Primeiro acesso: obriga a redefinir a senha
            if ((int) $usuario->getPrimeiroAcesso() === 1) {
                header('Location: redefinir.php');
                exit;
            }

            $_SESSION['flash'] = [
                'type' => 'success',
                'msg'  => 'Bem-vindo, ' . $usuario->getNome() . '.'
            ];
            header('Location: ../dashboard/index.php');
            exit;
        }

        // Mensagem genérica: não revela se o username existe
        $_SESSION['flash'] = [
            'type' => 'danger',
            'msg'  => 'Credenciais inválidas.'
        ];
    }

    header('Location: index.php');
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
    <title>Login | Sistema de Gestão Académica</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="auth-body">

<main class="auth-main">
    <section class="auth-card">
        <h1 class="auth-title">BEM VINDO DE VOLTA</h1>
        <p class="auth-subtitle">Insira as suas credenciais</p>

        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
                <span><?= htmlspecialchars($flash['msg']) ?></span>
            </div>
        <?php endif; ?>

        <form method="post" action="index.php">
            <div class="form-group">
                <label for="username">Nome de usuario <span class="required">*</span></label>
                <input type="text" id="username" name="username" class="form-control"
                       maxlength="60" autocomplete="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password <span class="required">*</span></label>
                <input type="password" id="password" name="password" class="form-control"
                       maxlength="60" autocomplete="current-password" required>
            </div>

            <div class="auth-links">
                <a href="redefinir.php">esqueceu a sua senha?</a>
            </div>

            <button type="submit" class="btn btn-success auth-submit">Login</button>
        </form>
    </section>
</main>

</body>
</html>
