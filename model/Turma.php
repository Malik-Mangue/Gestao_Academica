<?php

class Turma {
    private $codigo;
    private $nome;
    private $ano_lectivo;
    private $turno;
    private $id_diretor_turma;
    private $id_quali_nivel;

    public function __construct($codigo, $nome, $ano_lectivo, $turno, $id_diretor_turma, $id_quali_nivel)
    {
        $this->codigo = $codigo;
        $this->nome = $nome;
        $this->ano_lectivo = $ano_lectivo;
        $this->turno = $turno;
        $this->id_diretor_turma = $id_diretor_turma;
        $this->id_quali_nivel = $id_quali_nivel;
    }

    public function getCodigo(){ return $this->codigo; }
    public function setCodigo($codigo){ $this->codigo = $codigo; }

    public function getNome(){ return $this->nome; }
    public function setNome($nome){ $this->nome = $nome; }

    public function getAno_lectivo(){ return $this->ano_lectivo; }
    public function setAno_lectivo($ano_lectivo){ $this->ano_lectivo = $ano_lectivo; }

    public function getTurno(){ return $this->turno; }
    public function setTurno($turno){ $this->turno = $turno; }

    public function getId_diretor_turma(){ return $this->id_diretor_turma; }
    public function setId_diretor_turma($id_diretor_turma){ $this->id_diretor_turma = $id_diretor_turma; }

    public function getId_quali_nivel(){ return $this->id_quali_nivel; }
    public function setId_quali_nivel($id_quali_nivel){ $this->id_quali_nivel = $id_quali_nivel; }
}

?>
