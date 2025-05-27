<?php

namespace Axdron\Radianti\Componentes;

use Adianti\Widget\Form\TEntry;
use Axdron\Radianti\Services\RadiantiMascaras;
use Axdron\Radianti\Services\RadiantiValidacoes;

class RadiantiElementoCPFCNPJ extends TEntry
{


    public function __construct($name = 'cpf_cnpj')
    {
        parent::__construct($name);
        RadiantiMascaras::mascararCPFCNPJDinamicamente($this);
    }

    /**
     * Valida um CPF ou CNPJ.
     *
     * @param string $cpfCnpj O CPF ou CNPJ a ser validado.
     * @return bool Retorna true se o CPF/CNPJ for válido, caso contrário, false.
     */
    public static function validar(string $cpfCnpj): bool
    {
        return RadiantiValidacoes::validarCPFCNPJ($cpfCnpj);
    }
}
