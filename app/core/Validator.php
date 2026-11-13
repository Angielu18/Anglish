<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Validación del lado servidor.
 *
 * Nunca se confía en la validación HTML5: las reglas se repiten aquí.
 * Formato de reglas: 'campo' => 'required|email|max:120'.
 */
final class Validator
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $rules
     * @param array<string, string> $labels
     * @return array<string, string> Errores indexados por campo
     */
    public static function validate(array $data, array $rules, array $labels = []): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;
            $texto = is_scalar($value) ? trim((string) $value) : '';
            $label = $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));

            foreach (explode('|', $ruleSet) as $rule) {
                $parts = explode(':', $rule, 2);
                $name  = $parts[0];
                $param = $parts[1] ?? null;

                if ($name !== 'required' && $texto === '') {
                    continue; // Los campos opcionales vacíos no se validan más.
                }

                if ($name === 'required' && $texto === '') {
                    $errors[$field] = 'El campo ' . mb_strtolower($label, 'UTF-8') . ' es obligatorio.';
                    break;
                }

                if ($name === 'email' && filter_var($texto, FILTER_VALIDATE_EMAIL) === false) {
                    $errors[$field] = 'El campo ' . mb_strtolower($label, 'UTF-8') . ' no tiene un formato válido.';
                    break;
                }

                if ($name === 'min' && mb_strlen($texto, 'UTF-8') < (int) $param) {
                    $errors[$field] = 'El campo ' . mb_strtolower($label, 'UTF-8') . ' debe tener al menos ' . (int) $param . ' caracteres.';
                    break;
                }

                if ($name === 'max' && mb_strlen($texto, 'UTF-8') > (int) $param) {
                    $errors[$field] = 'El campo ' . mb_strtolower($label, 'UTF-8') . ' no debe superar ' . (int) $param . ' caracteres.';
                    break;
                }

                if ($name === 'numeric' && !is_numeric($texto)) {
                    $errors[$field] = 'El campo ' . mb_strtolower($label, 'UTF-8') . ' debe ser numérico.';
                    break;
                }

                if ($name === 'in' && $param !== null) {
                    $permitidos = explode(',', $param);
                    if (!in_array($texto, $permitidos, true)) {
                        $errors[$field] = 'El campo ' . mb_strtolower($label, 'UTF-8') . ' no contiene un valor permitido.';
                        break;
                    }
                }

                if ($name === 'same' && $param !== null) {
                    $otro = isset($data[$param]) && is_scalar($data[$param]) ? trim((string) $data[$param]) : '';
                    if ($texto !== $otro) {
                        $errors[$field] = 'La confirmación de ' . mb_strtolower($label, 'UTF-8') . ' no coincide.';
                        break;
                    }
                }
            }
        }

        return $errors;
    }
}
