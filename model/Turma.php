<?php
class Turma {
    private $codigo;
    private $nome;
    private $ano_ingresso;
    private $turno;
    private $diretor_turma;
    private $qualificacao;
    private $quali_nivel;

    public function __construct($codigo, $nome, $ano_ingresso, $turno, $diretor_turma = null, $qualificacao = null, $quali_nivel = null)
    {
        $this->codigo = $codigo;
        $this->nome = $nome;
        $this->ano_ingresso = $ano_ingresso;
        $this->turno = $turno;
        $this->diretor_turma = $diretor_turma;
        $this->qualificacao = $qualificacao;
        $this->quali_nivel = $quali_nivel;
    }

    public function getCodigo(){ return $this->codigo; }
    public function setCodigo($codigo){ $this->codigo = $codigo; }

    public function getNome(){ return $this->nome; }
    public function setNome($nome){ $this->nome = $nome; }

    public function getAnoIngresso(){ return $this->ano_ingresso; }
    public function setAnoIngresso($ano_ingresso){ $this->ano_ingresso = $ano_ingresso; }

    public function getTurno(){ return $this->turno; }
    public function setTurno($turno){ $this->turno = $turno; }

    public function getDiretorTurma(){ return $this->diretor_turma; }
    public function setDiretorTurma($diretor_turma){ $this->diretor_turma = $diretor_turma; }

    public function getQualificacao(){ return $this->qualificacao; }
    public function setQualificacao($qualificacao){ $this->qualificacao = $qualificacao; }

    public function getQualiNivel(){ return $this->quali_nivel; }
    public function setQualiNivel($quali_nivel){ $this->quali_nivel = $quali_nivel; }
}
?>


