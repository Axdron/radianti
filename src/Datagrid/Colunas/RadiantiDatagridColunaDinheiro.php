<?php

namespace Axdron\Radianti\Datagrid\Colunas;

use Adianti\Widget\Datagrid\TDataGridColumn;

class RadiantiDatagridColunaDinheiro extends TDataGridColumn
{
    public function __construct($name, $label, $align = 'right', $width = NULL)
    {
        parent::__construct($name, $label, $align, $width);

        $this->setTransformer(function ($value) {
            if (empty($value))
                $value = 0;
            return 'R$ ' . number_format($value, 2, ',', '.');
        });
    }
}
