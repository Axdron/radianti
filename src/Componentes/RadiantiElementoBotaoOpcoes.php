<?php

namespace Axdron\Radianti\Componentes;

use Adianti\Widget\Form\TRadioGroup;

class RadiantiElementoBotaoOpcoes extends TRadioGroup
{

    public function __construct(String $nome, array $opcoes)
    {
        parent::__construct($nome);
        $this->setLayout('horizontal');
        $this->setUseButton();
        $this->addItems($opcoes);
    }
}
