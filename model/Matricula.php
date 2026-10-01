<?php
class Matricula {
    private $codigo;
    private $formando;
    private $qualificacao;
    private $nivel;
    private $data_matricula;

    public function __construct($codigo, $formando, $qualificacao, $nivel, $data_matricula)
    {
        $this->codigo = $codigo;
        $this->formando = $formando;
        $this->qualificacao = $qualificacao;
        $this->nivel = $nivel;
        $this->data_matricula = $data_matricula;
    }

    public function getCodigo(){ return $this->codigo; }
    public function setCodigo($codigo){ $this->codigo = $codigo; }

    public function getFormando(){ return $this->formando; }
    public function setFormando($formando){ $this->formando = $formando; }

    public function getQualificacao(){ return $this->qualificacao; }
    public function setQualificacao($qualificacao){ $this->qualificacao = $qualificacao; }

    public function getNivel(){ return $this->nivel; }
    public function setNivel($nivel){ $this->nivel = $nivel; }

    public function getDataMatricula(){ return $this->data_matricula; }
    public function setDataMatricula($data_matricula){ $this->data_matricula = $data_matricula; }
}
?>
