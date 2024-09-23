<?php

namespace Axdron\Radianti\Services;

use Adianti\Database\TTransaction;
use Adianti\Widget\Dialog\TMessage;

class RadiantiTransaction
{
    public static function consultar($callback, $snEmiteTMessage = true)
    {
        return self::encapsularTransacao($callback, $snEmiteTMessage, false);
    }

    public static function consultarAPI($callback)
    {
        return self::encapsularTransacao($callback, false, false);
    }

    public static function salvarAPI($callback)
    {
        return self::encapsularTransacao($callback, false, true);
    }

    public static function salvar($callback, $snEmiteTMessage = true)
    {
        return self::encapsularTransacao($callback, $snEmiteTMessage);
    }

    public static function encapsularTransacao($callback, $snEmiteTMessage = true, $snAbrirTransacao = true)
    {
        try {
            if (empty(getenv('RADIANTI_DB_NAME')))
                throw new \Exception('Variável de ambiente RADIANTI_DB_NAME não definida');

            if ($snAbrirTransacao)
                TTransaction::open(getenv('RADIANTI_DB_NAME'));
            else
                TTransaction::openFake(getenv('RADIANTI_DB_NAME'));
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
