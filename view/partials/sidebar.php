<?php
require_once __DIR__ . '/../../services/Sessao.php';
Sessao::iniciar();

$active = $active_menu ?? 'dashboard';

// A gestao de utilizadores so aparece para o Administrador e a auditoria
// de logs apenas para o Auditor — os menus respeitam a politica de perfis.
$mostrar_utilizadores = Sessao::gestaoUtilizadores();
$mostrar_logs        = Sessao::auditoriaLogs();

?>
<aside class="sidebar">
    <div class="sidebar-user-panel">
        <div class="sidebar-avatar">
            <img src="../../assets/icons/user.svg" alt="User">
        </div>
        <div class="sidebar-user-greeting">Welcome!</div>
        <div class="sidebar-user-name"><?= htmlspecialchars($sessao_atual['nome'] ?? 'admin') ?></div>
    </div>

    <div class="sidebar-heading">Menu Principal</div>
    <ul class="sidebar-menu">
        <li class="<?php echo $active === 'dashboard' ? 'active' : ''; ?>">
            <a href="../../view/dashboard/index.php">
                <span class="sidebar-icon">
                    <img src="../../assets/icons/dashboard.svg" alt="Dashboard">
                </span>
                <span>Dashboard</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-heading"></div>
    <ul class="sidebar-menu">
        <li class="<?php echo $active === 'professor' ? 'active' : ''; ?>">
            <a href="../../view/formadores/index.php">
                <span class="sidebar-icon">
                    <img src="../../assets/icons/professor.svg" alt="Professores">
                </span>
                <span>Formadores</span>
            </a>
        </li>
        <!-- <li class="<?php /* echo $active === 'licao' ? 'active' : '';*/ ?>">
            <a href="../../view/licao/index.php">
                <span class="sidebar-icon">
                    <img src="../../assets/icons/licao.svg" alt="Lições">
                </span>
                <span>Horarios</span>
            </a>
        </li> -->
    </ul>

    <div class="sidebar-heading">Gestão Acadêmica</div>
    <ul class="sidebar-menu">
        <li class="<?php echo $active === 'turma' ? 'active' : ''; ?>">
            <a href="../../view/turma/index.php">
                <span class="sidebar-icon">
                    <img src="../../assets/icons/turma.svg" alt="Turmas">
                </span>
                <span>Turmas</span>
            </a>
        </li>
        <li class="<?php echo $active === 'formando' ? 'active' : ''; ?>">
            <a href="../../view/formando/index.php">
                <span class="sidebar-icon">
                    <img src="../../assets/icons/formando.svg" alt="Formandos">
                </span>
                <span>Formandos</span>
            </a>
        </li>
        <li class="<?php echo $active === 'matricula' ? 'active' : ''; ?>">
            <a href="../../view/matricula/index.php">
                <span class="sidebar-icon">
                    <img src="../../assets/icons/matricula.svg" alt="Matrículas">
                </span>
                <span>Matrículas</span>
            </a>
        </li>
        <li class="<?php echo $active === 'inscricao' ? 'active' : ''; ?>">
            <a href="../../view/inscricao/index.php">
                <span class="sidebar-icon">
                    <img src="../../assets/icons/inscricao.svg" alt="Inscrições">
                </span>
                <span>Inscrições em Módulos</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-heading">Estrutura Curricular</div>
    <ul class="sidebar-menu">
        <li class="<?php echo $active === 'modulo' ? 'active' : ''; ?>">
            <a href="../../view/modulo/index.php">
                <span class="sidebar-icon">
                    <img src="../../assets/icons/modulo.svg" alt="Módulos">
                </span>
                <span>Módulos</span>
            </a>
        </li>
        <li class="<?php echo $active === 'qualificacao' ? 'active' : ''; ?>">
            <a href="../../view/qualificacao/index.php">
                <span class="sidebar-icon">
                    <img src="../../assets/icons/qualificacao.svg" alt="Qualificações">
                </span>
                <span>Qualificações</span>
            </a>
        </li>
        <li class="<?php echo $active === 'nivel' ? 'active' : ''; ?>">
            <a href="../../view/nivel/index.php">
                <span class="sidebar-icon">
                    <img src="../../assets/icons/nivel.svg" alt="Níveis">
                </span>
                <span>Níveis de Formação</span>
            </a>
        </li>
        <li class="<?php echo $active === 'campo' ? 'active' : ''; ?>">
            <a href="../../view/Campo/index.php">
                <span class="sidebar-icon">
                    <img src="../../assets/icons/campo.svg" alt="Campos">
                </span>
                <span>Campos</span>
            </a>
        </li>
        <li class="<?php echo $active === 'sala' ? 'active' : ''; ?>">
            <a href="../../view/sala/index.php">
                <span class="sidebar-icon">
                    <img src="../../assets/icons/sala.svg" alt="Salas">
                </span>
                <span>Salas</span>
            </a>
        </li>
    </ul>

    
    <?php if ($mostrar_utilizadores || $mostrar_logs): ?>
    <div class="sidebar-heading">Administração</div>
    <ul class="sidebar-menu">
        <?php if ($mostrar_utilizadores): ?>
        <li class="<?php echo $active === 'usuario' ? 'active' : ''; ?>">
            <a href="../../view/usuario/index.php">
                <span class="sidebar-icon">
                    <img src="../../assets/icons/usuario.svg" alt="Utilizadores">
                </span>
                <span>Utilizadores</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if ($mostrar_logs): ?>
        <li class="<?php echo $active === 'log' ? 'active' : ''; ?>">
            <a href="../../view/log/index.php">
                <span class="sidebar-icon">
                    <img src="../../assets/icons/dashboard.svg" alt="Registos">
                </span>
                <span>Registos (Log)</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
<?php endif; ?>
</aside>
