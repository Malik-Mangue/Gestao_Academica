<?php
class Turma_Formando {
    private $cod_turma;
    private $cod_matricula;
    private $data_inscricao;
    private $turma;
    private $formando;

    public function __construct($cod_turma, $cod_matricula, $data_inscricao = null, $turma = null, $formando = null) {
        $this->cod_turma = $cod_turma;
        $this->cod_matricula = $cod_matricula;
        $this->data_inscricao = $data_inscricao;
        $this->turma = $turma;
        $this->formando = $formando;
    }

    public function getCodTurma(){ return $this->cod_turma; }
    public function setCodTurma($v){ $this->cod_turma = $v; }

    public function getCodMatricula(){ return $this->cod_matricula; }
    public function setCodMatricula($v){ $this->cod_matricula = $v; }

    public function getDataInscricao(){ return $this->data_inscricao; }
    public function setDataInscricao($v){ $this->data_inscricao = $v; }

    public function getTurma(){ return $this->turma; }
    public function setTurma($v){ $this->turma = $v; }

    public function getFormando(){ return $this->formando; }
    public function setFormando($v){ $this->formando = $v; }
}
?>
