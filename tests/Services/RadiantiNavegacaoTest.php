<?php

declare(strict_types=1);

namespace Adianti\Core;

/**
 * Double de teste para capturar chamadas em RadiantiNavegacao::carregarPagina
 */
class AdiantiCoreApplication
{
    public static array $ultimaChamada = [];

    public static function loadPage(string $classe, ?string $metodo = null, ?array $parametros = null): void
    {
        self::$ultimaChamada = [$classe, $metodo, $parametros];
    }
}

namespace Tests\Services;

use PHPUnit\Framework\TestCase;
use Axdron\Radianti\Services\RadiantiNavegacao;

class RadiantiNavegacaoTest extends TestCase
{
    public function testCarregarPaginaEncaminhaCorretamenteParaAdiantiCoreApplication(): void
    {
        $classe = 'MinhaClasse';
        $metodo = 'meuMetodo';
        $parametros = ['a' => 1, 'b' => 2];

        RadiantiNavegacao::carregarPagina($classe, $metodo, $parametros);

        $esperado = [$classe, $metodo, $parametros];
        $this->assertSame($esperado, \Adianti\Core\AdiantiCoreApplication::$ultimaChamada);
    }

    public function testAbrirNovaGuiaComApenasClasse(): void
    {
        $this->expectOutputString("<script>window.open('index.php?class=MinhaClasse', '_blank');</script>");
        RadiantiNavegacao::abrirNovaGuia('MinhaClasse');
    }

    public function testAbrirNovaGuiaComClasseEMetodo(): void
    {
        $this->expectOutputString("<script>window.open('index.php?class=MinhaClasse&method=editar', '_blank');</script>");
        RadiantiNavegacao::abrirNovaGuia('MinhaClasse', 'editar');
    }

    public function testAbrirNovaGuiaComClasseEKey(): void
    {
        $this->expectOutputString("<script>window.open('index.php?class=MinhaClasse&key=123&id=123', '_blank');</script>");
        RadiantiNavegacao::abrirNovaGuia('MinhaClasse', null, 123);
    }

    public function testAbrirNovaGuiaComClasseMetodoEKey(): void
    {
        $this->expectOutputString("<script>window.open('index.php?class=MinhaClasse&method=visualizar&key=ABC&id=ABC', '_blank');</script>");
        RadiantiNavegacao::abrirNovaGuia('MinhaClasse', 'visualizar', 'ABC');
    }

    public function testAbrirNovaGuiaNaoIncluiKeyQuandoValorFalso(): void
    {
        // 0 é considerado falso em PHP, então não deve incluir key/id
        $this->expectOutputString("<script>window.open('index.php?class=MinhaClasse', '_blank');</script>");
        RadiantiNavegacao::abrirNovaGuia('MinhaClasse', null, 0);
    }
}
