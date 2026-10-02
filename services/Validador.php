<?php
/**
 * Regras de validacao partilhadas pelos controllers. Centraliza as regras
 * de formato para que nenhum controller use expressoes regulares rigidas
 * que rejeitem nomes portugueses com acentos ou apostrofos.
 */
final class Validador {

    // Nome/apelido/titulo: letras (com acentos), apostrofos, espaco e hifen.
    public static function texto($valor, $maximo = 60) {
        $valor = trim((string) $valor);
        if ($valor === '' || mb_strlen($valor) > $maximo) {
            return false;
        }
        return preg_match("/^[\p{L}\p{N}][\p{L}\p{N}\p{Zs}.,'’()\-\/]*$/u", $valor) === 1;
    }

    // Apenas digitos, com tamanho maximo opcional.
    public static function digitos($valor, $maximo = 20) {
        $valor = trim((string) $valor);
        if ($valor === '') {
            return true;
        }
        return strlen($valor) <= $maximo && ctype_digit($valor);
    }

    public static function email($valor) {
        $valor = trim((string) $valor);
        if ($valor === '') {
            return true;
        }
        return mb_strlen($valor) <= 100 && filter_var($valor, FILTER_VALIDATE_EMAIL) !== false;
    }

    // Inteiro positivo (codigos, cargas horarias, salarios...).
    public static function inteiroPositivo($valor) {
        return filter_var($valor, FILTER_VALIDATE_INT) !== false && (int) $valor > 0;
    }

    // Data no formato AAAA-MM-DD.
    public static function data($valor) {
        $valor = trim((string) $valor);
        $data = DateTime::createFromFormat('Y-m-d', $valor);
        return $data !== false && $data->format('Y-m-d') === $valor;
    }

    // Hora no formato HH:MM.
    public static function hora($valor) {
        $valor = trim((string) $valor);
        return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $valor) === 1;
    }

    public static function emailObrigatorio($valor) {
        $valor = trim((string) $valor);
        return $valor !== '' && filter_var($valor, FILTER_VALIDATE_EMAIL) !== false;
    }
}
?>