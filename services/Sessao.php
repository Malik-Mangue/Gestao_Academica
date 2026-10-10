<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../Dao/PerfilDao.php';
require_once __DIR__ . '/../Dao/RecursoDao.php';
require_once __DIR__ . '/../Dao/PermissaoDao.php';
require_once __DIR__ . '/../Dao/PerfilPermissaoDao.php';
require_once __DIR__ . '/../model/Usuario.php';

/**
 * Classe estatica de sessao - unico ponto de inicio/termino de sessoes
 * e de controlo de autenticacao/autorizacao. Nao guarda passwords nem
 * executa regras de negocio.
 *
 * Autorizacao (RBAC) granular por recurso:
 *   perfil -> (recurso -> permissoes)
 *
 * - A fonte de verdade e a tabela `perfil_permissao` (recurso.nome x permissao.nome).
 * - Perfis sem qualquer linha em `perfil_permissao` (os perfis "legados") usam
 *   a matriz de fallback definida em codigo, para continuarem a funcionar.
 * - Um recurso novo e apenas um novo INSERT em `recurso`; uma permissao nova e
 *   apenas um novo INSERT em `permissao` (basta depois chamar
 *   Sessao::pode('nome_da_permissao', Sessao::RECURSO_X)).
 */
final class Sessao
{
    // Nomes canonicos dos perfis (resolvidos sempre na BD pela classe Perfil)
    const ADMIN    = 'Administrador';
    const SUPER    = 'SuperOperador';
    const OPERADOR = 'Operador';
    const AUDITOR  = 'Auditor';

    // Acoes usadas pela politica de permissoes. Cada acao curta e traduzida
    // para o nome correspondente na tabela `permissao` (ver $apelidoAcao).
    const ACAO_CRIAR   = 'C';
    const ACAO_LEITURA = 'R';
    const ACAO_EDITAR  = 'U';
    const ACAO_REMOVER = 'D';

    // Chaves dos recursos (recurso.nome na BD). Novos recursos so precisam de
    // uma nova constante aqui para serem referidos de forma segura no codigo.
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

    // Duracao da sessao: 2 horas (adequada a sistema academico).
    private static $duracao = 7200;

    // Traducao das acoes curtas para o nome da permissao na BD.
    private static $apelidoAcao = [
        self::ACAO_CRIAR   => 'criar',
        self::ACAO_LEITURA => 'consultar',
        self::ACAO_EDITAR  => 'editar',
        self::ACAO_REMOVER => 'eliminar',
    ];

    // Matriz de fallback para os perfis "legados" (sem linhas em
    // perfil_permissao). '*<grupo>' aplica-se a todos os recursos desse grupo.
    // Regras de negocio: Administrador sem acesso a logs; Auditor com CRUD nos
    // recursos academicos e apenas leitura nos logs.
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

        // Carrega a matriz de permissoes do perfil para a sessao.
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

    // Bloqueia a area privada enquanto o primeiro acesso (troca obrigatoria
    // de senha) nao estiver concluido - decisao interna do sistema.
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

    // TRUE quando o perfil actual tem a permissao indicada sobre o recurso.
    // $acao aceita um codigo curto ('C','R','U','D') ou o nome da permissao
    // (ex.: 'criar', 'consultar' ou uma permissao futura como 'exportar').
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

    // Gestao de utilizadores: depende da permissao de leitura no recurso.
    public static function gestaoUtilizadores()
    {
        return Sessao::pode(Sessao::ACAO_LEITURA, Sessao::RECURSO_UTILIZADORES);
    }

    // Auditoria de logs: depende da permissao de leitura no recurso.
    public static function auditoriaLogs()
    {
        return Sessao::pode(Sessao::ACAO_LEITURA, Sessao::RECURSO_LOGS);
    }

    // Revalida a permissao antes de qualquer mutacao (C, U ou D) num recurso.
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

    // Exige permissao de leitura sobre um recurso (guarda de rota).
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

    // Recarrega as permissoes do perfil em sessao. Deve ser chamado depois de
    // alterar as concessoes de um perfil (a UI de gestao de perfis fara-lo).
    public static function recarregarPermissoes()
    {
        Sessao::iniciar();
        Sessao::carregarPermissoes($_SESSION['idPerfil'] ?? null, $_SESSION['perfil'] ?? '');
    }

    // Constroi $_SESSION['permissoes'] a partir da BD. Se o perfil nao tiver
    // concessoes explicitas e for um perfil legado, aplica a matriz de fallback.
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

    // Expande a matriz de fallback para todos os recursos existentes na BD,
    // agrupando por grupo (ex.: todos os recursos 'Academico').
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

    // Aceita o codigo curto ('C') ou ja o nome da permissao ('criar'/'exportar').
    private static function normalizarAcao($acao)
    {
        return self::$apelidoAcao[$acao] ?? $acao;
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
