<?php

namespace Axdron\Radianti\Componentes;

use Adianti\Widget\Form\TDate;

class RadiantiElementoData extends TDate
{
    public function __construct($nome)
    {
        parent::__construct($nome);
        $this->setMask('dd/mm/yyyy');
        $this->setDatabaseMask('yyyy-mm-dd');
        $this->setSize('100%');
    }

    public function mudarParaCompetencia()
    {
        $this->setMask('mm/yyyy');
        $this->setDatabaseMask('yyyy-mm');
        $this->setOption('minViewMode', 'months');
    }

    public function definirValorComoHoje()
    {
        $this->setValue(date('Y-m-d'));
    }

    public function definirValorComoPrimeiroDiaMes()
    {
        $this->setValue(date('Y-m-01'));
    }

    public static function converterParaPadraoAmericano($data)
    {
        return implode('-', array_reverse(explode('/', $data)));
    }
}
