<?php
require_once __DIR__ . '/Perfil.php';

class Usuario {
    private $codigo;
    private $nome;
    private $username;
    private $password;
    private $apelido;
    private $perfil;
    private $primeiroAcesso;

    public function __construct($codigo = null, $nome = null, $username = null, $password = null, $apelido = null, $perfil = null, $primeiroAcesso = false)
    {
        $this->codigo = $codigo;
        $this->nome = $nome;
        $this->username = $username;
        $this->password = $password;
        $this->apelido = $apelido;
        $this->perfil = $perfil;
        $this->primeiroAcesso = $primeiroAcesso;
    }

    public function isPrimeiroAcesso(){ return $this->primeiroAcesso; }
    public function setPrimeiroAcesso($primeiroAcesso){ $this->primeiroAcesso = $primeiroAcesso; }

    public function getCodigo(){ return $this->codigo; }
    public function setCodigo($codigo){ $this->codigo = $codigo; }

    public function getUsername(){ return $this->username; }
    public function setUsername($username){ $this->username = $username; }

    public function getPassword(){ return $this->password; }
    public function setPassword($password){ $this->password = $password; }

    public function getNome(){ return $this->nome; }
    public function setNome($nome){ $this->nome = $nome; }

    public function getApelido(){ return $this->apelido; }
    public function setApelido($apelido){ $this->apelido = $apelido; }

    public function getPerfil(){ return $this->perfil; }
    public function setPerfil($perfil){ $this->perfil = $perfil; }

    public function __toString()
    {
        $idPerfil = $this->perfil != null ? $this->perfil->getId() : '';
        $nomePerfil = $this->perfil != null ? $this->perfil->getNome() : '';
        return "Usuario: Codigo: " . $this->codigo . " Username: " . $this->username . " Apelido: " . $this->apelido
            . " password: " . $this->password . " primeiroAcesso: " . ($this->primeiroAcesso ? 'true' : 'false')
            . " idPerfil: " . $idPerfil . " NomePerfil: " . $nomePerfil;
    }
}
?>
