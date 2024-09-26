<?php

namespace Axdron\Radianti\Componentes;

use Adianti\Control\TAction;
use Adianti\Widget\Form\TButton;

class RadiantiElementoBotaoCadastroForm extends TButton
{
    public function __construct($nome, $classeCadastro, $metodoClasseCadastro)
    {
        parent::__construct(strtolower(str_replace(' ', '_', $nome)));
        $this->setLabel('Cadastrar ' . ucfirst($nome));
        $this->setImage('fa:plus');
        $this->setAction(new TAction([$classeCadastro, $metodoClasseCadastro]), 'Cadastrar ' . ucfirst($nome));
    }
}
