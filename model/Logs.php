<?php
class Logs {
    private $codigo;
    private $acao;
    private $descricao;
    private $usuario;
    private $data;

    public function __construct($codigo, $acao, $descricao, $usuario)
    {
        $this->codigo = $codigo;
        $this->acao = $acao;
        $this->descricao = $descricao;
        $this->usuario = $usuario;
    }

    public function getCodigo(){ return $this->codigo; }
    public function setCodigo($codigo){ $this->codigo = $codigo; }

    public function getAcao(){ return $this->acao; }
    public function setAcao($acao){ $this->acao = $acao; }

    public function getDescricao(){ return $this->descricao; }
    public function setDescricao($descricao){ $this->descricao = $descricao; }

    public function getUsuario(){ return $this->usuario; }
    public function setUsuario($usuario){ $this->usuario = $usuario; }

    public function getData(){ return $this->data; }
    public function setData($data){ $this->data = $data; }
}
?>
