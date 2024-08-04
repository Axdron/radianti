<?php

use Adianti\Widget\Form\TLabel;

class RAdiantiLabelExplicativa extends TLabel {

    public function __construct($explicacao) {
        parent::__construct($explicacao);
        $this->style = "color: white;
        font-weight: bold;
        background-color: rgba(255, 150, 0, 0.8);
        border-radius: 0.2em;
        padding: 0.5em 3em;
        margin-top: 2em;
        width: 100%;
        text-align: left;";
        $this->setSize('100%', 'auto');
    }

}