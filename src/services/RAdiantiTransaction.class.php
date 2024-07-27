<?php

use Adianti\Database\TTransaction;
use Adianti\Widget\Dialog\TMessage;

class RAdiantiTransaction
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
        return self::encapsularTransacao($callback. $snEmiteTMessage);
    }

    public static function encapsularTransacao($callback, $snEmiteTMessage = true, $snAbrirTransacao = true)
    {

        try {
            if ($snAbrirTransacao)
                TTransaction::open('sample');
            else
                TTransaction::openFake('sample');
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
