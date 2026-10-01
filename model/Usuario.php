<?php

class Usuario {
    private $codigo;
    private $idPerfil;
    private $nome;
    private $username;
    private $apelido;
    private $password;
    private $primeiroAcesso;

    public function __construct($codigo, $idPerfil, $nome, $username, $apelido, $password, $primeiroAcesso)
    {
        $this->codigo = $codigo;
        $this->idPerfil = $idPerfil;
        $this->nome = $nome;
        $this->username = $username;
        $this->apelido = $apelido;
        $this->password = $password;
        $this->primeiroAcesso = $primeiroAcesso;
    }

    public function getCodigo(){ return $this->codigo; }
    public function setCodigo($codigo){ $this->codigo = $codigo; }

    public function getIdPerfil(){ return $this->idPerfil; }
    public function setIdPerfil($idPerfil){ $this->idPerfil = $idPerfil; }

    public function getNome(){ return $this->nome; }
    public function setNome($nome){ $this->nome = $nome; }

    public function getUsername(){ return $this->username; }
    public function setUsername($username){ $this->username = $username; }

    public function getApelido(){ return $this->apelido; }
    public function setApelido($apelido){ $this->apelido = $apelido; }

    public function getPassword(){ return $this->password; }
    public function setPassword($password){ $this->password = $password; }

    public function getPrimeiroAcesso(){ return $this->primeiroAcesso; }
    public function setPrimeiroAcesso($primeiroAcesso){ $this->primeiroAcesso = $primeiroAcesso; }
}

?>
