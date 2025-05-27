<?php

namespace Axdron\Radianti\Services;

use Adianti\Widget\Base\TScript;
use Adianti\Widget\Form\TEntry;

class RadiantiMascaras
{
    static function mascararCPFCNPJDinamicamente(TEntry $campo)
    {
        $campo->class = 'tfield cnpj';
        $nomeCampo = $campo->getName();
        TScript::create("		
			var masks = {'CPF': '000.000.000-000000', 'CNPJ': '00.000.000/0000-00'};		
			var cpfCnpj = $('input[name=\"{$nomeCampo}\"]');	
			var options = {
				onChange: function (cpfCnpj, ev, el, op) {
					this.adicionarMascaraCpfCnpj(el);
				}
			};

			function adicionarMascaraCpfCnpj(campo){	
				const cpfCnpj = campo.val().replace(/[./-]/g, '');
				campo.mask((cpfCnpj.length > 11) ? masks['CNPJ'] : masks['CPF'], options);
			}

			adicionarMascaraCpfCnpj(cpfCnpj);
		");
    }

    /**
     * Formata um telefone no formato (XX) XXXXX-XXXX.
     *
     * @param string $telefone O telefone a ser formatado.
     * @return string O telefone formatado.
     */
    static function mascararTelefone(string $telefone): string
    {
        $telefone = preg_replace('/\D/', '', $telefone);
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
    }
}
