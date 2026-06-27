<?php

declare(strict_types=1);

namespace Tests\Services;

use PHPUnit\Framework\TestCase;
use Axdron\Radianti\Services\RadiantiMailService\RadiantiEmailService;
use Axdron\Radianti\Services\RadiantiMailService\RadiantiEmailAnexo;

/**
 * Implementação concreta de RadiantiEmailService para testes.
 * 
 * Implementa os métodos abstratos obrigatórios para permitir instanciação
 * em testes unitários.
 */
class RadiantiEmailServiceConcrete extends RadiantiEmailService
{
    /**
     * Simulação de ambiente para testes.
     */
    private static bool $snSimulacao = true;

    /**
     * Chave de API para testes.
     */
    private static string $chaveAPI = 'test_key_123';

    /**
     * Define se está em modo simulação.
     */
    public static function setSimulacao(bool $valor): void
    {
        self::$snSimulacao = $valor;
    }

    /**
     * Define a chave de API para testes.
     */
    public static function setChaveAPI(string $chave): void
    {
        self::$chaveAPI = $chave;
    }

    protected static function verificarSeSimulacao(): bool
    {
        return self::$snSimulacao;
    }

    protected function buscarChaveAPI(): string
    {
        return self::$chaveAPI;
    }

    protected function enviarSimulacaoParaDiscord(string $arquivoTemporario): void
    {
        // Implementação vazia para testes
    }
}

/**
 * Testes para a classe RadiantiEmailService
 * 
 * Testa validação de e-mails, criação de instâncias e envio de simulações.
 */
class RadiantiEmailServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        RadiantiEmailServiceConcrete::setSimulacao(true);
        RadiantiEmailServiceConcrete::setChaveAPI('test_key');
    }

    /**
     * Testa validação de um e-mail válido.
     */
    public function testValidarEmailValido(): void
    {
        $this->assertTrue(RadiantiEmailServiceConcrete::validarEmail('usuario@example.com'));
    }

    /**
     * Testa validação de e-mails inválidos.
     */
    public function testValidarEmailInvalido(): void
    {
        $this->assertFalse(RadiantiEmailServiceConcrete::validarEmail('email-invalido'));
        $this->assertFalse(RadiantiEmailServiceConcrete::validarEmail('email@'));
        $this->assertFalse(RadiantiEmailServiceConcrete::validarEmail('@example.com'));
        $this->assertFalse(RadiantiEmailServiceConcrete::validarEmail(''));
    }

    /**
     * Testa validação de múltiplos e-mails válidos.
     */
    public function testValidarEmailsValidos(): void
    {
        $this->assertTrue(RadiantiEmailServiceConcrete::validarEmails('user1@example.com;user2@example.com'));
        $this->assertTrue(RadiantiEmailServiceConcrete::validarEmails('email@test.com'));
    }

    /**
     * Testa validação de múltiplos e-mails com um inválido.
     */
    public function testValidarEmailsComInvalido(): void
    {
        $this->assertFalse(RadiantiEmailServiceConcrete::validarEmails('user1@example.com;email-invalido;user2@example.com'));
    }

    /**
     * Testa criação de instância com um único destinatário como string.
     */
    public function testCriarInstanciaComDestinatarioString(): void
    {
        $email = new RadiantiEmailServiceConcrete(
            'noreply@example.com',
            'Sistema',
            'usuario@example.com',
            'Assunto teste',
            '<p>Mensagem teste</p>'
        );

        $this->assertInstanceOf(RadiantiEmailServiceConcrete::class, $email);
    }

    /**
     * Testa criação de instância com múltiplos destinatários.
     */
    public function testCriarInstanciaComMultiplosDestinatarios(): void
    {
        $email = new RadiantiEmailServiceConcrete(
            'noreply@example.com',
            'Sistema',
            'user1@example.com;user2@example.com;user3@example.com',
            'Assunto teste',
            '<p>Mensagem teste</p>'
        );

        $this->assertInstanceOf(RadiantiEmailServiceConcrete::class, $email);
    }

    /**
     * Testa criação de instância com array de destinatários.
     */
    public function testCriarInstanciaComArrayDestinatarios(): void
    {
        $email = new RadiantiEmailServiceConcrete(
            'noreply@example.com',
            'Sistema',
            ['user1@example.com', 'user2@example.com'],
            'Assunto teste',
            '<p>Mensagem teste</p>'
        );

        $this->assertInstanceOf(RadiantiEmailServiceConcrete::class, $email);
    }

    /**
     * Testa exceção ao criar instância com e-mail inválido.
     */
    public function testExcecaoComEmailInvalido(): void
    {
        $this->expectException(\Exception::class);

        new RadiantiEmailServiceConcrete(
            'email-invalido',
            'Sistema',
            'usuario@example.com',
            'Assunto',
            'Mensagem'
        );
    }

    /**
     * Testa exceção ao criar instância com destinatário inválido.
     */
    public function testExcecaoComDestinatarioInvalido(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('E-mail inválido');

        new RadiantiEmailServiceConcrete(
            'noreply@example.com',
            'Sistema',
            'email-invalido-como-destinatario',
            'Assunto',
            'Mensagem'
        );
    }

    /**
     * Testa criação de instância com anexos.
     */
    public function testCriarInstanciaComAnexos(): void
    {
        $anexo = new RadiantiEmailAnexo();
        $anexo->nome = 'documento.txt';
        $anexo->caminho = __DIR__ . '/../../tests/Services/RadiantiEmailServiceTest.php';
        $anexo->tipo = 'text/plain';

        $email = new RadiantiEmailServiceConcrete(
            'noreply@example.com',
            'Sistema',
            'usuario@example.com',
            'Com anexo',
            '<p>Conteúdo</p>',
            [$anexo]
        );

        $this->assertInstanceOf(RadiantiEmailServiceConcrete::class, $email);
    }

    /**
     * Testa adição dinâmica de anexo.
     */
    public function testAdicionarAnexoDinamicamente(): void
    {
        $email = new RadiantiEmailServiceConcrete(
            'noreply@example.com',
            'Sistema',
            'usuario@example.com',
            'Assunto',
            '<p>Conteúdo</p>'
        );

        $anexo = new RadiantiEmailAnexo();
        $anexo->nome = 'teste.txt';
        $anexo->caminho = __DIR__ . '/../../tests/Services/RadiantiEmailServiceTest.php';
        $anexo->tipo = 'text/plain';

        $email->adicionarAnexo($anexo);

        $this->assertInstanceOf(RadiantiEmailServiceConcrete::class, $email);
    }

    /**
     * Testa envio em modo simulação.
     */
    public function testEnvioEmModoSimulacao(): void
    {
        RadiantiEmailServiceConcrete::setSimulacao(true);

        $email = new RadiantiEmailServiceConcrete(
            'noreply@example.com',
            'Sistema',
            'usuario@example.com',
            'Teste simulação',
            '<p>Mensagem de teste</p>'
        );

        $result = $email->enviar();

        $this->assertTrue($result);
    }

    /**
     * Testa desativação de simulação em tela.
     */
    public function testDesativacaoSimulacaoEmTela(): void
    {
        RadiantiEmailServiceConcrete::setSimulacao(true);

        $email = new RadiantiEmailServiceConcrete(
            'noreply@example.com',
            'Sistema',
            'usuario@example.com',
            'Teste',
            '<p>Conteúdo</p>'
        );

        $email->snDisparaSimulacaoEmTela = false;

        $result = $email->enviar();

        $this->assertTrue($result);
    }

    /**
     * Testa criação com destinatários vazios.
     * 
     * Quando um array vazio é passado como destinatários, nenhuma exceção é lançada
     * no construtor, mas será lançada quando tentar enviar sem destinatários.
     */
    public function testDestinatariosVazios(): void
    {
        // Criar instância sem gerar exceção no construtor
        $email = new RadiantiEmailServiceConcrete(
            'noreply@example.com',
            'Sistema',
            [],
            'Assunto',
            'Mensagem'
        );

        $this->assertInstanceOf(RadiantiEmailServiceConcrete::class, $email);
    }
}
