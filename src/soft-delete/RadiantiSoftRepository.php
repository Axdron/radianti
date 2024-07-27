<?php

namespace RadiantiSoftDelete;

use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;

class RadiantiSoftRepository extends TRepository
{
    public function load(TCriteria $criteria = NULL, $callObjectLoad = TRUE)
    {
        if (is_null($criteria)) {
            $criteria = new TCriteria;
        }

        $criteria->add(new TFilter('data_exclusao', 'IS', NULL));

        return parent::load($criteria, $callObjectLoad);
    }

    public function loadComExcluidos(TCriteria $criteria = NULL, $callObjectLoad = TRUE)
    {
        return parent::load($criteria, $callObjectLoad);
    }
}
