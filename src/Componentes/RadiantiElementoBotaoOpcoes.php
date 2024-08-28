<?php

namespace Axdron\Radianti\Componentes;

use Adianti\Widget\Form\TRadioGroup;

class RadiantiElementoBotaoOpcoes extends TRadioGroup
{

    public function __construct(String $nome, array $opcoes, $opcaoPadrao = null)
    {
        parent::__construct($nome);
        $this->setLayout('horizontal');
        $this->setUseButton();
        $this->addItems($opcoes);
        if ($opcaoPadrao !== null) {
            $this->setValue($opcaoPadrao);
        }
    }
}
