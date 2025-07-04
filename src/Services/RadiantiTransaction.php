<?php

namespace Axdron\Radianti\Services;

use Adianti\Database\TTransaction;
use Adianti\Widget\Dialog\TMessage;
use Exception;
use PDO;

class RadiantiTransaction
{

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

    public static function consultar($callback, $snEmiteTMessage = true, $nomeBd = null)
    {
        return self::encapsularTransacao($callback, $snEmiteTMessage, false, $nomeBd);
    }

    public static function consultarAPI($callback, $nomeBd = null)
    {
        return self::consultar($callback, false, $nomeBd);
    }


    public static function salvar($callback, $snEmiteTMessage = true, $nomeBd = null)
    {
        return self::encapsularTransacao($callback, $snEmiteTMessage, true, $nomeBd);
    }

    public static function salvarAPI($callback, $nomeBd = null)
    {
        return self::salvar($callback, false, $nomeBd);
    }

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
