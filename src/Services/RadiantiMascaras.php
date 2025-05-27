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
}
