<?php
class Formador {
    private $codigo;
    private $nome;
    private $apelido;
    private $email;
    private $genero;
    private $estadoCivil;
    private $contacto;
    private $valor_horas;
    private $horas_mes;
    private $salario;

    public function __construct($codigo, $nome, $apelido, $email, $genero, $estadoCivil, $contacto, $valor_horas, $horas_mes, $salario)
    {
        $this->codigo = $codigo;
        $this->nome = $nome;
        $this->apelido = $apelido;
        $this->email = $email;
        $this->genero = $genero;
        $this->estadoCivil = $estadoCivil;
        $this->contacto = $contacto;
        $this->valor_horas = $valor_horas;
        $this->horas_mes = $horas_mes;
        $this->salario = $salario;
    }

    public function getCodigo(){ return $this->codigo; }
    public function setCodigo($codigo){ $this->codigo = $codigo; }

    public function getNome(){ return $this->nome; }
    public function setNome($nome){ $this->nome = $nome; }

    public function getApelido(){ return $this->apelido; }
    public function setApelido($apelido){ $this->apelido = $apelido; }

    public function getEmail(){ return $this->email; }
    public function setEmail($email){ $this->email = $email; }

    public function getGenero(){ return $this->genero; }
    public function setGenero($genero){ $this->genero = $genero; }

    public function getEstadoCivil(){ return $this->estadoCivil; }
    public function setEstadoCivil($estadoCivil){ $this->estadoCivil = $estadoCivil; }

    public function getContacto(){ return $this->contacto; }
    public function setContacto($contacto){ $this->contacto = $contacto; }

    public function getValorHoras(){ return $this->valor_horas; }
    public function setValorHoras($valor_horas){ $this->valor_horas = $valor_horas; }

    public function getHorasMes(){ return $this->horas_mes; }
    public function setHorasMes($horas_mes){ $this->horas_mes = $horas_mes; }

    public function getSalario(){ return $this->salario; }
    public function setSalario($salario){ $this->salario = $salario; }
}
?>
