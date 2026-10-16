<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../Dao/PerfilDao.php';
require_once __DIR__ . '/../Dao/RecursoDao.php';
require_once __DIR__ . '/../Dao/PermissaoDao.php';
require_once __DIR__ . '/../Dao/PerfilPermissaoDao.php';
require_once __DIR__ . '/../model/Usuario.php';

final class Sessao
{
    const ADMIN    = 'Administrador';
    const SUPER    = 'SuperOperador';
    const OPERADOR = 'Operador';
    const AUDITOR  = 'Auditor';

    const ACAO_CRIAR   = 'C';
    const ACAO_LEITURA = 'R';
    const ACAO_EDITAR  = 'U';
    const ACAO_REMOVER = 'D';

    const RECURSO_DASHBOARD      = 'dashboard';
    const RECURSO_FORMADORES     = 'formadores';
    const RECURSO_FORMANDOS      = 'formandos';
    const RECURSO_MATRICULAS     = 'matriculas';
    const RECURSO_INSCRICOES     = 'inscricoes';
    const RECURSO_TURMAS         = 'turmas';
    const RECURSO_MODULOS        = 'modulos';
    const RECURSO_QUALIFICACOES  = 'qualificacoes';
    const RECURSO_NIVEIS         = 'niveis';
    const RECURSO_CAMPOS         = 'campos';
    const RECURSO_SALAS          = 'salas';
    const RECURSO_LICOES         = 'licoes';
    const RECURSO_UTILIZADORES   = 'utilizadores';
    const RECURSO_LOGS           = 'logs';
    const RECURSO_PERFIS         = 'perfis';

    private static $duracao = 7200;

    private static $apelidoAcao = [
        self::ACAO_CRIAR   => 'criar',
        self::ACAO_LEITURA => 'consultar',
        self::ACAO_EDITAR  => 'editar',
        self::ACAO_REMOVER => 'eliminar',
    ];

    private static $matrizLegada = [
        self::ADMIN => [
            '*Academico'   => ['consultar', 'criar', 'editar', 'eliminar'],
            'utilizadores' => ['consultar', 'criar', 'editar', 'eliminar'],
            'perfis'       => ['consultar', 'criar', 'editar', 'eliminar'],
        ],
        self::SUPER => [
            '*Academico'   => ['consultar', 'criar', 'editar', 'eliminar'],
        ],
        self::OPERADOR => [
            '*Academico'   => ['consultar', 'criar'],
        ],
        self::AUDITOR => [
            '*Academico'   => ['consultar', 'criar', 'editar', 'eliminar'],
            'logs'         => ['consultar'],
        ],
    ];

    public static function iniciar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.gc_maxlifetime', self::$duracao);
            session_set_cookie_params([
                'lifetime' => self::$duracao,
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
        $_SESSION['primeiro_acesso'] = (int) $usuario->getPrimeiroAcesso() === 1;

        Sessao::carregarPermissoes($usuario->getIdPerfil(), $_SESSION['perfil']);
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

    private static function bloquearPrimeiroAcesso()
    {
        if (Sessao::primeiroAcesso()) {
            header('Location: ../login/redefinir.php');
            exit;
        }
    }

    public static function perfil()
    {
        return $_SESSION['perfil'] ?? '';
    }

    public static function temPerfil(array $perfis)
    {
        return in_array(Sessao::perfil(), $perfis, true);
    }

    public static function pode($acao, $recurso)
    {
        if ($recurso === null || $recurso === '') {
            return false;
        }
        Sessao::garantirPermissoesCarregadas();
        $permissao = Sessao::normalizarAcao($acao);
        return !empty($_SESSION['permissoes'][$recurso][$permissao]);
    }

    public static function podeCriar($recurso)
    {
        return Sessao::pode(Sessao::ACAO_CRIAR, $recurso);
    }

    public static function podeLer($recurso)
    {
        return Sessao::pode(Sessao::ACAO_LEITURA, $recurso);
    }

    public static function podeEditar($recurso)
    {
        return Sessao::pode(Sessao::ACAO_EDITAR, $recurso);
    }

    public static function podeRemover($recurso)
    {
        return Sessao::pode(Sessao::ACAO_REMOVER, $recurso);
    }

    public static function ehAdministrador()
    {
        return Sessao::perfil() === Sessao::ADMIN;
    }

    public static function gestaoUtilizadores()
    {
        return Sessao::pode(Sessao::ACAO_LEITURA, Sessao::RECURSO_UTILIZADORES);
    }

    public static function auditoriaLogs()
    {
        return Sessao::pode(Sessao::ACAO_LEITURA, Sessao::RECURSO_LOGS);
    }

    public static function exigirAcao($acao, $recurso, $destino)
    {
        Sessao::iniciar();
        if (!Sessao::esta_logado()) {
            header('Location: ' . $destino);
            exit;
        }
        if (!Sessao::pode($acao, $recurso)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg'  => 'Não tem permissão para executar esta operação.',
            ];
            header('Location: ' . $destino);
            exit;
        }
        Sessao::bloquearPrimeiroAcesso();
    }

    public static function exigirLeitura($recurso, $destino)
    {
        Sessao::iniciar();
        if (!Sessao::esta_logado()) {
            header('Location: ' . $destino);
            exit;
        }
        if (!Sessao::pode(Sessao::ACAO_LEITURA, $recurso)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg'  => 'Não tem permissão para acessar este recurso.',
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

    public static function recarregarPermissoes()
    {
        Sessao::iniciar();
        Sessao::carregarPermissoes($_SESSION['idPerfil'] ?? null, $_SESSION['perfil'] ?? '');
    }

    private static function carregarPermissoes($idPerfil, $nomePerfil)
    {
        $_SESSION['permissoes'] = [];
        if ($idPerfil === null || $idPerfil === '') {
            return;
        }

        $matriz = (new PerfilPermissaoDao())->getMatrizByPerfil($idPerfil);
        if (empty($matriz) && isset(self::$matrizLegada[$nomePerfil])) {
            $matriz = self::construirMatrizLegada($nomePerfil);
        }

        $_SESSION['permissoes'] = $matriz;
    }

    private static function construirMatrizLegada($nomePerfil)
    {
        $definicao = self::$matrizLegada[$nomePerfil] ?? [];
        $matriz = [];

        foreach ((new RecursoDao())->getAll() as $recurso) {
            $nome  = $recurso->getNome();
            $grupo = $recurso->getGrupo();

            $permissoes = $definicao['*' . $grupo] ?? [];
            if (isset($definicao[$nome])) {
                $permissoes = $definicao[$nome];
            }
            if (!empty($permissoes)) {
                $matriz[$nome] = array_fill_keys($permissoes, true);
            }
        }

        return $matriz;
    }

    private static function garantirPermissoesCarregadas()
    {
        Sessao::iniciar();
        if (!array_key_exists('permissoes', $_SESSION)) {
            Sessao::carregarPermissoes($_SESSION['idPerfil'] ?? null, $_SESSION['perfil'] ?? '');
        }
    }

    private static function normalizarAcao($acao)
    {
        return self::$apelidoAcao[$acao] ?? $acao;
    }

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
