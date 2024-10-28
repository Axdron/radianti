<?php

namespace Axdron\Radianti\Componentes;

use Axdron\Radianti\Utils\RadiantiDatasets;

class RadiantiElementoBotaoSN extends RadiantiElementoBotaoOpcoes
{

    public function __construct(String $nome, int|string $opcaoPadrao = 1, bool $opcaoTodos = false)
    {
        parent::__construct($nome, RadiantiDatasets::ARRAY_SN, $opcaoPadrao);
        if ($opcaoTodos) {
            $this->ativarOpcaoTodos();
        }
    }

    function ativarOpcaoTodos()
    {
        $opcoes = RadiantiDatasets::ARRAY_SN;
        $opcoes['Todos'] = 'Todos';
        $this->addItems($opcoes);
        $this->setValue('Todos');
    }

    static function tratarFiltroTodos($valor): bool
    {
        if ($valor == '' || $valor == 'Todos') {
            return false;
        }
        return true;
    }
}
