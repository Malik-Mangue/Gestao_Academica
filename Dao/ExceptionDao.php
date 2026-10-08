<?php
class ExceptionDao extends Exception {
    public function __construct($mensagem) {
        parent::__construct($mensagem);
    }
}
?>
