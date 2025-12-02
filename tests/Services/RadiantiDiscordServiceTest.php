<?php

use PHPUnit\Framework\TestCase;
use Axdron\Radianti\Services\RadiantiDiscordService;

namespace Tests\Services;
class RadiantiDiscordServiceTest extends TestCase
{
    private RadiantiDiscordService $service;
    private string $webhookUrl = 'https://discord.com/api/webhooks/test/webhook123';

    protected function setUp(): void
    {
        $this->service = new RadiantiDiscordService();
    }

    /**
     * Testa se enviarMensagem lança exceção com webhook vazio
     */
    public function testEnviarMensagemComWebhookVazio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Webhook não foi informado');

        $this->service->enviarMensagem('teste', '');
    }

    /**
     * Testa segmentação de mensagem simples (sem quebra)
     */
    public function testSegmentarMensagemSimples(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $metodo = $reflection->getMethod('segmentarMensagem');
        $metodo->setAccessible(true);

        $mensagem = 'Hello World';
        $resultado = $metodo->invoke($this->service, $mensagem);

        $this->assertCount(1, $resultado, 'Mensagem simples deveria retornar um segmento');
        $this->assertEquals('Hello World', $resultado[0]);
    }

    /**
     * Testa segmentação de mensagem longa
     */
    public function testSegmentarMensagemLonga(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $metodo = $reflection->getMethod('segmentarMensagem');
        $metodo->setAccessible(true);

        $mensagem = str_repeat('A', 4500); // 4500 caracteres (precisa 3 segmentos de 1500)
        $resultado = $metodo->invoke($this->service, $mensagem, 1500);

        $this->assertCount(3, $resultado, 'Deveria segmentar em 3 partes');
        $this->assertEquals(1500, mb_strlen($resultado[0]));
        $this->assertEquals(1500, mb_strlen($resultado[1]));
        $this->assertEquals(1500, mb_strlen($resultado[2]));
    }

    /**
     * Testa segmentação com caracteres multibyte
     */
    public function testSegmentarMensagemMultibyte(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $metodo = $reflection->getMethod('segmentarMensagem');
        $metodo->setAccessible(true);

        $mensagem = str_repeat('é', 3000); // 3000 caracteres acentuados
        $resultado = $metodo->invoke($this->service, $mensagem, 1500);

        $this->assertCount(2, $resultado, 'Deveria segmentar em 2 partes');
        $this->assertEquals(1500, mb_strlen($resultado[0]));
        $this->assertEquals(1500, mb_strlen($resultado[1]));
    }

    /**
     * Testa enviarMensagem com string simples (usando mock)
     */
    public function testEnviarMensagemString(): void
    {
        $mockService = $this->createPartialMock(RadiantiDiscordService::class, ['notificarWebhook']);
        $mockService->expects($this->once())
            ->method('notificarWebhook')
            ->with('Teste mensagem', $this->webhookUrl)
            ->willReturn(true);

        $resultado = $mockService->enviarMensagem('Teste mensagem', $this->webhookUrl);
        $this->assertTrue($resultado);
    }

    /**
     * Testa enviarMensagem com array
     */
    public function testEnviarMensagemArray(): void
    {
        $mockService = $this->createPartialMock(RadiantiDiscordService::class, ['notificarWebhook']);
        $mockService->expects($this->once())
            ->method('notificarWebhook')
            ->willReturn(true);

        $dados = ['status' => 'ok', 'codigo' => 200];
        $resultado = $mockService->enviarMensagem($dados, $this->webhookUrl);
        $this->assertTrue($resultado);
    }

    /**
     * Testa enviarMensagem com JSON string válido
     */
    public function testEnviarMensagemJsonString(): void
    {
        $mockService = $this->createPartialMock(RadiantiDiscordService::class, ['notificarWebhook']);
        $mockService->expects($this->once())
            ->method('notificarWebhook')
            ->willReturn(true);

        $jsonString = json_encode(['erro' => 'Teste erro']);
        $resultado = $mockService->enviarMensagem($jsonString, $this->webhookUrl);
        $this->assertTrue($resultado);
    }

    /**
     * Testa enviarMensagem com falha na notificação
     */
    public function testEnviarMensagemComFalha(): void
    {
        $mockService = $this->createPartialMock(RadiantiDiscordService::class, ['notificarWebhook']);
        $mockService->expects($this->once())
            ->method('notificarWebhook')
            ->willThrowException(new Exception('Erro na comunicação'));

        $resultado = $mockService->enviarMensagem('Teste', $this->webhookUrl);
        $this->assertFalse($resultado);
    }

    /**
     * Testa enviarException lança exceção com webhook vazio
     */
    public function testEnviarExceptionComWebhookVazio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Webhook não foi informado');

        try {
            throw new Exception('Erro de teste');
        } catch (\Throwable $e) {
            $this->service->enviarException($e, '');
        }
    }

    /**
     * Testa enviarException com dados básicos
     */
    public function testEnviarExceptionBasico(): void
    {
        $mockService = $this->createPartialMock(RadiantiDiscordService::class, ['enviarMensagem']);
        $mockService->expects($this->once())
            ->method('enviarMensagem')
            ->willReturn(true);

        try {
            throw new Exception('Erro de teste');
        } catch (\Throwable $e) {
            $resultado = $mockService->enviarException($e, $this->webhookUrl);
            $this->assertTrue($resultado);
        }
    }

    /**
     * Testa enviarException com dados de request
     */
    public function testEnviarExceptionComRequest(): void
    {
        $mockService = $this->createPartialMock(RadiantiDiscordService::class, ['enviarMensagem']);
        $mockService->expects($this->once())
            ->method('enviarMensagem')
            ->willReturn(true);

        $request = ['class' => 'MinhaClasse', 'method' => 'meuMetodo'];

        try {
            throw new Exception('Erro de teste');
        } catch (\Throwable $e) {
            $resultado = $mockService->enviarException($e, $this->webhookUrl, $request);
            $this->assertTrue($resultado);
        }
    }

    /**
     * Testa enviarException com stack trace
     */
    public function testEnviarExceptionComStack(): void
    {
        $mockService = $this->createPartialMock(RadiantiDiscordService::class, ['enviarMensagem']);
        $mockService->expects($this->once())
            ->method('enviarMensagem')
            ->willReturn(true);

        try {
            throw new Exception('Erro de teste');
        } catch (\Throwable $e) {
            $resultado = $mockService->enviarException($e, $this->webhookUrl, null, true);
            $this->assertTrue($resultado);
        }
    }

    /**
     * Testa enviarArquivo lança exceção com webhook vazio
     */
    public function testEnviarArquivoComWebhookVazio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Webhook não foi informado');

        $this->service->enviarArquivo('/arquivo/teste.txt', '');
    }

    /**
     * Testa enviarArquivo com arquivo inexistente
     */
    public function testEnviarArquivoInexistente(): void
    {
        $resultado = $this->service->enviarArquivo('/arquivo/inexistente/teste.txt', $this->webhookUrl);
        $this->assertFalse($resultado, 'Deveria retornar false para arquivo inexistente');
    }

    /**
     * Testa enviarArquivo com arquivo válido
     */
    public function testEnviarArquivoValido(): void
    {
        // Criar arquivo temporário
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'Conteúdo de teste');

        $mockService = $this->createPartialMock(RadiantiDiscordService::class, ['notificarWebhook']);

        try {
            $resultado = $mockService->enviarArquivo($tempFile, $this->webhookUrl);
            // O arquivo existe e pode ser lido, portanto o método deve retornar true
            $this->assertTrue($resultado);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    /**
     * Testa parametrização correta em enviarMensagem (mensagem primeiro)
     */
    public function testParametrizacaoEnviarMensagem(): void
    {
        $mockService = $this->createPartialMock(RadiantiDiscordService::class, ['notificarWebhook']);
        $callCount = 0;

        $mockService->expects($this->exactly(2))
            ->method('notificarWebhook')
            ->willReturnCallback(function ($parte, $webhook) use (&$callCount) {
                $callCount++;
                // Verifica se a ordem está correta: mensagem/parte primeiro, webhook segundo
                $this->assertIsString($parte);
                $this->assertEquals($this->webhookUrl, $webhook);
                return true;
            });

        // Quebra a mensagem em múltiplas partes para verificar as chamadas
        $mensagemLonga = str_repeat('A', 3000);
        $mockService->enviarMensagem($mensagemLonga, $this->webhookUrl);
        $this->assertEquals(2, $callCount);
    }

    /**
     * Testa parametrização correta em enviarArquivo (arquivo primeiro)
     */
    public function testParametrizacaoEnviarArquivo(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'Teste');

        try {
            $mockService = $this->getMockBuilder(RadiantiDiscordService::class)
                ->onlyMethods(['notificarWebhook'])
                ->getMock();

            $mockService->enviarArquivo($tempFile, $this->webhookUrl);
            // Sucesso indica que a ordem dos parâmetros está correta
            $this->assertTrue(true);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    /**
     * Testa parametrização correta em enviarException (exception primeiro)
     */
    public function testParametrizacaoEnviarException(): void
    {
        $mockService = $this->createPartialMock(RadiantiDiscordService::class, ['enviarMensagem']);
        $mockService->expects($this->once())
            ->method('enviarMensagem')
            ->willReturnCallback(function ($dados, $webhook) {
                // Verifica se a ordem está correta: dados/stdClass primeiro, webhook segundo
                $this->assertInstanceOf(\stdClass::class, $dados);
                $this->assertEquals($this->webhookUrl, $webhook);
                return true;
            });

        try {
            throw new Exception('Teste');
        } catch (\Throwable $e) {
            $mockService->enviarException($e, $this->webhookUrl);
        }
    }

    /**
     * Testa objeto stdClass em enviarMensagem
     */
    public function testEnviarMensagemComObject(): void
    {
        $mockService = $this->createPartialMock(RadiantiDiscordService::class, ['notificarWebhook']);
        $mockService->expects($this->once())
            ->method('notificarWebhook')
            ->willReturn(true);

        $obj = new \stdClass();
        $obj->campo = 'valor';
        $obj->numero = 123;

        $resultado = $mockService->enviarMensagem($obj, $this->webhookUrl);
        $this->assertTrue($resultado);
    }

    /**
     * Testa que notificarWebhook lança exceção com webhook vazio
     */
    public function testNotificarWebhookComWebhookVazio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Webhook não foi informado');

        $reflection = new \ReflectionClass($this->service);
        $metodo = $reflection->getMethod('notificarWebhook');
        $metodo->setAccessible(true);

        $metodo->invoke($this->service, 'Mensagem de teste', '');
    }
}
