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
            if ($snAbrirTransacao)
                TTransaction::open(getenv('DB_NAME_RADIANTI'));
            else
                TTransaction::openFake(getenv('DB_NAME_RADIANTI'));
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
