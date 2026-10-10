<?php
class Licao {
    private $codigo;
    private $modulo;
    private $formador;
    private $sala;
    private $turma;
    private $data;
    private $hora_inicio;
    private $hora_fim;

    public function __construct($codigo, $modulo, $formador, $sala, $turma, $data, $hora_inicio, $hora_fim)
    {
        $this->codigo = $codigo;
        $this->modulo = $modulo;
        $this->formador = $formador;
        $this->sala = $sala;
        $this->turma = $turma;
        $this->data = $data;
        $this->hora_inicio = $hora_inicio;
        $this->hora_fim = $hora_fim;
    }

    public function getCodigo(){ return $this->codigo; }
    public function setCodigo($codigo){ $this->codigo = $codigo; }

    public function getModulo(){ return $this->modulo; }
    public function setModulo($modulo){ $this->modulo = $modulo; }

    public function getFormador(){ return $this->formador; }
    public function setFormador($formador){ $this->formador = $formador; }

    public function getSala(){ return $this->sala; }
    public function setSala($sala){ $this->sala = $sala; }

    public function getTurma(){ return $this->turma; }
    public function setTurma($turma){ $this->turma = $turma; }

    public function getData(){ return $this->data; }
    public function setData($data){ $this->data = $data; }

    public function getHoraInicio(){ return $this->hora_inicio; }
    public function setHoraInicio($hora_inicio){ $this->hora_inicio = $hora_inicio; }

    public function getHoraFim(){ return $this->hora_fim; }
    public function setHoraFim($hora_fim){ $this->hora_fim = $hora_fim; }
}
?>
