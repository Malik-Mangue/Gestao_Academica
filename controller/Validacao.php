<?php

/**
 * Trait de validação de dados de entrada.
 *
 * Reúne os validadores que antes viviam na classe de serviço
 * `services/Validador.php`. Em vez de uma classe isolada, estes métodos
 * passam a fazer parte de cada classe que os usa, através de
 * `use Validacao;`:
 *
 *   - dentro do controller, invoca-se $this->texto(...), $this->data(...);
 *   - na view, invoca-se $controller->texto(...), $controller->data(...).
 *
 * Assim a validação fica integrada nas classes atingidas, sem duplicar
 * a mesma regra por vários ficheiros.
 */
trait Validacao {

    /**
     * Texto livre (letras, números e pontuação simples) com limite de caracteres.
     * Rejeita vazio e strings acima de $maximo.
     */
    public function texto($valor, $maximo = 60) {
        $valor = trim((string) $valor);
        if ($valor === '' || mb_strlen($valor) > $maximo) {
            return false;
        }
        return preg_match("/^[\p{L}\p{N}][\p{L}\p{N}\p{Zs}.,'’()\-\/]*$/u", $valor) === 1;
    }

    /**
     * Campo opcional composto apenas por dígitos, com limite de caracteres.
     * Vazio é considerado válido.
     */
    public function digitos($valor, $maximo = 20) {
        $valor = trim((string) $valor);
        if ($valor === '') {
            return true;
        }
        return strlen($valor) <= $maximo && ctype_digit($valor);
    }

    /**
     * E-mail opcional e válido. Vazio é considerado válido.
     */
    public function email($valor) {
        $valor = trim((string) $valor);
        if ($valor === '') {
            return true;
        }
        return mb_strlen($valor) <= 100 && filter_var($valor, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Número inteiro maior que zero.
     */
    public function inteiroPositivo($valor) {
        return filter_var($valor, FILTER_VALIDATE_INT) !== false && (int) $valor > 0;
    }

    /**
     * Data no formato Y-m-d (ex.: 2025-10-16).
     */
    public function data($valor) {
        $valor = trim((string) $valor);
        $data = DateTime::createFromFormat('Y-m-d', $valor);
        return $data !== false && $data->format('Y-m-d') === $valor;
    }

    /**
     * Hora no formato HH:MM (24 horas).
     */
    public function hora($valor) {
        $valor = trim((string) $valor);
        return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $valor) === 1;
    }

    /**
     * E-mail obrigatório e válido.
     */
    public function emailObrigatorio($valor) {
        $valor = trim((string) $valor);
        return $valor !== '' && filter_var($valor, FILTER_VALIDATE_EMAIL) !== false;
    }
}
