<?php

namespace Axdron\Radianti\Services;

use Adianti\Core\AdiantiCoreApplication;

class RadiantiNavegacao
{
    static function carregarPagina(string $classe, string|null $metodo = null, array|null $parametros = null)
    {
        AdiantiCoreApplication::loadPage($classe, $metodo, $parametros);
    }

    /**
     * Abre uma nova guia no navegador.
     * @param string $classe - Prefira usar a declaração CLASSE::class do que uma string literal.
     * @param string|null $method - Quando houver necessidade de parâmetros extras, recomenda-se informá-los aqui e não informar a key.
     * @param mixed|null $key -  Algumas classes trabalham com key, outras com id. Para garantir que o comportamento seja consistente, a função irá informar ambos os valores.
     */
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
