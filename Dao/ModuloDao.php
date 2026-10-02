<?php
class ModuloDao{
    private $db;

    public function __construct(){

    $database = new Database();
    $this->db = $database->getConnection();
    }

    public function create(Modulo $modulo){
        $sql = "insert into Modulo (nome_modulo, carga_horaria, id_Quali_Nivel) values(?,?,?)";
        $stmt = $this->db->prepare($sql);


    $nome_modulo = $modulo->getNome_modulo();
    $carga_horaria = $modulo->getCarga_horaria();
    $id_modulo = $modulo->getId_modulo();

    $stmt->bind_param("sii", $nome_modulo, $carga_horaria, $id_modulo);
    return $stmt->execute();
    }

    public function getAll($nome_modulo = null){
        if($nome_modulo != null && strlen($nome_modulo) > 0){
            $sql = "select * from Formador where nome like ?";
            $stmt = $this->db->prepare($sql);
            $busca = "%" . $nome_modulo . "%";
            $stmt->bind_param("s", $busca);
            $stmt->execute();
            $result = $stmt->get_result();
        }else{
            $sql = "select * from Modulo";
            $result = mysqli_query($this->db, $sql);
        }

        $modulos = [];
        while ($rs = mysqli_fetch_assoc($result)){
            $modulos[] = $this->montarModulo($rs);
        }
        return $modulos;
    }

    public function getById($id_modulo = null){
        $sql = "select * from Modulo where id_modulo";
    }
}

?>
