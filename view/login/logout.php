<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../services/Sessao.php';
require_once __DIR__ . '/../../Dao/UsuarioDao.php';

$usuario = Sessao::obterUtilizador();
if ($usuario != null) {
    (new UsuarioDao())->registarLogout($usuario);
}

Sessao::logout();
header('Location: index.php');
exit;
