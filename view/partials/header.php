<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo !empty($page_title) ? $page_title : 'Gestao Academica'; ?></title>
    
    
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<header class="topbar">
    <div class="topbar-brand">
        <img src="../../assets/icons/logo.svg" alt="LMS Logo">
        <span>LMS</span>
        <span class="brand-badge">Gestão Acadêmica</span>
    </div>
    <div class="topbar-center-title">
        Sistema de Gestao Academica
    </div>
    <div class="topbar-user">
        <div class="user-action-icon">
            <img src="../../assets/icons/bell.svg" alt="Notificações">
        </div>
        <div class="user-info">
            <div class="user-avatar-mini">
                <img src="../../assets/icons/user.svg" alt="Admin">
            </div>
            <span>admin</span>
        </div>
    </div>
</header>
