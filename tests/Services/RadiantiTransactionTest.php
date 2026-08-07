<?php

namespace Tests\Services;

use Axdron\Radianti\Services\RadiantiTransaction;
use PHPUnit\Framework\TestCase;

class RadiantiTransactionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        putenv('RADIANTI_DB_NAME=test_db');
        MockedRadiantiTransaction::reset();
    }

    // ========== Testes: Abertura de transações ==========

    /**
     * Testa se uma consulta avulsa abre uma transação fake
     */
    public function testConsultaAvulsaAbreTransacaoFake()
    {
        $resultado = MockedRadiantiTransaction::consultar(function () {
            return ['resultado' => 'ok'];
        });

        $this->assertTrue(MockedRadiantiTransaction::$abrirTransacaoFakeFoiChamado);
        $this->assertFalse(MockedRadiantiTransaction::$abrirTransacaoFoiChamado);
        $this->assertEquals(['resultado' => 'ok'], $resultado);
    }

    /**
     * Testa se um salvamento abre uma transação real
     */
    public function testSalvamentoAbreTransacaoReal()
    {
        MockedRadiantiTransaction::reset();

        $resultado = MockedRadiantiTransaction::salvar(function () {
            return ['salvo' => true];
        });

        $this->assertTrue(MockedRadiantiTransaction::$abrirTransacaoFoiChamado);
        $this->assertFalse(MockedRadiantiTransaction::$abrirTransacaoFakeFoiChamado);
        $this->assertEquals(['salvo' => true], $resultado);
    }

    /**
     * Testa se uma consulta dentro de outra consulta NÃO abre nova transação
     * (quando ambas usam o mesmo banco de dados)
     */
    public function testConsultaDentroDeConsultaNaoAbreNovaTransacao()
    {
        MockedRadiantiTransaction::reset();

        MockedRadiantiTransaction::consultar(function () {
            // Primeira consulta abre fake, segunda reutiliza
            MockedRadiantiTransaction::consultar(function () {
                return ['resultado' => 'ok'];
            });
        });

        // Apenas 1 abrirTransacaoFake deve ter sido chamado
        $this->assertEquals(1, MockedRadiantiTransaction::$contagemAbrirTransacaoFake);
        // Nenhum abrirTransacao real
        $this->assertEquals(0, MockedRadiantiTransaction::$contagemAbrirTransacao);
    }

    /**
     * Testa se um salvamento dentro de uma consulta abre uma transação real
     */
    public function testSalvamentoDentroDeConsultaAbreTransacaoReal()
    {
        MockedRadiantiTransaction::reset();
        MockedRadiantiTransaction::$conexaoAtiva = true;
        MockedRadiantiTransaction::$bancoAtivo = 'test_db';

        MockedRadiantiTransaction::consultar(function () {
            // Primeira consulta é fake, agora salvamento deve abrir real
            MockedRadiantiTransaction::reset();

            MockedRadiantiTransaction::salvar(function () {
                return ['salvo' => true];
            });
        });

        // O salvamento dentro da consulta deve chamar abrirTransacao (real)
        $this->assertTrue(MockedRadiantiTransaction::$abrirTransacaoFoiChamado);
    }

    // ========== Testes: Fechamento de transações ==========

    /**
     * Testa se uma transação aberta é fechada corretamente
     */
    public function testTransacaoEhFechadaAposConcluida()
    {
        MockedRadiantiTransaction::consultar(function () {
            return 'ok';
        });

        $this->assertTrue(MockedRadiantiTransaction::$fecharTransacaoFoiChamado);
    }

    /**
     * Testa se rollback é chamado quando ocorre erro em salvamento
     */
    public function testRollbackEhChamadoQuandoOcorreErroEmSalvamento()
    {
        try {
            MockedRadiantiTransaction::salvar(function () {
                throw new \Exception('Erro intencional');
            }, false);
        } catch (\Exception $e) {
            // Esperado
        }

        $this->assertTrue(MockedRadiantiTransaction::$fazerRollbackFoiChamado);
    }

    /**
     * Testa se rollback é chamado apenas para transações abertas
     */
    public function testRollbackNaoEhChamadoQuandoTransacaoNaoFoiAberta()
    {
        MockedRadiantiTransaction::reset();
        MockedRadiantiTransaction::$conexaoAtiva = true;
        MockedRadiantiTransaction::$bancoAtivo = 'test_db';

        try {
            MockedRadiantiTransaction::consultar(function () {
                throw new \Exception('Erro intencional');
            }, false);
        } catch (\Exception $e) {
            // Esperado
        }

        // Consulta não abre transação real, apenas fake. Em caso de erro, faz close, não rollback
        $this->assertFalse(MockedRadiantiTransaction::$fazerRollbackFoiChamado);
    }

    // ========== Testes: Validações ==========

    /**
     * Testa se exceção é lançada quando RADIANTI_DB_NAME não está definido
     */
    public function testExcecaoQuandoBancoDadosNaoDefinido()
    {
        // Remove a variável de ambiente temporariamente
        $original = getenv('RADIANTI_DB_NAME');
        putenv('RADIANTI_DB_NAME=');

        try {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Variável de ambiente RADIANTI_DB_NAME não definida');

            MockedRadiantiTransaction::consultar(function () {
                return 'ok';
            }, false, null);
        } finally {
            // Restaura a variável original
            putenv('RADIANTI_DB_NAME=' . $original);
        }
    }

    /**
     * Testa se banco de dados específico pode ser passado como parâmetro
     */
    public function testBancoDadosEspecificoViaParametro()
    {
        MockedRadiantiTransaction::reset();

        MockedRadiantiTransaction::consultar(function () {
            return 'ok';
        }, true, 'outro_db');

        // Deve ter chamado abrirTransacaoFake com outro_db
        $this->assertEquals('outro_db', MockedRadiantiTransaction::$ultimoBdSolicitado);
    }

    // ========== Testes: Mensagens de erro ==========

    /**
     * Testa se mensagem de erro é enviada quando snEmiteTMessage é true
     */
    public function testMensagemErroEhEnviadaQuandoSolicitado()
    {
        MockedRadiantiTransaction::reset();

        MockedRadiantiTransaction::consultar(function () {
            throw new \Exception('Erro de teste');
        }, true);

        $this->assertTrue(MockedRadiantiTransaction::$enviarMensagemFoiChamado);
        $this->assertEquals('error', MockedRadiantiTransaction::$ultimoTipoMensagem);
        $this->assertEquals('Erro de teste', MockedRadiantiTransaction::$ultimoConteudoMensagem);
    }

    /**
     * Testa se mensagem de erro NÃO é enviada quando snEmiteTMessage é false
     */
    public function testMensagemErroNaoEhEnviadaQuandoNaoSolicitado()
    {
        MockedRadiantiTransaction::reset();

        try {
            MockedRadiantiTransaction::consultar(function () {
                throw new \Exception('Erro de teste');
            }, false);
        } catch (\Exception $e) {
            // Esperado
        }

        $this->assertFalse(MockedRadiantiTransaction::$enviarMensagemFoiChamado);
    }

    // ========== Testes: Retorno de valores ==========

    /**
     * Testa se o resultado do callback é retornado corretamente
     */
    public function testResultadoCallbackEhRetornado()
    {
        $expectedResult = ['id' => 1, 'nome' => 'Test'];

        $result = MockedRadiantiTransaction::consultar(function () use ($expectedResult) {
            return $expectedResult;
        });

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Testa se null é retornado quando há erro e snEmiteTMessage é true
     */
    public function testNullEhRetornadoQuandoErroComMensagem()
    {
        MockedRadiantiTransaction::reset();

        $result = MockedRadiantiTransaction::consultar(function () {
            throw new \Exception('Erro');
        }, true);

        $this->assertNull($result);
    }

    // ========== Testes: API Methods ==========

    /**
     * Testa consultarAPI (deve ser equivalente a consultar com snEmiteTMessage=false)
     */
    public function testConsultarAPILancaExcecaoEmErro()
    {
        MockedRadiantiTransaction::reset();

        $this->expectException(\Exception::class);

        MockedRadiantiTransaction::consultarAPI(function () {
            throw new \Exception('Erro intencional');
        });
    }

    /**
     * Testa salvarAPI (deve ser equivalente a salvar com snEmiteTMessage=false)
     */
    public function testSalvarAPILancaExcecaoEmErro()
    {
        MockedRadiantiTransaction::reset();

        $this->expectException(\Exception::class);

        MockedRadiantiTransaction::salvarAPI(function () {
            throw new \Exception('Erro intencional');
        });
    }

    /**
     * Testa se consultarAPI executa sem lançar exceção em sucesso
     */
    public function testConsultarAPIRetornaResultadoEmSucesso()
    {
        MockedRadiantiTransaction::reset();

        $result = MockedRadiantiTransaction::consultarAPI(function () {
            return ['ok' => true];
        });

        $this->assertEquals(['ok' => true], $result);
    }

    // ========== Testes: Contagem de transações ==========

    /**
     * Testa se 2 consultas aninhadas abrem 1 transação fake só
     */
    public function testDuasConsultasAninhadas_AbreSoUmaTransacao()
    {
        MockedRadiantiTransaction::reset();

        MockedRadiantiTransaction::consultar(function () {
            // Primeira consulta abre fake
            MockedRadiantiTransaction::consultar(function () {
                // Segunda consulta não deve abrir (reutiliza conexão)
                return 'ok';
            });
            return 'ok';
        });

        // Apenas 1 abrirTransacaoFake deve ter sido chamado
        $this->assertEquals(1, MockedRadiantiTransaction::$contagemAbrirTransacaoFake);
        $this->assertEquals(0, MockedRadiantiTransaction::$contagemAbrirTransacao);
    }

    /**
     * Testa se 2 salvar aninhados abrem 2 transações reais
     */
    public function testDoisSalvarAninhados_AbreDuasTransacoes()
    {
        MockedRadiantiTransaction::reset();

        MockedRadiantiTransaction::salvar(function () {
            // Primeiro salvar abre transação real
            MockedRadiantiTransaction::salvar(function () {
                // Segundo salvar abre outra transação real
                return 'ok';
            });
            return 'ok';
        });

        // 2 abrirTransacao devem ter sido chamados
        $this->assertEquals(2, MockedRadiantiTransaction::$contagemAbrirTransacao);
        $this->assertEquals(0, MockedRadiantiTransaction::$contagemAbrirTransacaoFake);
    }

    /**
     * Testa se 1 salvar + 1 consultar aninhado abre 1 transação (só a do salvar)
     */
    public function testSalvarPrimeiro_ConsultarSegundo_AbreSoUmaTransacao()
    {
        MockedRadiantiTransaction::reset();

        MockedRadiantiTransaction::salvar(function () {
            // Primeiro salvar abre transação real
            MockedRadiantiTransaction::consultar(function () {
                // Consultar vê que já tem conexão real no banco correto, não abre fake
                return 'ok';
            });
            return 'ok';
        });

        // Apenas 1 abrirTransacao (do salvar)
        $this->assertEquals(1, MockedRadiantiTransaction::$contagemAbrirTransacao);
        // Nenhum abrirTransacaoFake (consultar reutiliza a conexão real)
        $this->assertEquals(0, MockedRadiantiTransaction::$contagemAbrirTransacaoFake);
    }

    /**
     * Testa se 1 consultar + 1 salvar aninhado abre 2 transações
     */
    public function testConsultarPrimeiro_SalvarSegundo_AbreAsDuasTransacoes()
    {
        MockedRadiantiTransaction::reset();

        MockedRadiantiTransaction::consultar(function () {
            // Primeira consulta abre transação fake
            MockedRadiantiTransaction::salvar(function () {
                // Segundo salvar SEMPRE abre transação real, mesmo que haja conexão fake
                return 'ok';
            });
            return 'ok';
        });

        // 1 abrirTransacao (do salvar)
        $this->assertEquals(1, MockedRadiantiTransaction::$contagemAbrirTransacao);
        // 1 abrirTransacaoFake (da consulta)
        $this->assertEquals(1, MockedRadiantiTransaction::$contagemAbrirTransacaoFake);
    }

    /**
     * Testa se Salvar + Consultar + Salvar + Consultar abre 2 transações reais
     *
     * Sequência esperada:
     * 1. Salvar → abre real (1)
     * 2. Consultar → reutiliza conexão real (0 novo)
     * 3. Salvar → abre nova real (2)
     * 4. Consultar → reutiliza conexão real (0 novo)
     * Total: 2 reais, 0 fake
     */
    public function testSalvarConsultarSalvarConsultar_AbreDuasTransacoes()
    {
        MockedRadiantiTransaction::reset();

        MockedRadiantiTransaction::salvar(function () {
            // Salvar #1 abre transação real

            MockedRadiantiTransaction::consultar(function () {
                // Consultar #2 reutiliza conexão real do salvar

                MockedRadiantiTransaction::salvar(function () {
                    // Salvar #2 abre OUTRA transação real

                    MockedRadiantiTransaction::consultar(function () {
                        // Consultar #4 reutiliza conexão real do salvar #2
                        return 'ok';
                    });
                    return 'ok';
                });
                return 'ok';
            });
            return 'ok';
        });

        // 2 abrirTransacao (salvar #1, salvar #2)
        $this->assertEquals(2, MockedRadiantiTransaction::$contagemAbrirTransacao);
        // 0 abrirTransacaoFake (todas as consultas reutilizaram)
        $this->assertEquals(0, MockedRadiantiTransaction::$contagemAbrirTransacaoFake);
    }

    /**
     * Testa se Consultar + Salvar + Consultar + Salvar + Salvar abre 3 transações reais e 1 fake
     *
     * Sequência esperada:
     * 1. Consultar → abre fake (1)
     * 2. Salvar → abre real (1)
     * 3. Consultar → reutiliza conexão real (0 novo)
     * 4. Salvar → abre nova real (2)
     * 5. Salvar → abre nova real (3)
     * Total: 3 reais, 1 fake
     */
    public function testConsultarSalvarConsultarSalvarSalvar_AbreTresReaisUmaFake()
    {
        MockedRadiantiTransaction::reset();

        MockedRadiantiTransaction::consultar(function () {
            // Consultar #1 abre transação fake

            MockedRadiantiTransaction::salvar(function () {
                // Salvar #1 abre transação real

                MockedRadiantiTransaction::consultar(function () {
                    // Consultar #2 reutiliza conexão real do salvar

                    MockedRadiantiTransaction::salvar(function () {
                        // Salvar #2 abre OUTRA transação real

                        MockedRadiantiTransaction::salvar(function () {
                            // Salvar #3 abre OUTRA transação real
                            return 'ok';
                        });
                        return 'ok';
                    });
                    return 'ok';
                });
                return 'ok';
            });
            return 'ok';
        });

        // 3 abrirTransacao (salvar #1, salvar #2, salvar #3)
        $this->assertEquals(3, MockedRadiantiTransaction::$contagemAbrirTransacao);
        // 1 abrirTransacaoFake (consultar #1)
        $this->assertEquals(1, MockedRadiantiTransaction::$contagemAbrirTransacaoFake);
    }
}

/**
 * Classe mocked que estende RadiantiTransaction para permitir espionagem de métodos protegidos
 */
class MockedRadiantiTransaction extends RadiantiTransaction
{
    // Flags para rastrear chamadas
    public static bool $abrirTransacaoFoiChamado = false;
    public static bool $abrirTransacaoFakeFoiChamado = false;
    public static bool $fecharTransacaoFoiChamado = false;
    public static bool $fazerRollbackFoiChamado = false;
    public static bool $enviarMensagemFoiChamado = false;

    // Contadores de chamadas
    public static int $contagemAbrirTransacao = 0;
    public static int $contagemAbrirTransacaoFake = 0;
    public static int $contagemFecharTransacao = 0;
    public static int $contagemRollback = 0;

    // Dados de simulação
    public static bool $conexaoAtiva = false;
    public static ?string $bancoAtivo = null;

    // Histórico
    public static ?string $ultimoBdSolicitado = null;
    public static ?string $ultimoTipoMensagem = null;
    public static ?string $ultimoConteudoMensagem = null;

    public static function reset()
    {
        self::$abrirTransacaoFoiChamado = false;
        self::$abrirTransacaoFakeFoiChamado = false;
        self::$fecharTransacaoFoiChamado = false;
        self::$fazerRollbackFoiChamado = false;
        self::$enviarMensagemFoiChamado = false;

        self::$contagemAbrirTransacao = 0;
        self::$contagemAbrirTransacaoFake = 0;
        self::$contagemFecharTransacao = 0;
        self::$contagemRollback = 0;

        self::$ultimoBdSolicitado = null;
        self::$ultimoTipoMensagem = null;
        self::$ultimoConteudoMensagem = null;

        self::$conexaoAtiva = false;
        self::$bancoAtivo = null;
    }

    protected static function abrirTransacao($nomeBd)
    {
        self::$abrirTransacaoFoiChamado = true;
        self::$contagemAbrirTransacao++;
        self::$ultimoBdSolicitado = $nomeBd;
        self::$conexaoAtiva = true;
        self::$bancoAtivo = $nomeBd;
        // Mock: não abre realmente
    }

    protected static function abrirTransacaoFake($nomeBd)
    {
        self::$abrirTransacaoFakeFoiChamado = true;
        self::$contagemAbrirTransacaoFake++;
        self::$ultimoBdSolicitado = $nomeBd;
        self::$conexaoAtiva = true;
        self::$bancoAtivo = $nomeBd;
    }

    protected static function fecharTransacao()
    {
        self::$fecharTransacaoFoiChamado = true;
        self::$contagemFecharTransacao++;
        self::$conexaoAtiva = false;
        // Mock: não fecha realmente
    }

    protected static function fazerRollback()
    {
        self::$fazerRollbackFoiChamado = true;
        self::$contagemRollback++;
        self::$conexaoAtiva = false;
        // Mock: não faz rollback realmente
    }

    protected static function obterConexao()
    {
        return self::$conexaoAtiva ? new \stdClass() : null;
    }

    protected static function obterBancoDados()
    {
        return self::$bancoAtivo;
    }

    protected static function enviarMensagem($tipo, $conteudo)
    {
        self::$enviarMensagemFoiChamado = true;
        self::$ultimoTipoMensagem = $tipo;
        self::$ultimoConteudoMensagem = $conteudo;
        // Mock: não envia mensagem realmente
    }

    protected static function executarQuery($query)
    {
        // Mock: retorna resultado simulado
        return [
            (object)['resultado' => 'mock']
        ];
    }
}
