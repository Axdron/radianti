<?php

namespace Axdron\Radianti\Services;

use Adianti\Core\AdiantiCoreApplication;

class RadiantiNavegacao
{
    static function carregarPagina(string $classe, string|null $metodo = null, array|null $parametros = null)
    {
        AdiantiCoreApplication::loadPage($classe, $metodo, $parametros);
    }

    static function abrirNovaGuia(string $classe, ?string $method = null, $key = null)
    {
        $endereco = "index.php?class={$classe}";
        if ($method) {
            $endereco .= "&method={$method}";
        }
        if ($key) {
            $endereco .= "&key={$key}&id={$key}";
        }
        echo "<script>window.open('$endereco', '_blank');</script>";
    }
}
