<?php

class RAdiantiElementoBotaoSN extends RadiantiElementoBotaoOpcoes
{

    public function __construct(String $nome)
    {
        parent::__construct($nome, RAdiantiDatasets::ARRAY_SN);
    }

    function ativarOpcaoTodos()
    {
        $opcoes = RAdiantiDatasets::ARRAY_SN;
        $opcoes[''] = 'Todos';
        $this->addItems($opcoes);
        $this->setValue('');
    }
}
