<?php
class Quali_modulo {
    private $codigo;
    private $semestre;
    private $modulo;
    private $qualificacao;

    public function __construct($codigo, $semestre, $modulo, $qualificacao)
    {
        $this->codigo = $codigo;
        $this->semestre = $semestre;
        $this->modulo = $modulo;
        $this->qualificacao = $qualificacao;
    }

    public function getCodigo(){ return $this->codigo; }
    public function setCodigo($codigo){ $this->codigo = $codigo; }

    public function getSemestre(){ return $this->semestre; }
    public function setSemestre($semestre){ $this->semestre = $semestre; }

    public function getModulo(){ return $this->modulo; }
    public function setModulo($modulo){ $this->modulo = $modulo; }

    public function getQualificacao(){ return $this->qualificacao; }
    public function setQualificacao($qualificacao){ $this->qualificacao = $qualificacao; }
}
?>
