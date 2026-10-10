<?php
class Modulo {
    private $codigo;
    private $nome;
    private $carga_horaria;
    private $quali_modulo;
    private $quali_nivel;

    public function __construct($codigo, $nome, $carga_horaria)
    {
        $this->codigo = $codigo;
        $this->nome = $nome;
        $this->carga_horaria = $carga_horaria;
    }

    public function getCodigo(){ return $this->codigo; }
    public function setCodigo($codigo){ $this->codigo = $codigo; }

    public function getNome(){ return $this->nome; }
    public function setNome($nome){ $this->nome = $nome; }

    public function getCargaHoraria(){ return $this->carga_horaria; }
    public function setCargaHoraria($carga_horaria){ $this->carga_horaria = $carga_horaria; }

    public function getQualiModulo(){ return $this->quali_modulo; }
    public function setQualiModulo($quali_modulo){ $this->quali_modulo = $quali_modulo; }

    public function getQualiNivel(){ return $this->quali_nivel; }
    public function setQualiNivel($quali_nivel){ $this->quali_nivel = $quali_nivel; }

    public function __toString()
    {
        return "Modulo [codigo=" . $this->codigo . ", nome=" . $this->nome . ", carga_horaria=" . $this->carga_horaria . "]";
    }
}
?>
