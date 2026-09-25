<?php

class Formando {
    private $codigo;
    private $nome;
    private $apelido;
    private $contacto;
    private $email;
    private $bi;

    public function __construct($codigo, $nome, $apelido, $contacto, $email, $bi)
    {
        $this->codigo = $codigo;
        $this->nome = $nome;
        $this->apelido = $apelido;
        $this->contacto = $contacto;
        $this->email = $email;
        $this->bi = $bi;
    }

    public function getCodigo(){ return $this->codigo; }
    public function setCodigo($codigo){ $this->codigo = $codigo; }

    public function getNome(){ return $this->nome; }
    public function setNome($nome){ $this->nome = $nome; }

    public function getApelido(){ return $this->apelido; }
    public function setApelido($apelido){ $this->apelido = $apelido; }

    public function getContacto(){ return $this->contacto; }
    public function setContacto($contacto){ $this->contacto = $contacto; }

    public function getEmail(){ return $this->email; }
    public function setEmail($email){ $this->email = $email; }

    public function getBi(){ return $this->bi; }
    public function setBi($bi){ $this->bi = $bi; }
}

?>
