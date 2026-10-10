<?php
require_once __DIR__ . '/../Dao/PerfilDao.php';
require_once __DIR__ . '/../Dao/RecursoDao.php';
require_once __DIR__ . '/../Dao/PermissaoDao.php';
require_once __DIR__ . '/../Dao/PerfilPermissaoDao.php';
require_once __DIR__ . '/../Dao/UsuarioDao.php';
require_once __DIR__ . '/../services/Sessao.php';

/**
 * Controlo da gestao de perfis e das permissoes por recurso.
 *
 * Concentra as regras de negocio que as views de perfil/permissoes usam:
 * - CRUD do perfil (nome);
 * - leitura da matriz de concessoes (perfil x recurso x permissao);
 * - gravacao da matriz, recarregando a sessao quando se altera o proprio perfil.
 *
 * Os perfis de sistema (ids 1..4) nao podem ser renomeados nem removidos,
 * para nunca quebrar o motor de autorizacao.
 */
class PerfilController {

    const PERFIS_SISTEMA = [1, 2, 3, 4];

    private $perfilDao;
    private $recursoDao;
    private $permissaoDao;
    private $perfilPermissaoDao;
    private $usuarioDao;

    public function __construct() {
        $this->perfilDao          = new PerfilDAO();
        $this->recursoDao         = new RecursoDao();
        $this->permissaoDao       = new PermissaoDao();
        $this->perfilPermissaoDao = new PerfilPermissaoDao();
        $this->usuarioDao         = new UsuarioDao();
    }

    // ---- Perfis ---------------------------------------------------------

    public function listar() {
        return $this->perfilDao->getAll();
    }

    public function buscar($id) {
        return $this->perfilDao->getById($id);
    }

    public function store($nome) {
        $nome = trim((string) $nome);
        if ($nome === '' || mb_strlen($nome) > 60) {
            return false;
        }
        if ($this->nomeEmUso($nome)) {
            return false;
        }
        return $this->perfilDao->create(new Perfil(null, $nome));
    }

    public function update($id, $nome) {
        $id = (int) $id;
        $nome = trim((string) $nome);

        if ($this->ehPerfilSistema($id) || $nome === '' || mb_strlen($nome) > 60) {
            return false;
        }
        if ($this->nomeEmUso($nome, $id)) {
            return false;
        }
        return $this->perfilDao->update(new Perfil($id, $nome));
    }

    public function delete($id) {
        $id = (int) $id;
        if ($this->ehPerfilSistema($id)) {
            return false;
        }
        if ($this->temUtilizadores($id)) {
            return false;
        }
        return $this->perfilDao->delete($id);
    }

    public function ehPerfilSistema($id) {
        return in_array((int) $id, self::PERFIS_SISTEMA, true);
    }

    // ---- Recursos e permissoes -----------------------------------------

    public function listarRecursos() {
        return $this->recursoDao->getAll();
    }

    public function listarPermissoes() {
        return $this->permissaoDao->getAll();
    }

    // Concessoes detalhadas de um perfil (linhas recurso x permissao).
    public function concessoes($idPerfil) {
        return $this->perfilPermissaoDao->getConcessoesByPerfil((int) $idPerfil);
    }

    public function temConcessoes($idPerfil) {
        return $this->perfilPermissaoDao->temConcessoes((int) $idPerfil);
    }

    // $concessoes: lista de pares ['recurso_id' => int, 'permissao_id' => int].
    // Substitui toda a matriz do perfil. Se o perfil for o da sessao actual,
    // recarrega as permissoes para o efeito ser imediato.
    public function guardarPermissoes($idPerfil, array $concessoes) {
        $idPerfil = (int) $idPerfil;
        $ok = $this->perfilPermissaoDao->substituir($idPerfil, $concessoes);

        if ($ok && $idPerfil === (int) ($_SESSION['idPerfil'] ?? 0)) {
            Sessao::recarregarPermissoes();
        }
        return $ok;
    }

    // ---- Auxiliares -----------------------------------------------------

    private function nomeEmUso($nome, $ignorarId = null) {
        foreach ($this->perfilDao->getAll() as $perfil) {
            if (mb_strtolower($perfil->getNome()) !== mb_strtolower($nome)) {
                continue;
            }
            if ($ignorarId !== null && (int) $perfil->getId() === (int) $ignorarId) {
                continue;
            }
            return true;
        }
        return false;
    }

    private function temUtilizadores($idPerfil) {
        return $this->usuarioDao->contarPorPerfil((int) $idPerfil) > 0;
    }
}
?>
