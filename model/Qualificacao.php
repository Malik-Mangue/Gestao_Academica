<?php

class Qualificacao {
    private $codigo;
    private $titulo;
    private $cod_coordenador;

    public function __construct($codigo, $titulo, $cod_coordenador)
    {
        $this->codigo = $codigo;
        $this->titulo = $titulo;
        $this->cod_coordenador = $cod_coordenador;
    }

    public function getCodigo(){ return $this->codigo; }
    public function setCodigo($codigo){ $this->codigo = $codigo; }

    public function getTitulo(){ return $this->titulo; }
    public function setTitulo($titulo){ $this->titulo = $titulo; }

    public function getCod_coordenador(){ return $this->cod_coordenador; }
    public function setCod_coordenador($cod_coordenador){ $this->cod_coordenador = $cod_coordenador; }
}

?>
