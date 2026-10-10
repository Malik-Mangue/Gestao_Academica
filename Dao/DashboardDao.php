<?php
class DashboardDao {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Contagem real de registos em cada tabela — os numeros do dashboard
    // nunca sao fixos no codigo.
    public function contar($tabela) {
        $permitidas = [
            'Formador', 'Formando', 'Turma', 'Modulo', 'Qualificacao',
            'Nivel', 'Sala', 'Campo', 'Matricula', 'Inscricao', 'Licao', 'Usuario',
        ];
        if (!in_array($tabela, $permitidas, true)) {
            throw new InvalidArgumentException("Tabela nao permitida na contagem: " . $tabela);
        }

        $sql = "select count(*) as total from `" . $tabela . "`";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rs = $stmt->get_result()->fetch_assoc();

        return (int) $rs['total'];
    }

    public function resumo() {
        return [
            'formadores'    => $this->contar('Formador'),
            'formandos'     => $this->contar('Formando'),
            'turmas'        => $this->contar('Turma'),
            'modulos'       => $this->contar('Modulo'),
            'qualificacoes' => $this->contar('Qualificacao'),
            'niveis'        => $this->contar('Nivel'),
            'salas'         => $this->contar('Sala'),
            'campos'        => $this->contar('Campo'),
            'matriculas'    => $this->contar('Matricula'),
            'inscricoes'    => $this->contar('Inscricao'),
            'licoes'        => $this->contar('Licao'),
            'utilizadores'  => $this->contar('Usuario'),
        ];
    }
}
?>