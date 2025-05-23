<?php

namespace Axdron\Radianti\Componentes;

use Adianti\Widget\Base\TElement;
use Adianti\Widget\Form\TText;

class RadiantiElementoTextoBloqueavel
{

    private $elemento;

    function __construct(string $nome, string|null $conteudo = null, $snBloqueado = false)
    {
        if ($snBloqueado) {
            $this->elemento = new TElement('div');
            $this->elemento->add('<pre>' . $conteudo . '</pre>');
            $this->elemento->setProperties(['id' => $nome, 'name' => $nome, 'readonly' => true]);
        } else {
            $this->elemento = new TText($nome);
            $this->elemento->setValue($conteudo);
        }
    }



    static function criarCampo($nome, $conteudo = null, $snBloqueado = false)
    {
        $novoCampo = new self($nome, $conteudo, $snBloqueado);
        return $novoCampo->elemento;
    }
}
