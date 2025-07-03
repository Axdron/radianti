<?php

namespace Axdron\Radianti\Componentes;

use Adianti\Widget\Form\TNumeric;

class RadiantiElementoDinheiro extends TNumeric
{
    public function __construct($nome, $casasDecimais = 2, $separadorDecimais = ',', $separadorMilhares = '.', $snAtualizaAoEfetuarPost = true, $snPreenchimentoEsquerdaParaDireita = false, $snPermiteNegativo = false)
    {
        parent::__construct($nome, $casasDecimais, $separadorDecimais, $separadorMilhares, $snAtualizaAoEfetuarPost, $snPreenchimentoEsquerdaParaDireita, $snPermiteNegativo);
    }
}
