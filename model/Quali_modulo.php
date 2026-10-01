<?php

class Quali_modulo {
    private $codigo;
    private $cod_modulo;
    private $cod_quali;
    private $semestre;

    public function __construct($codigo, $cod_modulo, $cod_quali, $semestre)
    {
        $this->codigo = $codigo;
        $this->cod_modulo = $cod_modulo;
        $this->cod_quali = $cod_quali;
        $this->semestre = $semestre;
    }

    public function getCodigo(){ return $this->codigo; }
    public function setCodigo($codigo){ $this->codigo = $codigo; }

    public function getCod_modulo(){ return $this->cod_modulo; }
    public function setCod_modulo($cod_modulo){ $this->cod_modulo = $cod_modulo; }

    public function getCod_quali(){ return $this->cod_quali; }
    public function setCod_quali($cod_quali){ $this->cod_quali = $cod_quali; }

    public function getSemestre(){ return $this->semestre; }
    public function setSemestre($semestre){ $this->semestre = $semestre; }
}

?>
