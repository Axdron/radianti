<?php

namespace Axdron\Radianti\Componentes;

use Adianti\Widget\Form\TNumeric;

/**
 * Elemento Numérico para Peso
 * 
 * 3 casas decimais com separador brasileiro (vírgula)
 */
class RadiantiElementoNumericoPeso extends TNumeric
{
    public function __construct($nome, $snAtualizaAoEfetuarPost = true, $snPreenchimentoEsquerdaParaDireita = false, $snPermiteNegativo = false)
    {
        parent::__construct(
            $nome,
            casasDecimais: 3,
            separadorDecimais: ',',
            separadorMilhares: '.',
            snAtualizaAoEfetuarPost: $snAtualizaAoEfetuarPost,
            snPreenchimentoEsquerdaParaDireita: $snPreenchimentoEsquerdaParaDireita,
            snPermiteNegativo: $snPermiteNegativo
        );
    }
}
