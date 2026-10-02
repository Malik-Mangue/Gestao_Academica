<?php
class Quali_Nivel {
    private $codigo;
    private $cod_quali;
    private $cod_nivel;
    private $qualificacao;
    private $nivel;

    public function __construct($codigo, $cod_quali, $cod_nivel)
    {
        $this->codigo = $codigo;
        $this->cod_quali = $cod_quali;
        $this->cod_nivel = $cod_nivel;
    }

    public function getCodigo(){ return $this->codigo; }
    public function setCodigo($codigo){ $this->codigo = $codigo; }

    public function getCod_quali(){ return $this->cod_quali; }
    public function setCod_quali($cod_quali){ $this->cod_quali = $cod_quali; }

    public function getCod_nivel(){ return $this->cod_nivel; }
    public function setCod_nivel($cod_nivel){ $this->cod_nivel = $cod_nivel; }

    public function getQualificacao(){ return $this->qualificacao; }
    public function setQualificacao($qualificacao){ $this->qualificacao = $qualificacao; }

    public function getNivel(){ return $this->nivel; }
    public function setNivel($nivel){ $this->nivel = $nivel; }
}
?>
