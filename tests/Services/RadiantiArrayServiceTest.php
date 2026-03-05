<?php

declare(strict_types=1);

namespace Tests\Services;

use PHPUnit\Framework\TestCase;
use Axdron\Radianti\Services\RadiantiArrayService;

/**
 * Testes para a classe RadiantiArrayService
 * 
 * Testa o método de conversão de arrays em texto formatado.
 */
class RadiantiArrayServiceTest extends TestCase
{
    /**
     * Testa o método converterEmTexto com array simples
     */
    public function testConverterEmTextoComArraySimples(): void
    {
        $array = [
            'nome' => 'João Silva',
            'idade' => 30,
            'cidade' => 'São Paulo',
        ];

        $resultado = RadiantiArrayService::converterEmTexto($array);

        $this->assertStringContainsString('nome: João Silva<br>', $resultado);
        $this->assertStringContainsString('idade: 30<br>', $resultado);
        $this->assertStringContainsString('cidade: São Paulo<br>', $resultado);
    }

    /**
     * Testa o método converterEmTexto com valores numéricos
     */
    public function testConverterEmTextoComValoresNumericos(): void
    {
        $array = [
            'quantidade' => 100,
            'preco' => 50.75,
            'desconto' => 0,
        ];

        $resultado = RadiantiArrayService::converterEmTexto($array);

        $this->assertStringContainsString('quantidade: 100<br>', $resultado);
        $this->assertStringContainsString('preco: 50.75<br>', $resultado);
        $this->assertStringContainsString('desconto: 0<br>', $resultado);
    }

    /**
     * Testa o método converterEmTexto com valores booleanos
     */
    public function testConverterEmTextoComValoresBooleanos(): void
    {
        $array = [
            'ativo' => true,
            'inativo' => false,
        ];

        $resultado = RadiantiArrayService::converterEmTexto($array);

        $this->assertStringContainsString('ativo: 1<br>', $resultado);
        $this->assertStringContainsString('inativo: <br>', $resultado);
    }

    /**
     * Testa o método converterEmTexto com array vazio
     */
    public function testConverterEmTextoComArrayVazio(): void
    {
        $array = [];
        $resultado = RadiantiArrayService::converterEmTexto($array);

        $this->assertSame('', $resultado);
    }

    /**
     * Testa o método converterEmTexto com caracteres especiais
     */
    public function testConverterEmTextoComCaracteresEspeciais(): void
    {
        $array = [
            'nome' => 'José & Silva',
            'descricao' => 'Produto "especial"',
            'observacao' => "Linha 1\nLinha 2",
        ];

        $resultado = RadiantiArrayService::converterEmTexto($array);

        $this->assertStringContainsString('nome: José & Silva<br>', $resultado);
        $this->assertStringContainsString('descricao: Produto "especial"<br>', $resultado);
        $this->assertIsString($resultado);
    }

    /**
     * Testa o método converterEmTexto com array misto (vários tipos de valores)
     */
    public function testConverterEmTextoComArrayMisto(): void
    {
        $array = [
            'data' => '2026-03-15',
            'vendedor' => 'João Silva',
            'valor' => 1500.50,
            'ativo' => true,
        ];

        $resultado = RadiantiArrayService::converterEmTexto($array);

        $this->assertStringContainsString('data: 2026-03-15<br>', $resultado);
        $this->assertStringContainsString('vendedor: João Silva<br>', $resultado);
        $this->assertStringContainsString('valor: 1500.5<br>', $resultado);
        $this->assertStringContainsString('ativo: 1<br>', $resultado);
    }

    /**
     * Testa que cada linha termina com <br>
     */
    public function testConverterEmTextoTerminaComBr(): void
    {
        $array = [
            'campo1' => 'valor1',
            'campo2' => 'valor2',
        ];

        $resultado = RadiantiArrayService::converterEmTexto($array);

        // Verifica que há 2 tags <br>
        $this->assertSame(2, substr_count($resultado, '<br>'));

        // Verifica que termina com <br>
        $this->assertStringEndsWith('<br>', $resultado);
    }

    /**
     * Testa o formato de saída chave: valor
     */
    public function testConverterEmTextoFormatoChaveValor(): void
    {
        $array = [
            'teste' => 'exemplo',
        ];

        $resultado = RadiantiArrayService::converterEmTexto($array);

        $this->assertSame('teste: exemplo<br>', $resultado);
    }

    /**
     * Testa conversão com valores null
     */
    public function testConverterEmTextoComValorNull(): void
    {
        $array = [
            'campo_null' => null,
            'campo_preenchido' => 'valor',
        ];

        $resultado = RadiantiArrayService::converterEmTexto($array);

        $this->assertStringContainsString('campo_null: <br>', $resultado);
        $this->assertStringContainsString('campo_preenchido: valor<br>', $resultado);
    }

    /**
     * Testa conversão com strings vazias
     */
    public function testConverterEmTextoComStringVazia(): void
    {
        $array = [
            'campo_vazio' => '',
            'campo_preenchido' => 'valor',
        ];

        $resultado = RadiantiArrayService::converterEmTexto($array);

        $this->assertStringContainsString('campo_vazio: <br>', $resultado);
        $this->assertStringContainsString('campo_preenchido: valor<br>', $resultado);
    }
}
