<?php

use PHPUnit\Framework\TestCase;

class RadiantiElementoDataHoraTest extends TestCase
{
    public function testClasseExiste(): void
    {
        $this->assertTrue(
            class_exists('Axdron\\Radianti\\Componentes\\RadiantiElementoDataHora'),
            'A classe Axdron\\Radianti\\Componentes\\RadiantiElementoDataHora deve existir.'
        );
    }

    public function testMetodosExistemEPublicos(): void
    {
        $ref = new \ReflectionClass('Axdron\\Radianti\\Componentes\\RadiantiElementoDataHora');

        $this->assertTrue($ref->hasMethod('definirValorComoHoje'), 'Método definirValorComoHoje deve existir.');
        $this->assertTrue($ref->hasMethod('definirValorComoAgora'), 'Método definirValorComoAgora deve existir.');
        $this->assertTrue($ref->hasMethod('definirValorComoPrimeiroDiaMes'), 'Método definirValorComoPrimeiroDiaMes deve existir.');

        $m1 = $ref->getMethod('definirValorComoHoje');
        $m2 = $ref->getMethod('definirValorComoAgora');
        $m3 = $ref->getMethod('definirValorComoPrimeiroDiaMes');

        $this->assertTrue($m1->isPublic(), 'definirValorComoHoje deve ser público.');
        $this->assertTrue($m2->isPublic(), 'definirValorComoAgora deve ser público.');
        $this->assertTrue($m3->isPublic(), 'definirValorComoPrimeiroDiaMes deve ser público.');
    }

    public function testValoresRetornadosCorretos(): void
    {
        $class = 'Axdron\\Radianti\\Componentes\\RadiantiElementoDataHora';
        $this->assertTrue(class_exists($class), 'Classe deve existir para testar retornos.');

        $obj = new \Axdron\Radianti\Componentes\RadiantiElementoDataHora('data_hora_test');

        // testar definirValorComoHoje() => início do dia
        $retHoje = $obj->definirValorComoHoje();
        $expectedHoje = date('Y-m-d 00:00');
        $this->assertEquals($expectedHoje, $retHoje, 'definirValorComoHoje deve retornar a data atual às 00:00.');

        // testar definirValorComoAgora() => data/hora atual
        $retAgora = $obj->definirValorComoAgora();
        $expectedAgora = date('Y-m-d H:i');
        $this->assertEquals($expectedAgora, $retAgora, 'definirValorComoAgora deve retornar a data/hora atual no formato Y-m-d H:i.');

        // testar definirValorComoPrimeiroDiaMes()
        $retPrimeiro = $obj->definirValorComoPrimeiroDiaMes();
        $expectedPrimeiro = date('Y-m-01 00:00');
        $this->assertEquals($expectedPrimeiro, $retPrimeiro, 'definirValorComoPrimeiroDiaMes deve retornar o primeiro dia do mês com hora 00:00.');
    }

    public function testConstrutorTemTipagemString(): void
    {
        $ref = new \ReflectionClass('Axdron\\Radianti\\Componentes\\RadiantiElementoDataHora');
        $ctor = $ref->getConstructor();

        $this->assertNotNull($ctor, 'Construtor deve existir.');

        $params = $ctor->getParameters();
        $this->assertGreaterThanOrEqual(1, count($params), 'Construtor deve ter pelo menos um parâmetro.');

        $first = $params[0];
        $this->assertTrue($first->hasType(), 'Primeiro parâmetro do construtor deve ter type hint.');
        $this->assertEquals('string', $first->getType()->getName(), 'Primeiro parâmetro do construtor deve ser tipado como string.');
    }
}
