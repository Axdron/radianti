<?php

use PHPUnit\Framework\TestCase;
use Axdron\Radianti\Services\RadiantiEngineService;

class RadiantiEngineServiceTest extends TestCase
{
    private $backupRequest;

    protected function setUp(): void
    {
        // Backup original superglobal
        $this->backupRequest = $_REQUEST;
    }

    protected function tearDown(): void
    {
        // Restore original superglobal to avoid side effects
        $_REQUEST = $this->backupRequest;
    }

    private function setMaxInputVars(RadiantiEngineService $service, ?int $value): void
    {
        $ref = new \ReflectionObject($service);
        if ($ref->hasProperty('maxInputVars')) {
            $prop = $ref->getProperty('maxInputVars');
            $prop->setAccessible(true);
            $prop->setValue($service, $value);
        }
    }

    public function testValidoQuandoAbaixoDoLimite(): void
    {
        $_REQUEST = [
            'campo1' => 'valor1',
            'campo2' => 'valor2',
            'campo3' => ['sub1' => 'a', 'sub2' => 'b'],
        ];

        $service = new RadiantiEngineService();
        $this->setMaxInputVars($service, 10); // ajustar limite via reflection
        $service->validarMaxInputVars();

        $this->assertTrue($service->snMaxInputVarsValido, 'Deveria ser válido quando total de variáveis está abaixo do limite.');
        $this->assertEquals(4, $service->totalRequest, 'Total de variáveis esperado (2 campos no nível superior + 2 subcampos).');
        $this->assertEquals(10, $service->maxInputVars);
    }

    public function testInvalidoQuandoExcedeOLimite(): void
    {
        // criar um array aninhado com 6 elementos
        $_REQUEST = [
            'arr' => [1, 2, 3, 4, 5, 6],
        ];

        $service = new RadiantiEngineService();
        $this->setMaxInputVars($service, 3); // ajustar limite menor via reflection
        $service->validarMaxInputVars();

        $this->assertFalse($service->snMaxInputVarsValido, 'Deveria ser inválido quando total de variáveis excede o limite.');
        $this->assertEquals(6, $service->totalRequest);
        $this->assertEquals(3, $service->maxInputVars);
    }

    public function testContaMultiplosNiveisDeArray(): void
    {
        $_REQUEST = [
            'a' => [
                'b' => [
                    'c1' => 1,
                    'c2' => 2,
                ],
                'b2' => 3,
            ],
            'd' => 4,
        ];

        $service = new RadiantiEngineService();
        $this->setMaxInputVars($service, 100);
        $service->validarMaxInputVars();

        // elementos: c1, c2, b2, d => 4
        $this->assertEquals(4, $service->totalRequest);
        $this->assertTrue($service->snMaxInputVarsValido);
    }
}
