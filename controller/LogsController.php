<?php
require_once __DIR__ . '/../Dao/LogDao.php';
require_once __DIR__ . '/../model/Logs.php';

class LogController {
    private $dao;

    public function __construct() {
        $this->dao = new LogDao();
    }

    public function listarLogs($filtro = null) {
        if ($filtro != null && strlen($filtro) > 0) {
            return $this->dao->listarLogsComFiltro($filtro);
        }
        return $this->dao->listarLogs();
    }
}
?>
