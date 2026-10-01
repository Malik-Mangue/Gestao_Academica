<?php
class Database {
    private $host = "127.0.0.1";
    private $db_name = "GestaoProfessor";
    private $user_name = "root";
    private $password = "846533793";

    private static $conn = null;

    public function getConnection(){
        if (self::$conn === null) {
            try {
                self::$conn = new mysqli($this->host, $this->user_name, $this->password, $this->db_name);
                if (self::$conn->connect_error) {
                    throw new Exception("Erro de conexao: " . self::$conn->connect_error);
                }
            } catch (Exception $e) {
                die("Falha na base de dados: " . $e->getMessage());
            }
        }
        return self::$conn;
    }
}
?>
