<?php
class Permissao {
    private $id;
    private $nome;

    public function __construct($id = null, $nome = null)
    {
        $this->id = $id;
        $this->nome = $nome;
    }

    public function getId(){ return $this->id; }
    public function setId($id){ $this->id = $id; }

    public function getNome(){ return $this->nome; }
    public function setNome($nome){ $this->nome = $nome; }

    public function __toString()
    {
        return (string) $this->nome;
    }
}
?>
