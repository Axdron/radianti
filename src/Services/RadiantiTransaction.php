<?php

namespace Axdron\Radianti\Services;

use Adianti\Database\TTransaction;
use Adianti\Widget\Dialog\TMessage;
use Exception;
use PDO;

class RadiantiTransaction
{

    /**
     * Executa uma query SQL bruta dentro de uma transação fake.
     * A query deve começar com SELECT, caso contrário, uma exceção será lançada.
     * 
     * @param string $query A query SQL a ser executada.
     * @param bool $snEmiteTMessage Indica se deve emitir uma mensagem de erro usando TMessage.
     * @param string|null $nomeBd O nome do banco de dados a ser usado.
     * @return array O resultado da query como um array de objetos.
     * @throws \Throwable Se ocorrer um erro e $snEmiteTMessage for false.
     */
    public static function executarConsultaBruta($query, $snEmiteTMessage = true, $nomeBd = null): array
    {
        $resultado = self::consultar(function () use ($query) {
            if (strpos(strtolower($query), 'select') === false) {
                throw new Exception('A query deve começar com select');
            }


            $result = static::executarQuery($query);

            if (isset($result)) {
                return $result;
            }

            return false;
        }, $snEmiteTMessage, $nomeBd);

        if (empty($resultado)) {
            return [];
        }

        return (array)$resultado;
    }

    /**
     * @deprecated Desde v3.14.0. Use {@link executarConsultaBruta()} em seu lugar.
     * Esta função será removida em uma versão futura. O nome atual não representa corretamente
     * o comportamento da função, que executa queries SQL brutas sem gerenciar transações.
     * 
     * Executa uma query SQL de consulta dentro de uma transação aberta.
     * A query deve começar com SELECT, caso contrário, uma exceção será lançada. 
     * @param string $query A query SQL a ser executada.
     * @param bool $snEmiteTMessage Indica se deve emitir uma mensagem de erro usando TMessage.
     * @param string|null $nomeBd O nome do banco de dados a ser usado.
     * @return array O resultado da query como um array de objetos.
     * @throws \Throwable Se ocorrer um erro e $snEmiteTMessage for false. 
     */
    public static function executarQueryComTransacao($query, $snEmiteTMessage = true, $nomeBd = null): array
    {
        return self::executarConsultaBruta($query, $snEmiteTMessage, $nomeBd);
    }

    /**
     * Executa um callback dentro de uma transação fake.
     * Abre uma transação fake, executa o callback e fecha a transação.
     * Em caso de erro, encerra a transação fake e opcionalmente emite uma mensagem de erro.
     * Essa função é um facilitador para operações de consulta de dados.
     * @param callable $callback A função a ser executada dentro da transação.
     * @param bool $snEmiteTMessage Indica se deve emitir uma mensagem de erro usando TMessage.
     * @param string|null $nomeBd O nome do banco de dados a ser usado. Se nulo, usa a variável de ambiente RADIANTI_DB_NAME.
     * @return mixed O resultado do callback, se bem-sucedido. 
     * @throws \Throwable Se ocorrer um erro e $snEmiteTMessage for false.
     */
    public static function consultar($callback, $snEmiteTMessage = true, $nomeBd = null)
    {
        return self::encapsularTransacao($callback, $snEmiteTMessage, false, $nomeBd);
    }

    /**
     * Versão de consultar que não emite mensagens de erro.
     * Usado em APIs para retornar erros via JSON.
     * @param callable $callback A função a ser executada dentro da transação.
     * @param string|null $nomeBd O nome do banco de dados a ser usado. Se nulo, usa a variável de ambiente RADIANTI_DB_NAME.
     * @return mixed O resultado do callback, se bem-sucedido. 
     * @throws \Throwable Se ocorrer um erro.
     */
    public static function consultarAPI($callback, $nomeBd = null)
    {
        return self::consultar($callback, false, $nomeBd);
    }

    /**
     * Executa um callback dentro de uma transação.
     * Abre uma transação, executa o callback e fecha a transação.
     * Em caso de erro, faz rollback da transação e opcionalmente emite uma mensagem de erro.
     * Essa função é um facilitador para operações de salvar/atualizar/excluir dados.
     * @param callable $callback A função a ser executada dentro da transação.
     * @param bool $snEmiteTMessage Indica se deve emitir uma mensagem de erro usando TMessage.
     * @param string|null $nomeBd O nome do banco de dados a ser usado. Se nulo, usa a variável de ambiente RADIANTI_DB_NAME.
     * @return mixed O resultado do callback, se bem-sucedido. 
     * @throws \Throwable Se ocorrer um erro e $snEmiteTMessage for false.
     */
    public static function salvar($callback, $snEmiteTMessage = true, $nomeBd = null)
    {
        return self::encapsularTransacao($callback, $snEmiteTMessage, true, $nomeBd);
    }

    /**
     * Versão de salvar que não emite mensagens de erro.
     * Usado em APIs para retornar erros via JSON.
     * @param callable $callback A função a ser executada dentro da transação.
     * @param string|null $nomeBd O nome do banco de dados a ser usado. Se nulo, usa a variável de ambiente RADIANTI_DB_NAME.
     * @return mixed O resultado do callback, se bem-sucedido. 
     * @throws \Throwable Se ocorrer um erro.
     */
    public static function salvarAPI($callback, $nomeBd = null)
    {
        return self::salvar($callback, false, $nomeBd);
    }

    /**
     * Encapsula a execução de um callback dentro de uma transação.
     * Abre uma transação, executa o callback e fecha a transação.
     * Em caso de erro, faz rollback da transação e opcionalmente emite uma mensagem de erro.
     * @param callable $callback A função a ser executada dentro da transação.
     * @param bool $snEmiteTMessage Indica se deve emitir uma mensagem de erro usando TMessage.
     * @param bool $snAbrirTransacao Indica se deve abrir uma transação real ou fake.
     * @param string|null $nomeBd O nome do banco de dados a ser usado. Se nulo, usa a variável de ambiente RADIANTI_DB_NAME.
     * @return mixed O resultado do callback, se bem-sucedido. 
     * @throws \Throwable Se ocorrer um erro e $snEmiteTMessage for false.
     */
    public static function encapsularTransacao($callback, $snEmiteTMessage = true, $snAbrirTransacao = true, $nomeBd = null)
    {
        $snTransacaoAberta = false;

        try {
            if (empty($nomeBd) && empty(getenv('RADIANTI_DB_NAME')))
                throw new \Exception('Variável de ambiente RADIANTI_DB_NAME não definida');

            $dbSolicitado = $nomeBd ?? getenv('RADIANTI_DB_NAME');

            if ($snAbrirTransacao) {
                static::abrirTransacao($dbSolicitado);
                $snTransacaoAberta = true;
            } else {
                // Verifica se já há conexão ativa no banco correto
                $conn = static::obterConexao();
                $dbAtivo = static::obterBancoDados();

                if (!$conn || $dbAtivo !== $dbSolicitado) {
                    static::abrirTransacaoFake($dbSolicitado);
                    $snTransacaoAberta = true;
                }
            }
            $retorno = $callback();
            if ($snTransacaoAberta) {
                static::fecharTransacao();
            }
            return $retorno;
        } catch (\Throwable $th) {

            if ($snTransacaoAberta) {
                if ($snAbrirTransacao)
                    static::fazerRollback();
                else
                    static::fecharTransacao();
            }

            if ($snEmiteTMessage) {
                static::enviarMensagem('error', $th->getMessage());
                return;
            }
            throw $th;
        }
    }

    /**
     * Abre uma transação real.
     * Método protegido para permitir mockagem em testes.
     * 
     * @param string $nomeBd O nome do banco de dados.
     * @return void
     */
    protected static function abrirTransacao($nomeBd)
    {
        TTransaction::open($nomeBd);
    }

    /**
     * Abre uma transação fake.
     * Método protegido para permitir mockagem em testes.
     * 
     * @param string $nomeBd O nome do banco de dados.
     * @return void
     */
    protected static function abrirTransacaoFake($nomeBd)
    {
        TTransaction::openFake($nomeBd);
    }

    /**
     * Fecha a transação atual.
     * Método protegido para permitir mockagem em testes.
     * 
     * @return void
     */
    protected static function fecharTransacao()
    {
        TTransaction::close();
    }

    /**
     * Faz rollback da transação atual.
     * Método protegido para permitir mockagem em testes.
     * 
     * @return void
     */
    protected static function fazerRollback()
    {
        TTransaction::rollback();
    }

    /**
     * Obtém a conexão atual do TTransaction.
     * Método protegido para permitir mockagem em testes.
     * 
     * @return mixed A conexão ou null se não houver.
     */
    protected static function obterConexao()
    {
        return TTransaction::get();
    }

    /**
     * Obtém o banco de dados ativo no TTransaction.
     * Método protegido para permitir mockagem em testes.
     * 
     * @return string|null O nome do banco de dados ativo.
     */
    protected static function obterBancoDados()
    {
        return TTransaction::getDatabase();
    }

    /**
     * Envia uma mensagem usando TMessage.
     * Método protegido para permitir mockagem em testes.
     * 
     * @param string $tipo O tipo da mensagem (error, info, success, etc).
     * @param string $conteudo O conteúdo da mensagem.
     * @return void
     */
    protected static function enviarMensagem($tipo, $conteudo)
    {
        new TMessage($tipo, $conteudo);
    }

    /**
     * Executa uma query SQL preparada.
     * Método protegido para permitir mockagem em testes.
     * 
     * @param string $query A query SQL a executar.
     * @return array O resultado como array de objetos.
     */
    protected static function executarQuery($query)
    {
        $conn = static::obterConexao();

        if (!$conn) {
            throw new Exception('Não há transação aberta!');
        }

        $sth = $conn->prepare($query);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_OBJ);
    }
}
