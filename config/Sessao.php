<?php
class Sessao {
    public static function obterUtilizador() {
        return $_SESSION['usuario'] ?? null;
    }
}
?>
