<?php

use Adianti\Core\AdiantiCoreApplication;

class RadiantiNavegacao
{
    static function carregarPagina(string $classe, string $metodo = null, array $parametros = null)
    {
        AdiantiCoreApplication::loadPage($classe, $metodo, $parametros);
    }

    static function abrirNovaGuia($classe, $method, $key)
    {
        echo "<script>window.open('index.php?class={$classe}&method={$method}&key={$key}&id={$key}', '_blank');</script>";
    }
}
