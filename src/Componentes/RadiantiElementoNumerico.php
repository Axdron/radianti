<?php

namespace Axdron\Radianti\Componentes;

use Adianti\Widget\Form\TNumeric;

class RadiantiElementoNumerico extends TNumeric
{
    public function __construct(string $nome, $casasDecimais = 2, $separadorDecimais = ',', $separadorMilhares = '.', $snAtualizaAoEfetuarPost = true, $snPreenchimentoEsquerdaParaDireita = false, $snPermiteNegativo = false)
    {
        parent::__construct($nome, $casasDecimais, $separadorDecimais, $separadorMilhares, $snAtualizaAoEfetuarPost, $snPreenchimentoEsquerdaParaDireita, $snPermiteNegativo);
    }
}
