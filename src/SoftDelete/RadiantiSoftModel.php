<?php

namespace Axdron\Radianti\SoftDelete;

use Adianti\Database\TRecord;

abstract class RadiantiSoftModel extends TRecord
{

    const PRIMARYKEY = 'id';
    const IDPOLICY =  'serial';

    const CREATEDAT = 'data_cadastro';
    const CREATEDBY = 'usuario_cadastro_id';

    const UPDATEDAT = 'data_atualizacao';
    const UPDATEDBY = 'usuario_atualizacao_id';

    const DELETEDAT = 'data_exclusao';
    const DELETEDBY = 'usuario_exclusao_id';

    const USERBYATT = 'usuario_id';


    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute(self::CREATEDAT);
        parent::addAttribute(self::CREATEDBY);
        parent::addAttribute(self::UPDATEDAT);
        parent::addAttribute(self::UPDATEDBY);
        parent::addAttribute(self::DELETEDAT);
        parent::addAttribute(self::DELETEDBY);
    }
}
