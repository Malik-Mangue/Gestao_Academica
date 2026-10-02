<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../services/Sessao.php';

// Encerra a sessão e volta para o login
Sessao::logout();
header('Location: index.php');
exit;
