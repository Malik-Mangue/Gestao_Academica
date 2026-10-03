<?php
require_once __DIR__ . '/../Dao/DashboardDao.php';

class DashboardController {
    private $dao;

    public function __construct() {
        $this->dao = new DashboardDao();
    }

    // Numeros do dashboard sempre lidos da base de dados.
    public function resumo() {
        return $this->dao->resumo();
    }

    public function contar($tabela) {
        return $this->dao->contar($tabela);
    }
}
?>