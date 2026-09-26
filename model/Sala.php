<?php

class Sala {
    private $codigo;
    private $designacao;
    private $tipo_sala;

    public function __construct($codigo, $designacao, $tipo_sala)
    {
        $this->codigo = $codigo;
        $this->designacao = $designacao;
        $this->tipo_sala = $tipo_sala;
    }

    public function getCodigo(){ return $this->codigo; }
    public function setCodigo($codigo){ $this->codigo = $codigo; }

    public function getDesignacao(){ return $this->designacao; }
    public function setDesignacao($designacao){ $this->designacao = $designacao; }

    public function getTipo_sala(){ return $this->tipo_sala; }
    public function setTipo_sala($tipo_sala){ $this->tipo_sala = $tipo_sala; }
}

?>
