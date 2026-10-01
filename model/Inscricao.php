<?php
class Inscricao {
    private $codigo;
    private $formando;
    private $modulo;
    private $semestre;
    private $data_inscricao;

    public function __construct($codigo, $formando, $modulo, $semestre, $data_inscricao)
    {
        $this->codigo = $codigo;
        $this->formando = $formando;
        $this->modulo = $modulo;
        $this->semestre = $semestre;
        $this->data_inscricao = $data_inscricao;
    }

    public function getCodigo(){ return $this->codigo; }
    public function setCodigo($codigo){ $this->codigo = $codigo; }

    public function getFormando(){ return $this->formando; }
    public function setFormando($formando){ $this->formando = $formando; }

    public function getModulo(){ return $this->modulo; }
    public function setModulo($modulo){ $this->modulo = $modulo; }

    public function getSemestre(){ return $this->semestre; }
    public function setSemestre($semestre){ $this->semestre = $semestre; }

    public function getDataInscricao(){ return $this->data_inscricao; }
    public function setDataInscricao($data_inscricao){ $this->data_inscricao = $data_inscricao; }
}
?>
