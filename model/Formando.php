<?php
class Formando {
    private $codigo;
    private $nome;
    private $apelido;
    private $email;
    private $bi;
    private $contacto;
    private $codCampo = null;
    private $campo = null;

    public function __construct($codigo, $nome, $apelido, $email, $bi, $contacto)
    {
        $this->codigo = $codigo;
        $this->nome = $nome;
        $this->apelido = $apelido;
        $this->email = $email;
        $this->bi = $bi;
        $this->contacto = $contacto;
    }

    public function getCodigo() { return $this->codigo; }
    public function setCodigo($codigo) { $this->codigo = $codigo; }

    public function getNome() { return $this->nome; }
    public function setNome($nome) { $this->nome = $nome; }

    public function getApelido() { return $this->apelido; }
    public function setApelido($apelido) { $this->apelido = $apelido; }

    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = $email; }

    public function getBi() { return $this->bi; }
    public function setBi($bi) { $this->bi = $bi; }

    public function getContacto() { return $this->contacto; }
    public function setContacto($contacto) { $this->contacto = $contacto; }

    public function getCodCampo() { return $this->codCampo; }
    public function setCodCampo($codCampo) { $this->codCampo = $codCampo; }

    public function getCampo() { return $this->campo; }
    public function setCampo($campo) { $this->campo = $campo; }

    public function getCampoNome() { return $this->campo ? $this->campo->getNome() : null; }
}
?>
