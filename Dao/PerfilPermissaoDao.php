<?php
require_once __DIR__ . '/../config/conexao.php';

/**
 * Persistencia das concessoes perfil <-> recurso <-> permissao.
 *
 * A tabela perfil_permissao e a fonte de verdade para perfis criados pelo
 * Administrador. Os perfis "legados" (sem qualquer linha) sao tratados pela
 * classe Sessao com uma matriz de fallback, pelo que podem continuar a
 * funcionar sem constarem aqui.
 */
class PerfilPermissaoDao {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Matriz de permissoes efectivas: [recurso.nome => [permissao.nome => true]].
    public function getMatrizByPerfil($idPerfil) {
        $sql = "select r.nome as recurso, p.nome as permissao
                from perfil_permissao pp
                join recurso r on r.id = pp.recurso_id
                join permissao p on p.id = pp.permissao_id
                where pp.perfil_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idPerfil);
        $stmt->execute();
        $result = $stmt->get_result();

        $matriz = [];
        while ($rs = $result->fetch_assoc()) {
            $matriz[$rs['recurso']][$rs['permissao']] = true;
        }
        return $matriz;
    }

    // Lista detalhada (para a UI de gestao de perfis).
    public function getConcessoesByPerfil($idPerfil) {
        $sql = "select pp.id, pp.recurso_id, pp.permissao_id,
                       r.nome as recurso, r.grupo, p.nome as permissao
                from perfil_permissao pp
                join recurso r on r.id = pp.recurso_id
                join permissao p on p.id = pp.permissao_id
                where pp.perfil_id = ?
                order by r.grupo, r.nome, p.id";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idPerfil);
        $stmt->execute();
        $result = $stmt->get_result();

        $concessoes = [];
        while ($rs = $result->fetch_assoc()) {
            $concessoes[] = $rs;
        }
        return $concessoes;
    }

    // Substitui TODAS as concessoes do perfil pelo conjunto indicado.
    // $concessoes: lista de pares ['recurso_id' => int, 'permissao_id' => int].
    // Transaccional: ou aplica tudo, ou nao altera nada.
    public function substituir($idPerfil, array $concessoes) {
        $this->db->begin_transaction();
        try {
            $del = $this->db->prepare("delete from perfil_permissao where perfil_id = ?");
            $del->bind_param("i", $idPerfil);
            $del->execute();

            $ins = $this->db->prepare(
                "insert into perfil_permissao (perfil_id, recurso_id, permissao_id) values (?, ?, ?)"
            );
            $vistos = [];
            foreach ($concessoes as $c) {
                $recursoId   = (int) ($c['recurso_id'] ?? 0);
                $permissaoId = (int) ($c['permissao_id'] ?? 0);
                if ($recursoId <= 0 || $permissaoId <= 0) {
                    continue;
                }
                $chave = $recursoId . ':' . $permissaoId;
                if (isset($vistos[$chave])) {
                    continue;
                }
                $vistos[$chave] = true;
                $ins->bind_param("iii", $idPerfil, $recursoId, $permissaoId);
                $ins->execute();
            }

            $this->db->commit();
            return true;
        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function removerPorPerfil($idPerfil) {
        $sql = "delete from perfil_permissao where perfil_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idPerfil);
        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    // Um perfil tem concessoes explicitas? (distingue perfis "novos" de "legados")
    public function temConcessoes($idPerfil) {
        $sql = "select count(*) as total from perfil_permissao where perfil_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idPerfil);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();
        return $rs !== null && (int) $rs['total'] > 0;
    }
}
?>
