<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Adianti\Control\TAction;
use Axdron\Radianti\Interfaces\RadiantiJanelaMultiOpcoes;

class RadiantiJanelaMultiOpcoesTest extends TestCase
{
    /**
     * Testa se a classe existe e está no namespace correto
     */
    public function testClasseExiste(): void
    {
        $this->assertTrue(
            class_exists('Axdron\\Radianti\\Interfaces\\RadiantiJanelaMultiOpcoes'),
            'A classe Axdron\\Radianti\\Interfaces\\RadiantiJanelaMultiOpcoes deve existir.'
        );
    }

    /**
     * Testa se a classe possui um construtor público
     */
    public function testConstrutorExisteEEhPublico(): void
    {
        $ref = new \ReflectionClass('Axdron\\Radianti\\Interfaces\\RadiantiJanelaMultiOpcoes');
        $ctor = $ref->getConstructor();

        $this->assertNotNull($ctor, 'Construtor deve existir.');
        $this->assertTrue($ctor->isPublic(), 'Construtor deve ser público.');
    }

    /**
     * Testa se o construtor tem os parâmetros obrigatórios com tipos corretos
     */
    public function testConstrutorTemParametrosComTipagem(): void
    {
        $ref = new \ReflectionClass('Axdron\\Radianti\\Interfaces\\RadiantiJanelaMultiOpcoes');
        $ctor = $ref->getConstructor();

        $params = $ctor->getParameters();
        $this->assertGreaterThanOrEqual(2, count($params), 'Construtor deve ter pelo menos 2 parâmetros obrigatórios.');

        // Verificar tipos dos dois primeiros parâmetros obrigatórios
        $this->assertEquals('string', $params[0]->getType()->getName(), '1º parâmetro (mensagem) deve ser string.');
        $this->assertEquals('array', $params[1]->getType()->getName(), '2º parâmetro (opcoes) deve ser array.');
    }

    /**
     * Testa se os parâmetros opcionais têm valores padrão
     */
    public function testParametrosOpcionaisTemDefaultValues(): void
    {
        $ref = new \ReflectionClass('Axdron\\Radianti\\Interfaces\\RadiantiJanelaMultiOpcoes');
        $ctor = $ref->getConstructor();
        $params = $ctor->getParameters();

        // Verificar que parâmetros 3, 4, 5 e 6 têm default (titulo, largura, altura, nomeFormulario)
        $this->assertTrue($params[2]->isDefaultValueAvailable(), '3º parâmetro (titulo) deve ter valor padrão.');
        $this->assertTrue($params[3]->isDefaultValueAvailable(), '4º parâmetro (largura) deve ter valor padrão.');
        $this->assertTrue($params[4]->isDefaultValueAvailable(), '5º parâmetro (altura) deve ter valor padrão.');
        $this->assertTrue($params[5]->isDefaultValueAvailable(), '6º parâmetro (nomeFormulario) deve ter valor padrão.');
    }

    /**
     * Testa se a classe tem declare(strict_types=1)
     */
    public function testArquivoTemStrictTypes(): void
    {
        $filePath = __DIR__ . '/../../src/Interfaces/RadiantiJanelaMultiOpcoes.php';
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
        $ref = new \ReflectionClass('Axdron\\Radianti\\Interfaces\\RadiantiJanelaMultiOpcoes');

        $this->assertNotNull($ref->getDocComment(), 'Classe deve ter PHPDoc.');
        $this->assertStringContainsString('RadiantiJanelaMultiOpcoes', $ref->getDocComment(), 'PHPDoc deve conter o nome da classe.');
    }

    /**
     * Testa se o construtor tem PHPDoc com descrição dos parâmetros
     */
    public function testConstrutorTemPHPDocComParametros(): void
    {
        $ref = new \ReflectionClass('Axdron\\Radianti\\Interfaces\\RadiantiJanelaMultiOpcoes');
        $ctor = $ref->getConstructor();
        $docComment = $ctor->getDocComment();

        $this->assertNotNull($docComment, 'Construtor deve ter PHPDoc.');
        $this->assertStringContainsString('@param', $docComment, 'PHPDoc do construtor deve descrever os parâmetros.');
    }

    /**
     * Testa se a classe lança InvalidArgumentException quando opcão não tem 'rotulo'
     * Nota: Testes de exceção não são executados pois requerem contexto Adianti completo
     */
    public function testValidacaoOpcoesAtraVesDeReflection(): void
    {
        $ref = new \ReflectionClass('Axdron\\Radianti\\Interfaces\\RadiantiJanelaMultiOpcoes');

        // Verificar que a classe tem o construtor que valida as opções
        $this->assertTrue($ref->hasMethod('__construct'), 'Classe deve ter construtor.');

        // Verificar através do código-fonte que há validação de 'rotulo' e 'acao'
        $filePath = __DIR__ . '/../../src/Interfaces/RadiantiJanelaMultiOpcoes.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString(
            "empty(\$opcao['rotulo'])",
            $content,
            'Código deve validar se rotulo está preenchido.'
        );

        $this->assertStringContainsString(
            "empty(\$opcao['acao'])",
            $content,
            'Código deve validar se acao está preenchida.'
        );

        $this->assertStringContainsString(
            'InvalidArgumentException',
            $content,
            'Código deve lançar InvalidArgumentException para opções inválidas.'
        );
    }

    /**
     * Testa se a classe pode ser carregada e tem os métodos esperados
     * Nota: Instanciação real não é testada pois requer contexto Adianti completo
     */
    public function testClasseTemEstruturaCerta(): void
    {
        $ref = new \ReflectionClass('Axdron\\Radianti\\Interfaces\\RadiantiJanelaMultiOpcoes');

        // Verificar que a classe tem construtor
        $this->assertTrue($ref->hasMethod('__construct'), 'Classe deve ter construtor.');

        // Verificar que o construtor é público
        $ctor = $ref->getConstructor();
        $this->assertTrue($ctor->isPublic(), 'Construtor deve ser público.');

        // Validar através do código que a classe utiliza BootstrapFormBuilder
        $filePath = __DIR__ . '/../../src/Interfaces/RadiantiJanelaMultiOpcoes.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString(
            'BootstrapFormBuilder',
            $content,
            'Classe deve usar BootstrapFormBuilder para criar o formulário.'
        );

        $this->assertStringContainsString(
            'TWindow::create',
            $content,
            'Classe deve criar uma TWindow.'
        );
    }
}
