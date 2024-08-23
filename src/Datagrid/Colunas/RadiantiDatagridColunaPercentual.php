<?php

namespace Axdron\Radianti\Datagrid\Colunas;

use Adianti\Widget\Datagrid\TDataGridColumn;

class RadiantiDatagridColunaPercentual extends TDataGridColumn
{
    public function __construct($name, $label, $align = 'center', $width = NULL)
    {
        parent::__construct($name, $label, $align, $width);
        $this->setTransformer(function ($value) {
            if (!is_numeric($value)) {
                return $value;
            }
            return number_format($value, 2, ',', '.') . '%';
        });
    }
}
