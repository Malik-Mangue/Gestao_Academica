<?php
require_once __DIR__ . '/Perfil.php';

class Usuario {
    private $codigo;
    private $idPerfil;
    private $nome;
    private $username;
    private $password;
    private $apelido;
    private $perfil;
    private $primeiroAcesso;

    // Ordem canonica usada por todos os chamadores (Dao, Controller e Sessao):
    // (codigo, idPerfil, nome, username, apelido, password, primeiroAcesso)
    public function __construct($codigo = null, $idPerfil = null, $nome = null, $username = null, $apelido = null, $password = null, $primeiroAcesso = 0)
    {
        $this->codigo = $codigo;
        $this->idPerfil = $idPerfil;
        $this->nome = $nome;
        $this->username = $username;
        $this->apelido = $apelido;
        $this->password = $password;
        $this->primeiroAcesso = $primeiroAcesso;
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
