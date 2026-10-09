<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../Dao/PerfilDao.php';
require_once __DIR__ . '/../model/Usuario.php';

/**
 * Classe estatica de sessao - unico ponto de inicio/termino de sessoes
 * e de controlo de autenticacao/autorizacao. Nao guarda passwords nem
 * executa regras de negocio.
 */
final class Sessao
{
    // Nomes canonicos dos perfis (resolvidos sempre na BD pela classe Perfil)
    const ADMIN    = 'Administrador';
    const SUPER    = 'SuperOperador';
    const OPERADOR = 'Operador';
    const AUDITOR  = 'Auditor';

    // Acoes usadas pela politica de permissoes (ESPECIFICACAO_AUTENTICACAO)
    const ACAO_CRIAR   = 'C';
    const ACAO_LEITURA = 'R';
    const ACAO_EDITAR  = 'U';
    const ACAO_REMOVER = 'D';

    // Tempo de vida da sessao: 2 horas (7200 segundos)
    private static $duracao = 7200;

    public static function iniciar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.gc_maxlifetime', (string) Sessao::$duracao);
            session_set_cookie_params([
                'lifetime' => Sessao::$duracao,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public static function esta_logado()
    {
        return isset($_SESSION['user_id']);
    }

    public static function utilizador()
    {
        if (!Sessao::esta_logado()) {
            return null;
        }
        return [
            'id'       => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? '',
            'nome'     => $_SESSION['nome'] ?? '',
            'idPerfil' => $_SESSION['idPerfil'] ?? null,
            'perfil'   => $_SESSION['perfil'] ?? '',
        ];
    }

    // Reconstroi um objeto Usuario a partir da sessao. Usado pela camada de
    // auditoria (Log), que precisa do codigo do utilizador e nao de um array.
    public static function obterUtilizador()
    {
        if (!Sessao::esta_logado()) {
            return null;
        }
        $perfil = new Perfil($_SESSION['idPerfil'] ?? null, $_SESSION['perfil'] ?? null);

        $usuario = new Usuario(
            $_SESSION['user_id'],
            $_SESSION['idPerfil'] ?? null,
            $_SESSION['nome'] ?? '',
            $_SESSION['username'] ?? '',
            $_SESSION['apelido'] ?? '',
            null,
            empty($_SESSION['primeiro_acesso']) ? 0 : 1
        );
        $usuario->setPerfil($perfil);

        return $usuario;
    }

    public static function login(Usuario $usuario)
    {
        Sessao::iniciar();
        session_regenerate_id(true);

        $_SESSION['user_id']  = $usuario->getCodigo();
        $_SESSION['username'] = $usuario->getUsername();
        $_SESSION['nome']     = $usuario->getNome();
        $_SESSION['apelido']  = $usuario->getApelido();
        $_SESSION['idPerfil'] = $usuario->getIdPerfil();
        $_SESSION['perfil']   = Sessao::nomePerfil($usuario->getIdPerfil());
        // Primeiro acesso: flag interno do sistema, nunca escolha do utilizador
        $_SESSION['primeiro_acesso'] = (int) $usuario->getPrimeiroAcesso() === 1;
    }

    public static function primeiroAcesso()
    {
        return Sessao::esta_logado() && !empty($_SESSION['primeiro_acesso']);
    }

    public static function logout()
    {
        Sessao::iniciar();
        session_unset();
        session_destroy();
    }

    public static function exigirLogin($destino)
    {
        Sessao::iniciar();
        if (!Sessao::esta_logado()) {
            header('Location: ' . $destino);
            exit;
        }
        Sessao::bloquearPrimeiroAcesso();
    }

    // Bloqueia a área privada enquanto o primeiro acesso (troca obrigatória
    // de senha) não estiver concluído — decisão interna do sistema.
    private static function bloquearPrimeiroAcesso()
    {
        if (Sessao::primeiroAcesso()) {
            header('Location: ../login/redefinir.php');
            exit;
        }
    }

    // Matriz de permissoes por perfil (ver tabela da especificacao).
    // 'Criar + Reset senha', 'CRUD', 'CR' e 'R' sao as operacoes permitidas
    // sobre os recursos academicos; 'Utilizadores' e 'Log' sao exclusivos.
    private static $permissoes = [
        self::ADMIN    => [
            'CRUD', 'Utilizadores', 'Logs',
        ],
        self::SUPER    => [
            'CRUD',
        ],
        self::OPERADOR => [
            'CR',
        ],
        self::AUDITOR  => [
            'CRUD', 'Logs',
        ],
    ];

    public static function perfil()
    {
        return $_SESSION['perfil'] ?? '';
    }

    public static function temPerfil(array $perfis)
    {
        return in_array(Sessao::perfil(), $perfis, true);
    }

    // TRUE quando o perfil actual tem a capacidade indicada ('C', 'R', 'U' ou 'D').
    public static function pode($acao)
    {
        $capacidades = Sessao::$permissoes[Sessao::perfil()] ?? [];
        if (in_array('CRUD', $capacidades, true)) {
            return true;
        }
        if (in_array('CR', $capacidades, true)) {
            return in_array($acao, [Sessao::ACAO_CRIAR, Sessao::ACAO_LEITURA], true);
        }
        return in_array($acao, [Sessao::ACAO_LEITURA], true);
    }

    public static function ehAdministrador()
    {
        return Sessao::perfil() === Sessao::ADMIN;
    }

    // Gestao de utilizadores: exclusiva do Administrador.
    public static function gestaoUtilizadores()
    {
        return Sessao::ehAdministrador();
    }

    // Auditoria de logs: exclusiva do Auditor.
    public static function auditoriaLogs()
    {
        return Sessao::perfil() === Sessao::AUDITOR;
    }

    // Revalida o perfil antes de qualquer mutacao (C, U ou D).
    public static function exigirAcao($acao, $destino)
    {
        Sessao::iniciar();
        if (!Sessao::esta_logado()) {
            header('Location: ' . $destino);
            exit;
        }
        if (!Sessao::pode($acao)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg'  => 'Não tem permissão para executar esta operação.',
            ];
            header('Location: ' . $destino);
            exit;
        }
        Sessao::bloquearPrimeiroAcesso();
    }

    public static function exigirPerfil(array $perfis, $destino)
    {
        Sessao::iniciar();
        if (!Sessao::esta_logado()) {
            header('Location: ' . $destino);
            exit;
        }
        if (!Sessao::temPerfil($perfis)) {
            header('Location: ' . $destino);
            exit;
        }
        Sessao::bloquearPrimeiroAcesso();
    }

    // Rastreio do perfil existente na base de dados atraves da classe Perfil
    private static function nomePerfil($idPerfil)
    {
        if ($idPerfil === null || $idPerfil === '') {
            return '';
        }
        $dao = new PerfilDao();
        $perfil = $dao->getById($idPerfil);
        return $perfil !== null ? $perfil->getNome() : '';
    }
}
