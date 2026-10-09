<?php
require_once __DIR__ . '/../model/Usuario.php';

class FicheiroDefault {

    public static function criarFicheiro($usuario) {
        $nome = $usuario->getNome();
        $username = $usuario->getUsername();
        $perfil = $usuario->getPerfil()->getNome();
        $senha = $usuario->getPassword();
        $tituloFicheiro = $nome;

        $pasta = __DIR__ . '/../senhasDefault';
        if (!is_dir($pasta)) {
            mkdir($pasta, 0777, true);
        }

        $caminho = $pasta . '/' . $tituloFicheiro . '.txt';
        $conteudo = "Nome: " . $nome . "\n";
        $conteudo .= "Username: " . $username . "\n";
        $conteudo .= "Perfil: " . $perfil . "\n";
        $conteudo .= "Senha : " . $senha . "\n";

        file_put_contents($caminho, $conteudo);
    }

    public static function apagarFicheiro($nome) {
        $caminho = __DIR__ . '/../senhasDefault/' . $nome . '.txt';
        if (file_exists($caminho)) {
            return unlink($caminho);
        }
        return false;
    }
}
?>
