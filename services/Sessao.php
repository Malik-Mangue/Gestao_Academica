<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
        public static function obterUtilizador()
    {
        if (!Sessao::esta_logado()) {
            return null;
        }
        return new Usuario($_SESSION['user_id'], $_SESSION['username'] ?? '', null);
    }

    public static function login(Usuario $usuario)
    {
        Sessao::iniciar();
        session_regenerate_id(true);

        $_SESSION['user_id']  = $usuario->getCodigo();
        $_SESSION['username'] = $usuario->getUsername();
        $_SESSION['nome']     = $usuario->getNome();
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

    public static function perfil()
    {
        return $_SESSION['perfil'] ?? '';
    }

    public static function temPerfil(array $perfis)
    {
        return in_array(Sessao::perfil(), $perfis, true);
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
