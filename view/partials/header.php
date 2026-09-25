<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$deep = substr_count(trim($_SERVER['SCRIPT_NAME'], '/'), '/');
$rel_root = str_repeat('../', max(0, $deep - 1));
if (empty($rel_root)) {
    $rel_root = './';
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' | Gestão Acadêmica' : 'Gestão Acadêmica'; ?></title>
    <link rel="stylesheet" href="<?php echo $rel_root; ?>assets/css/style.css">
    <?php if (isset($screen_css) && !empty($screen_css)): ?>
    <link rel="stylesheet" href="<?php echo $rel_root; ?>assets/css/screens/<?php echo htmlspecialchars($screen_css); ?>.css">
    <?php endif; ?>
</head>
<body>

<header class="topbar">
    <div class="topbar-brand">
        <img src="<?php echo $rel_root; ?>assets/icons/logo.svg" alt="LMS Logo">
        <span>LMS</span>
        <span class="brand-badge">Gestão Acadêmica</span>
    </div>
    <div class="topbar-center-title">
        Academic & Faculty Control Panel
    </div>
    <div class="topbar-user">
        <div class="user-action-icon">
            <img src="<?php echo $rel_root; ?>assets/icons/bell.svg" alt="Notificações">
        </div>
        <div class="user-info">
            <div class="user-avatar-mini">
                <img src="<?php echo $rel_root; ?>assets/icons/user.svg" alt="Admin">
            </div>
            <span>admin</span>
        </div>
    </div>
</header>
