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
    }

    public function definirValorComoHoje()
    {
        $this->setValue(date('Y-m-d'));
    }

    public function definirValorComoPrimeiroDiaMes()
    {
        $this->setValue(date('Y-m-01'));
    }
}
