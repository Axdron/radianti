<?php

namespace Axdron\Radianti\Datagrid\Colunas;

use Adianti\Widget\Datagrid\TDataGridColumn;

class RadiantiDatagridColunaSimNao extends TDataGridColumn
{
    public function __construct($name, $label, $align = 'center', $width = NULL)
    {
        parent::__construct($name, $label, $align, $width);
        $this->setTransformer(function ($value) {
            return $value ? 'Sim' : 'Não';
        });
    }
}
