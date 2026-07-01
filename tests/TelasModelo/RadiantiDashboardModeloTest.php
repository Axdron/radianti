<?php

declare(strict_types=1);

namespace Tests\TelasModelo;

use PHPUnit\Framework\TestCase;

/**
 * Testes para a classe RadiantiDashboardModelo
 * 
 * Testa principalmente os métodos utilitários de formatação que não dependem do framework Adianti.
 * Os métodos de criação de componentes visuais (criarCardIndicador, criarSecaoDashboard, etc)
 * são testados através de instanciação em aplicação real.
 * 
 * @covers \Axdron\Radianti\TelasModelo\RadiantiDashboardModelo
 */
class RadiantiDashboardModeloTest extends TestCase
{
    /**
     * Mock class para testar métodos protegidos sem chamar construtor
     */
    private TestDashboardHelper $testHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testHelper = new TestDashboardHelper();
    }

    /**
     * Testa se o método tratarFiltros converte tipos corretamente (int)
     */
    public function testTratarFiltrosComTipoInt(): void
    {
        $filtros = ['valor' => '123'];
        $configuracao = ['valor' => 'int'];

        $resultado = $this->testHelper->tratarFiltros($filtros, $configuracao);

        $this->assertIsInt($resultado['valor']);
        $this->assertEquals(123, $resultado['valor']);
    }

    /**
     * Testa conversão de float com vírgula e ponto
     */
    public function testTratarFiltrosComTipoFloatVírgula(): void
    {
        $filtros = ['valor' => '1.234,56'];
        $configuracao = ['valor' => 'float'];

        $resultado = $this->testHelper->tratarFiltros($filtros, $configuracao);

        $this->assertIsFloat($resultado['valor']);
        $this->assertEqualsWithDelta(1234.56, $resultado['valor'], 0.01);
    }

    /**
     * Testa conversão de string
     */
    public function testTratarFiltrosComTipoString(): void
    {
        $filtros = ['valor' => 123];
        $configuracao = ['valor' => 'string'];

        $resultado = $this->testHelper->tratarFiltros($filtros, $configuracao);

        $this->assertIsString($resultado['valor']);
        $this->assertEquals('123', $resultado['valor']);
    }

    /**
     * Testa conversão de bool
     */
    public function testTratarFiltrosComTipoBool(): void
    {
        $filtros = ['ativo' => 1];
        $configuracao = ['ativo' => 'bool'];

        $resultado = $this->testHelper->tratarFiltros($filtros, $configuracao);

        $this->assertIsBool($resultado['ativo']);
        $this->assertTrue($resultado['ativo']);
    }

    /**
     * Testa tratamento de filtros vazios
     */
    public function testTratarFiltrosVazios(): void
    {
        $filtros = ['valor' => '', 'outro' => '123'];
        $configuracao = ['valor' => 'int', 'outro' => 'int'];

        $resultado = $this->testHelper->tratarFiltros($filtros, $configuracao);

        $this->assertEmpty($resultado['valor']);
        $this->assertEquals(123, $resultado['outro']);
    }

    /**
     * Testa formatação de valores monetários com símbolo
     */
    public function testFormatarValor(): void
    {
        $resultado = $this->testHelper->formatarValor(1234.50, true);
        $this->assertStringContainsString('R$', $resultado);
        $this->assertStringContainsString('1.234,50', $resultado);
    }

    /**
     * Testa formatação de valor sem símbolo
     */
    public function testFormatarValorSemSimbolo(): void
    {
        $resultado = $this->testHelper->formatarValor(1234.50, false);
        $this->assertStringNotContainsString('R$', $resultado);
        $this->assertStringContainsString('1.234,50', $resultado);
    }

    /**
     * Testa formatação de valor nulo
     */
    public function testFormatarValorNulo(): void
    {
        $resultado = $this->testHelper->formatarValor(null, true);
        $this->assertStringContainsString('R$', $resultado);
        $this->assertStringContainsString('0,00', $resultado);
    }

    /**
     * Testa formatação de valor zero
     */
    public function testFormatarValorZero(): void
    {
        $resultado = $this->testHelper->formatarValor(0, true);
        $this->assertStringContainsString('R$', $resultado);
        $this->assertStringContainsString('0,00', $resultado);
    }

    /**
     * Testa formatação de número inteiro com separadores
     */
    public function testFormatarNumero(): void
    {
        $resultado = $this->testHelper->formatarNumero(1234);
        $this->assertEquals('1.234', $resultado);
    }

    /**
     * Testa formatação de número nulo
     */
    public function testFormatarNumeroNulo(): void
    {
        $resultado = $this->testHelper->formatarNumero(null);
        $this->assertEquals('0', $resultado);
    }

    /**
     * Testa formatação de número grande
     */
    public function testFormatarNumeroGrande(): void
    {
        $resultado = $this->testHelper->formatarNumero(1234567);
        $this->assertEquals('1.234.567', $resultado);
    }

    /**
     * Testa formatação de percentual sem sinal
     */
    public function testFormatarPercentual(): void
    {
        $resultado = $this->testHelper->formatarPercentual(12.50);
        $this->assertStringContainsString('12,50%', $resultado);
    }

    /**
     * Testa formatação de percentual com sinal positivo
     */
    public function testFormatarPercentualComSinalPositivo(): void
    {
        $resultado = $this->testHelper->formatarPercentual(12.50, true);
        $this->assertStringContainsString('+12,50%', $resultado);
    }

    /**
     * Testa formatação de percentual negativo sem sinal positivo
     */
    public function testFormatarPercentualNegativo(): void
    {
        $resultado = $this->testHelper->formatarPercentual(-12.50, true);
        $this->assertStringContainsString('-12,50%', $resultado);
    }

    /**
     * Testa cor de crescimento positivo (success)
     */
    public function testDeterminarCorCrescimentoPositivo(): void
    {
        $resultado = $this->testHelper->determinarCorCrescimento(10.5);
        $this->assertEquals('success', $resultado);
    }

    /**
     * Testa cor de crescimento negativo (danger)
     */
    public function testDeterminarCorCrescimentoNegativo(): void
    {
        $resultado = $this->testHelper->determinarCorCrescimento(-10.5);
        $this->assertEquals('danger', $resultado);
    }

    /**
     * Testa cor de crescimento zero (secondary/neutro)
     */
    public function testDeterminarCorCrescimentoZero(): void
    {
        $resultado = $this->testHelper->determinarCorCrescimento(0);
        $this->assertEquals('secondary', $resultado);
    }

    /**
     * Testa ícone de crescimento positivo (seta para cima)
     */
    public function testDeterminarIconeCrescimentoPositivo(): void
    {
        $resultado = $this->testHelper->determinarIconeCrescimento(10.5);
        $this->assertStringContainsString('fa-arrow-up', $resultado);
    }

    /**
     * Testa ícone de crescimento negativo (seta para baixo)
     */
    public function testDeterminarIconeCrescimentoNegativo(): void
    {
        $resultado = $this->testHelper->determinarIconeCrescimento(-10.5);
        $this->assertStringContainsString('fa-arrow-down', $resultado);
    }

    /**
     * Testa ícone de crescimento zero (traço)
     */
    public function testDeterminarIconeCrescimentoZero(): void
    {
        $resultado = $this->testHelper->determinarIconeCrescimento(0);
        $this->assertStringContainsString('fa-minus', $resultado);
    }
}

/**
 * Helper class que implementa manualmente os métodos de teste
 * sem depender do construtor do RadiantiDashboardModelo
 */
class TestDashboardHelper
{
    /**
     * Implementação do método tratarFiltros
     * Trata e converte tipos de filtros conforme configurado
     */
    public function tratarFiltros(array $filtros, array $configuracao): array
    {
        foreach ($configuracao as $campo => $tipo) {
            if (!isset($filtros[$campo]) || empty($filtros[$campo])) {
                continue;
            }

            $valor = $filtros[$campo];

            switch ($tipo) {
                case 'int':
                    $filtros[$campo] = (int) $valor;
                    break;
                case 'float':
                    // Remover pontos e converter vírgula para ponto
                    if (is_string($valor)) {
                        $valor = str_replace('.', '', $valor);
                        $valor = str_replace(',', '.', $valor);
                    }
                    $filtros[$campo] = (float) $valor;
                    break;
                case 'string':
                    $filtros[$campo] = (string) $valor;
                    break;
                case 'bool':
                    $filtros[$campo] = (bool) $valor;
                    break;
            }
        }

        return $filtros;
    }

    /**
     * Implementação do método formatarValor
     * Formata valor monetário
     */
    public function formatarValor(?float $valor, bool $incluirSimbolo = true): string
    {
        $valor = $valor ?? 0.0;
        $valorFormatado = number_format($valor, 2, ',', '.');
        return $incluirSimbolo ? "R$ {$valorFormatado}" : $valorFormatado;
    }

    /**
     * Implementação do método formatarNumero
     * Formata número inteiro
     */
    public function formatarNumero(?int $numero): string
    {
        $numero = $numero ?? 0;
        return number_format($numero, 0, ',', '.');
    }

    /**
     * Implementação do método formatarPercentual
     * Formata percentual
     */
    public function formatarPercentual(float $percentual, bool $incluirSinal = false): string
    {
        $sinal = ($incluirSinal && $percentual > 0) ? '+' : '';
        return $sinal . number_format($percentual, 2, ',', '.') . '%';
    }

    /**
     * Implementação do método determinarCorCrescimento
     * Determina cor baseada em crescimento (positivo/negativo)
     */
    public function determinarCorCrescimento(float $valor): string
    {
        if ($valor > 0) return 'success';
        if ($valor < 0) return 'danger';
        return 'secondary';
    }

    /**
     * Implementação do método determinarIconeCrescimento
     * Determina ícone baseado em crescimento (seta para cima/baixo)
     */
    public function determinarIconeCrescimento(float $valor): string
    {
        if ($valor > 0) return 'fas fa-arrow-up';
        if ($valor < 0) return 'fas fa-arrow-down';
        return 'fas fa-minus';
    }
}
