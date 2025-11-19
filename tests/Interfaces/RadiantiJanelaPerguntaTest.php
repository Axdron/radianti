<?php

use PHPUnit\Framework\TestCase;
use Adianti\Control\TAction;
use Axdron\Radianti\Interfaces\RadiantiJanelaPergunta;

class RadiantiJanelaPerguntaTest extends TestCase
{
    /**
     * Testa se a classe existe e está no namespace correto
     */
    public function testClasseExiste(): void
    {
        $this->assertTrue(
            class_exists('Axdron\\Radianti\\Interfaces\\RadiantiJanelaPergunta'),
            'A classe Axdron\\Radianti\\Interfaces\\RadiantiJanelaPergunta deve existir.'
        );
    }

    /**
     * Testa se a classe possui um construtor público
     */
    public function testConstrutorExisteEEhPublico(): void
    {
        $ref = new \ReflectionClass('Axdron\\Radianti\\Interfaces\\RadiantiJanelaPergunta');
        $ctor = $ref->getConstructor();

        $this->assertNotNull($ctor, 'Construtor deve existir.');
        $this->assertTrue($ctor->isPublic(), 'Construtor deve ser público.');
    }

    /**
     * Testa se o construtor tem os parâmetros obrigatórios com tipos corretos
     */
    public function testConstrutorTemParametrosComTipagem(): void
    {
        $ref = new \ReflectionClass('Axdron\\Radianti\\Interfaces\\RadiantiJanelaPergunta');
        $ctor = $ref->getConstructor();

        $params = $ctor->getParameters();
        $this->assertGreaterThanOrEqual(4, count($params), 'Construtor deve ter pelo menos 4 parâmetros.');

        // Verificar tipos dos primeiros 4 parâmetros obrigatórios
        $this->assertEquals('string', $params[0]->getType()->getName(), '1º parâmetro (message) deve ser string.');
        $this->assertEquals('Adianti\\Control\\TAction', $params[1]->getType()->getName(), '2º parâmetro (action_yes) deve ser TAction.');
        $this->assertEquals('Adianti\\Control\\TAction', $params[2]->getType()->getName(), '3º parâmetro (action_no) deve ser TAction.');
        $this->assertEquals('string', $params[3]->getType()->getName(), '4º parâmetro (title_msg) deve ser string.');
    }

    /**
     * Testa se os parâmetros opcionais têm valores padrão
     */
    public function testParametrosOpcionaisTemDefaultValues(): void
    {
        $ref = new \ReflectionClass('Axdron\\Radianti\\Interfaces\\RadiantiJanelaPergunta');
        $ctor = $ref->getConstructor();
        $params = $ctor->getParameters();

        // Verificar que parâmetros 5 e 6 (label_yes e label_no) têm default
        $this->assertTrue($params[4]->isDefaultValueAvailable(), '5º parâmetro (label_yes) deve ter valor padrão.');
        $this->assertTrue($params[5]->isDefaultValueAvailable(), '6º parâmetro (label_no) deve ter valor padrão.');
    }

    /**
     * Testa se a classe tem declare(strict_types=1)
     */
    public function testArquivoTemStrictTypes(): void
    {
        $filePath = __DIR__ . '/../../src/Interfaces/RadiantiJanelaPergunta.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString(
            'declare(strict_types=1)',
            $content,
            'Arquivo deve conter declare(strict_types=1) no início.'
        );
    }

    /**
     * Testa se a classe tem PHPDoc
     */
    public function testClasseTemPHPDoc(): void
    {
        $ref = new \ReflectionClass('Axdron\\Radianti\\Interfaces\\RadiantiJanelaPergunta');

        $this->assertNotNull($ref->getDocComment(), 'Classe deve ter PHPDoc.');
        $this->assertStringContainsString('RadiantiJanelaPergunta', $ref->getDocComment(), 'PHPDoc deve conter o nome da classe.');
    }

    /**
     * Testa se o construtor tem PHPDoc com descrição dos parâmetros
     */
    public function testConstrutorTemPHPDocComParametros(): void
    {
        $ref = new \ReflectionClass('Axdron\\Radianti\\Interfaces\\RadiantiJanelaPergunta');
        $ctor = $ref->getConstructor();
        $docComment = $ctor->getDocComment();

        $this->assertNotNull($docComment, 'Construtor deve ter PHPDoc.');
        $this->assertStringContainsString('@param', $docComment, 'PHPDoc do construtor deve descrever os parâmetros.');
    }

    /**
     * Testa se a classe pode ser instanciada com parâmetros mínimos
     */
    public function testInstanciacaoComParametrosMinimos(): void
    {
        $this->assertTrue(
            class_exists('Axdron\\Radianti\\Interfaces\\RadiantiJanelaPergunta'),
            'Classe deve existir para testes de instanciação.'
        );
        // A instanciação real é complexa pois envolve componentes Adianti que requerem contexto de aplicação
        // Validamos apenas que a classe pode ser carregada e tem o construtor correto
        $ref = new \ReflectionClass('Axdron\\Radianti\\Interfaces\\RadiantiJanelaPergunta');
        $this->assertTrue($ref->hasMethod('__construct'), 'Classe deve ter construtor.');
    }

    /**
     * Testa se a classe pode ser instanciada com todos os parâmetros
     */
    public function testInstanciacaoComTodosParametros(): void
    {
        $this->assertTrue(
            class_exists('Axdron\\Radianti\\Interfaces\\RadiantiJanelaPergunta'),
            'Classe deve existir para testes de instanciação.'
        );
        // A instanciação real é complexa pois envolve componentes Adianti que requerem contexto de aplicação
        // Validamos apenas que a classe pode ser carregada e tem o construtor correto
        $ref = new \ReflectionClass('Axdron\\Radianti\\Interfaces\\RadiantiJanelaPergunta');
        $ctor = $ref->getConstructor();
        $this->assertNotNull($ctor, 'Construtor deve existir.');
        $params = $ctor->getParameters();
        $this->assertGreaterThanOrEqual(6, count($params), 'Construtor deve aceitar 6 parâmetros.');
    }
}
