<?php
class Recurso {
    private $id;
    private $nome;
    private $grupo;

    public function __construct($id = null, $nome = null, $grupo = null)
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->grupo = $grupo;
    }

    public function getId(){ return $this->id; }
    public function setId($id){ $this->id = $id; }

    public function getNome(){ return $this->nome; }
    public function setNome($nome){ $this->nome = $nome; }

    public function getGrupo(){ return $this->grupo; }
    public function setGrupo($grupo){ $this->grupo = $grupo; }

    public function __toString()
    {
        return (string) $this->nome;
    }
}
?>
