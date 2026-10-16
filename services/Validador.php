<?php
final class Validador {

    public static function texto($valor, $maximo = 60) {
        $valor = trim((string) $valor);
        if ($valor === '' || mb_strlen($valor) > $maximo) {
            return false;
        }
        return preg_match("/^[\p{L}\p{N}][\p{L}\p{N}\p{Zs}.,'’()\-\/]*$/u", $valor) === 1;
    }

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

    public static function inteiroPositivo($valor) {
        return filter_var($valor, FILTER_VALIDATE_INT) !== false && (int) $valor > 0;
    }

    public static function data($valor) {
        $valor = trim((string) $valor);
        $data = DateTime::createFromFormat('Y-m-d', $valor);
        return $data !== false && $data->format('Y-m-d') === $valor;
    }

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