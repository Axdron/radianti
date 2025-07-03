<?php

namespace Axdron\Radianti\Componentes;

use Adianti\Widget\Form\TNumeric;

class RadiantiElementoNumeroInteiro extends TNumeric
{
    public function __construct($nome, $casasDecimais = 0, $separadorDecimais = ',', $separadorMilhares = '.', $snAtualizaAoEfetuarPost = true, $snPreenchimentoEsquerdaParaDireita = false, $snPermiteNegativo = false)
    {
        parent::__construct($nome, $casasDecimais, $separadorDecimais, $separadorMilhares, $snAtualizaAoEfetuarPost, $snPreenchimentoEsquerdaParaDireita, $snPermiteNegativo);
    }
}
