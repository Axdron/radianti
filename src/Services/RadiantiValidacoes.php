<?php

namespace Axdron\Radianti\Services;

use Adianti\Validator\TCNPJValidator;
use Adianti\Validator\TCPFValidator;

class RadiantiValidacoes
{

    /**
     * Valida um CPF ou CNPJ.
     *
     * @param string $cpfCnpj O CPF ou CNPJ a ser validado.
     * @return bool Retorna true se o CPF/CNPJ for válido, caso contrário, false.
     */
    public static function validarCPFCNPJ(string $cpfCnpj): bool
    {
        try {
            $cpfCnpj = preg_replace('/\D/', '', $cpfCnpj);

            $validador = strlen($cpfCnpj) === 11 ? new TCPFValidator() : new TCNPJValidator();
            $validador->validate('', $cpfCnpj);

            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }
}
