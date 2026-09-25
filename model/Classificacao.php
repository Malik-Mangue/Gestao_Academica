<?php

class Classificacao {
    private $codigo;
    private $campo;
    private $qualificacao;

    public function __construct($codigo, $campo, $qualificacao)
    {
        $this->codigo = $codigo;
        $this->campo = $campo;
        $this->qualificacao = $qualificacao;
    }

    public function getCodigo(){ return $this->codigo; }
    public function setCodigo($codigo){ $this->codigo = $codigo; }

    public function getCampo(){ return $this->campo; }
    public function setCampo($campo){ $this->campo = $campo; }

    public function getQualificacao(){ return $this->qualificacao; }
    public function setQualificacao($qualificacao){ $this->qualificacao = $qualificacao; }
}

?>
