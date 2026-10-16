<?php
require_once __DIR__ . '/../Dao/DashboardDao.php';

class DashboardController {
    private $dao;

    public function __construct() {
        $this->dao = new DashboardDao();
    }

    public function resumo() {
        return $this->dao->resumo();
    }

    public function contar($tabela) {
        return $this->dao->contar($tabela);
    }
}
?>