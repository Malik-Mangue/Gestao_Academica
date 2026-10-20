<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../services/Sessao.php';
require_once __DIR__ . '/../model/Formando.php';
require_once __DIR__ . '/../model/Campo.php';
require_once __DIR__ . '/../model/Logs.php';
require_once __DIR__ . '/LogDao.php';

class FormandoDAO {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    private function montarFormando($rs) {
        $formando = new Formando(
            $rs['codigo_formando'],
            $rs['nome_formando'],
            $rs['apelido_formando'],
            $rs['email'],
            $rs['BI'],
            $rs['contacto_formando']
        );
        if (isset($rs['codCampo'])) {
            $formando->setCodCampo($rs['codCampo']);
            if (isset($rs['campo_nome'])) {
                $formando->setCampo(new Campo($rs['codCampo'], $rs['campo_nome']));
            }
        }
        return $formando;
    }

    public function getAll($pesquisa = null, $codCampo = null) {
        $sql = "select f.codigo_formando, f.nome_formando, f.apelido_formando,
                       f.contacto_formando, f.email, f.BI, f.codCampo,
                       c.nome as campo_nome
                from Formando f
                left join Campo c on c.codigo = f.codCampo";

        $condicoes = [];
        $tipos = "";
        $parametros = [];
        if ($pesquisa !== null && strlen($pesquisa) > 0) {
            $condicoes[] = "(f.nome_formando like ? or f.apelido_formando like ? or f.email like ?
                             or f.BI like ? or f.contacto_formando like ?)";
            $busca = "%" . $pesquisa . "%";
            $parametros = array_merge($parametros, [$busca, $busca, $busca, $busca, $busca]);
            $tipos .= "sssss";
        }
        if ($codCampo !== null && (int) $codCampo > 0) {
            $condicoes[] = "f.codCampo = ?";
            $parametros[] = (int) $codCampo;
            $tipos .= "i";
        }
        if (!empty($condicoes)) {
            $sql .= " where " . implode(" and ", $condicoes);
        }
        $sql .= " order by f.nome_formando, f.apelido_formando";

        $stmt = $this->db->prepare($sql);
        if (!empty($parametros)) {
            $stmt->bind_param($tipos, ...$parametros);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $formandos = [];
        while ($rs = $result->fetch_assoc()) {
            $formandos[] = $this->montarFormando($rs);
        }
        return $formandos;
    }

    public function getById($codigo) {
        $sql = "select f.codigo_formando, f.nome_formando, f.apelido_formando,
                       f.contacto_formando, f.email, f.BI, f.codCampo,
                       c.nome as campo_nome
                from Formando f
                left join Campo c on c.codigo = f.codCampo
                where f.codigo_formando = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();

        if ($rs === null) {
            return null;
        }
        return $this->montarFormando($rs);
    }

    public function create(Formando $formando) {
        $sql = "insert into Formando (nome_formando, apelido_formando, contacto_formando, email, BI, codCampo) values (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $nome = $formando->getNome();
        $apelido = $formando->getApelido();
        $contacto = $formando->getContacto();
        $email = $formando->getEmail();
        $bi = $formando->getBi();
        $codCampo = $formando->getCodCampo();
        $stmt->bind_param("ssissi", $nome, $apelido, $contacto, $email, $bi, $codCampo);
        try {
            $result = $stmt->execute();
            if ($result) {
                $this->registarLog("INSERT", "Formando " . $nome . " " . $apelido . " foi cadastrado");
            }
            return $result;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function update(Formando $formando) {
        $sql = "update Formando set nome_formando = ?, apelido_formando = ?, contacto_formando = ?, email = ?, BI = ?, codCampo = ? where codigo_formando = ?";
        $stmt = $this->db->prepare($sql);
        $nome = $formando->getNome();
        $apelido = $formando->getApelido();
        $contacto = $formando->getContacto();
        $email = $formando->getEmail();
        $bi = $formando->getBi();
        $codCampo = $formando->getCodCampo();
        $codigo = $formando->getCodigo();
        $stmt->bind_param("ssissii", $nome, $apelido, $contacto, $email, $bi, $codCampo, $codigo);
        try {
            $result = $stmt->execute();
            if ($result) {
                $this->registarLog("UPDATE", "Formando " . $nome . " " . $apelido . " (ID: " . $codigo . ") foi atualizado");
            }
            return $result;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function delete($codigo) {
        $sql = "delete from Formando where codigo_formando = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $codigo);
        try {
            $result = $stmt->execute();
            if ($result) {
                $this->registarLog("DELETE", "Formando (ID: " . $codigo . ") foi removido");
            }
            return $result;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function getFormandosPorCampo($codCampo = null) {
        $sql = "select codigo_formando as codigo,
                       concat(nome_formando, ' ', apelido_formando) as descricao
                from Formando";
        if ($codCampo !== null && (int) $codCampo > 0) {
            $sql .= " where codCampo = ?";
        }
        $sql .= " order by nome_formando, apelido_formando";
        $stmt = $this->db->prepare($sql);
        if ($codCampo !== null && (int) $codCampo > 0) {
            $stmt->bind_param("i", $codCampo);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $opcoes = [];
        while ($rs = $result->fetch_assoc()) {
            $opcoes[] = ['codigo' => $rs['codigo'], 'descricao' => $rs['descricao']];
        }
        return $opcoes;
    }

    private function registarLog($acao, $descricao) {
        $usuario = Sessao::obterUtilizador();
        if ($usuario != null) {
            $log = new Logs(null, $acao, $descricao, $usuario);
            $log->setData(date('d/m/Y H:i:s'));
            (new LogDao())->salvar($log);
        }
    }
}
?>
