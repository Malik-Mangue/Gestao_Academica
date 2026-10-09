<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../services/Sessao.php';
require_once __DIR__ . '/../../Dao/UsuarioDao.php';

// Registra a saída antes de encerrar a sessão (o log precisa do utilizador)
$usuario = Sessao::obterUtilizador();
if ($usuario != null) {
    (new UsuarioDao())->registarLogout($usuario);
}

// Encerra a sessão e volta para o login
Sessao::logout();
header('Location: index.php');
exit;
