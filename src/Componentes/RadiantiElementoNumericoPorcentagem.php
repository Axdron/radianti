<?php

namespace Axdron\Radianti\Componentes;

use Adianti\Widget\Form\TNumeric;

/**
 * Elemento Numérico para Porcentagem
 * 
 * 2 casas decimais com separador brasileiro (vírgula)
 */
class RadiantiElementoNumericoPorcentagem extends TNumeric
{
    public function __construct($nome, $snAtualizaAoEfetuarPost = true, $snPreenchimentoEsquerdaParaDireita = false, $snPermiteNegativo = false)
    {
        parent::__construct(
            $nome,
            casasDecimais: 2,
            separadorDecimais: ',',
            separadorMilhares: '.',
            snAtualizaAoEfetuarPost: $snAtualizaAoEfetuarPost,
            snPreenchimentoEsquerdaParaDireita: $snPreenchimentoEsquerdaParaDireita,
            snPermiteNegativo: $snPermiteNegativo
        );
    }
}
