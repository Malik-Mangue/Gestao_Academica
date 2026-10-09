<?php

require_once __DIR__ . '/Perfil.php';

class Usuario {
    private $codigo;
    private $idPerfil;
    private $nome;
    private $username;
    private $password;
    private $apelido;
    private $estadoCivil;
    private $genero;
    private $telefone;
    private $email;
    private $bi;
    private $perfil;
    private $primeiroAcesso;

    // Ordem canonica usada por todos os chamadores (Dao, Controller e Sessao):
    // (codigo, idPerfil, nome, username, apelido, password, primeiroAcesso,
    //  estadoCivil, genero, telefone, email, bi, perfil)
    // Os campos a partir de primeiroAcesso sao opcionais (default null).
    public function __construct($codigo = null, $idPerfil = null, $nome = null, $username = null, $apelido = null, $password = null, $primeiroAcesso = 0, $estadoCivil = null, $genero = null, $telefone = null, $email = null, $bi = null, $perfil = null)
    {
        $this->codigo = $codigo;
        $this->idPerfil = $idPerfil;
        $this->nome = $nome;
        $this->username = $username;
        $this->apelido = $apelido;
        $this->password = $password;
        $this->primeiroAcesso = $primeiroAcesso;
        $this->estadoCivil = $estadoCivil;
        $this->genero = $genero;
        $this->telefone = $telefone;
        $this->email = $email;
        $this->bi = $bi;
        $this->perfil = $perfil;
    }

    public function isPrimeiroAcesso(){ return $this->primeiroAcesso; }
    public function getPrimeiroAcesso(){ return $this->primeiroAcesso; }
    public function setPrimeiroAcesso($primeiroAcesso){ $this->primeiroAcesso = $primeiroAcesso; }

    public function getIdPerfil(){ return $this->idPerfil; }
    public function setIdPerfil($idPerfil){ $this->idPerfil = $idPerfil; }

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

    public function getEstadoCivil(){ return $this->estadoCivil; }
    public function setEstadoCivil($estadoCivil){ $this->estadoCivil = $estadoCivil; }

    public function getGenero(){ return $this->genero; }
    public function setGenero($genero){ $this->genero = $genero; }

    public function getTelefone(){ return $this->telefone; }
    public function setTelefone($telefone){ $this->telefone = $telefone; }

    public function getEmail(){ return $this->email; }
    public function setEmail($email){ $this->email = $email; }

    public function getBi(){ return $this->bi; }
    public function setBi($bi){ $this->bi = $bi; }

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