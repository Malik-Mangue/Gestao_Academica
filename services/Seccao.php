<?php 

class Seccao{
    /**
     * Chave reservada usada pelas views para transportar mensagens de
     * feedback após um redirect (ESPECIFICACAO_MODELO.md, ponto 6.4).
     */
    private const FLASH_KEY = 'flash';

    /**
     * Chave reservada onde ficam guardados os dados do utilizador autenticado.
     * 'user_id' é a chave que as views verificam no guarda de acesso
     * (ex.: view/Campo/index.php: `if (!isset($_SESSION['user_id']))`).
     */
    private const USER_KEY = 'usuario';
    private const ID_KEY = 'user_id';

    /**
     * Classe de serviço: só é usada por métodos estáticos.
     */
    private function __construct(){}

    public static function start():void{
        if(session_status() == PHP_SESSION_NONE){
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    /**
     * Devolve true quando a seccão já está ativa na memória.
     */
    public static function isStarted():bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Identificador atual da seccão (útil para depuração e logs).
     */
    public static function id():string
    {
        self::start();
        return session_id();
    }

    public static function set(string $id, mixed $valor):void
    {
        self::start();
        $_SESSION[$id] = $valor;
    }

    /**
     * Lê um valor da seccão, devolvendo $padrao quando não existe.
     */
    public static function get(string $id, mixed $padrao = null):mixed
    {
        self::start();
        return $_SESSION[$id] ?? $padrao;
    }

    /**
     * Verifica se a chave existe na seccão.
     */
    public static function has(string $id):bool
    {
        self::start();
        return isset($_SESSION[$id]);
    }

    /**
     * Remove uma chave da seccão.
     */
    public static function remove(string $id):void
    {
        self::start();
        unset($_SESSION[$id]);
    }

    /**
     * Devolve todos os dados da seccão como array.
     */
    public static function all():array
    {
        self::start();
        return $_SESSION;
    }

    /**
     * Esvazia a seccão sem a destruir (mantém o cookie e o id ativos).
     */
    public static function clear():void
    {
        self::start();
        $_SESSION = [];
    }

    /**
     * Regenera o id da seccão (obrigatório no login, contra session fixation).
     */
    public static function regenerate():void
    {
        self::start();
        if (!headers_sent()) {
            session_regenerate_id(true);
        }
    }

    /**
     * Destói por completo a seccão e apaga o cookie (logout).
     */
    public static function destroy():void
    {
        self::start();
        $_SESSION = [];

        if (ini_get('session.use_cookies') && !headers_sent()) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax'
            ]);
        }

        session_destroy();
    }

    /**
     * Armazena a mensagem de feedback usada após um redirect.
     * Tipos usados no projeto: 'success' e 'danger' (alerts da UI).
     */
    public static function setFlash(string $type, string $msg):void
    {
        self::set(self::FLASH_KEY, [
            'type' => $type,
            'msg' => $msg
        ]);
    }

    /**
     * Lê a mensagem de feedback e consome-a (unset), tal como exigido
     * no ponto 6.4 da especificação. Devolve null quando não há mensagem.
     *
     * @param bool $limpar Quando false, apenas lê sem consumir a mensagem.
     */
    public static function getFlash(bool $limpar = true):?array
    {
        self::start();

        $flash = $_SESSION[self::FLASH_KEY] ?? null;
        if (!is_array($flash)) {
            return null;
        }

        if ($limpar) {
            unset($_SESSION[self::FLASH_KEY]);
        }

        return $flash;
    }

    /**
     * Descarta a mensagem de feedback sem a ler.
     */
    public static function clearFlash():void
    {
        self::remove(self::FLASH_KEY);
    }

    /**
     * Inicia a seccão do utilizador autenticado e regenera o id
     * para prevenir session fixation. Grava também o 'user_id'
     * exigido pelos guardas de acesso das views.
     *
     * @param array $usuario Dados do utilizador (ex: codigo, nome, username, idPerfil).
     */
    public static function autenticar(array $usuario):void
    {
        self::start();
        self::regenerate();
        $_SESSION[self::ID_KEY] = $usuario['codigo'] ?? $usuario['user_id'] ?? null;
        $_SESSION[self::USER_KEY] = $usuario;
    }

    /**
     * Devolve true quando existe um utilizador autenticado.
     * Equivalente a `isset($_SESSION['user_id'])` usado nas views.
     */
    public static function autenticado():bool
    {
        return self::has(self::ID_KEY);
    }

    /**
     * Devolve os dados do utilizador autenticado ou null.
     */
    public static function usuario():?array
    {
        $usuario = self::get(self::USER_KEY);
        return is_array($usuario) ? $usuario : null;
    }

    /**
     * Guarda de acesso usado no topo das views: redireciona para o
     * login quando não existe utilizador autenticado.
     */
    public static function exigeLogin():void
    {
        if (!self::autenticado()) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Termina a seccão do utilizador (logout completo).
     */
    public static function logout():void
    {
        self::destroy();
    }
}
