<?php

namespace Axdron\Radianti\Componentes;

use Adianti\Widget\Base\TScript;
use Adianti\Widget\Util\TTextDisplay;

/**
 * Classe para criar um elemento de texto, com quebra de linha opcional.
 * Atenção: É importante que, quando o conteúdo não estiver iniciado no momento da declaração, 
 * seja informado através do método atualizarValor no momento em que o conteúdo for definido.
 * 
 * @author Axdron
 * @version 1.0
 */
class RadiantiElementoTexto extends TTextDisplay
{
    public function __construct(string $nome, string $conteudo = '', bool $snMostraQuebrasLinhas = false)
    {
        if ($snMostraQuebrasLinhas && !empty($conteudo))
            self::mostrarQuebrasLinhas($conteudo);

        parent::__construct($conteudo);
        $this->setProperties(['id' => $nome, 'name' => $nome]);
    }

    private static function mostrarQuebrasLinhas(string &$conteudo = '')
    {
        $conteudo = str_replace(["\r\n", "\r", "\n"], '<br>', $conteudo);
    }

    public static function atualizarValor(string $nome, string $conteudo = '', bool $snMostraQuebrasLinhas = false)
    {
        if ($snMostraQuebrasLinhas) {
            self::mostrarQuebrasLinhas($conteudo);
        }

        TScript::create("$('#$nome').html('$conteudo');");
    }
}
