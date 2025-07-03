<?php


namespace Axdron\Radianti\Services\RadiantiQuestionService;

use Adianti\Widget\Form\TField;

class RadiantiQuestionServiceCampoFormulario
{
    public string $label;
    public TField $campo;

    public function __construct(string $label, TField $campo)
    {
        $this->label = $label;
        $this->campo = $campo;
    }
}
