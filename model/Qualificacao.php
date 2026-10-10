<?php

class Qualificacao {
private $codigo;
private $titulo;
private $cod_coordenador;
private $campo = null;
private $niveis = null;   

public function __construct($codigo, $titulo, $cod_coordenador)
    {
$this->codigo = $codigo;
$this->titulo = $titulo;
$this->cod_coordenador = $cod_coordenador;
    }

public function getCodigo(){ return $this->codigo; }
public function setCodigo($codigo){ $this->codigo = $codigo; }

public function getTitulo(){ return $this->titulo; }
public function setTitulo($titulo){ $this->titulo = $titulo; }

public function getCod_coordenador(){ return $this->cod_coordenador; }
public function setCod_coordenador($cod_coordenador){ $this->cod_coordenador = $cod_coordenador; }

// NOVO: nome do campo da qualificação (vem da tabela Classificacao/Campo)
public function getCampo(){ return $this->campo; }
public function setCampo($campo){ $this->campo = $campo; }

// NOVO: nomes dos níveis da qualificação, separados por vírgula (vêm de Quali_Nivel/Nivel)
public function getNiveis(){ return $this->niveis; }
public function setNiveis($niveis){ $this->niveis = $niveis; }
}

?>
