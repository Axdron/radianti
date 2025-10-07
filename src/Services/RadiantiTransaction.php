<?php

namespace Axdron\Radianti\Services;

use Adianti\Database\TTransaction;
use Adianti\Widget\Dialog\TMessage;
use Exception;
use PDO;

class RadiantiTransaction
{

    /**
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
        $resultado =  self::consultar(function () use ($query) {
            if (strpos(strtolower($query), 'select') === false) {
                throw new Exception('A query deve começar com select');
            }

            $conn = TTransaction::get();

            if (!$conn) {
                throw new Exception('Não há transação aberta!');
            }

            $sth = $conn->prepare($query);

            $sth->execute();

            $result = $sth->fetchAll(PDO::FETCH_OBJ);

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
        try {
            if (empty($nomeBd) && empty(getenv('RADIANTI_DB_NAME')))
                throw new \Exception('Variável de ambiente RADIANTI_DB_NAME não definida');

            if ($snAbrirTransacao)
                TTransaction::open($nomeBd ?? getenv('RADIANTI_DB_NAME'));
            else
                TTransaction::openFake($nomeBd ?? getenv('RADIANTI_DB_NAME'));
            $retorno = $callback();
            TTransaction::close();
            return $retorno;
        } catch (\Throwable $th) {

            if ($snAbrirTransacao)
                TTransaction::rollback();
            else
                TTransaction::close();

            if ($snEmiteTMessage) {
                new TMessage('error', $th->getMessage());
                return;
            }
            throw $th;
        }
    }
}
