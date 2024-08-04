<?php

use Adianti\Widget\Dialog\TMessage;

class RAdiantiXLSXService{
    public static function gerarXLSX($nomeArquivo, array $matrizDados)
    {
        try {
            if ($matrizDados) {
                $arquivo = ArquivoTemporario::criar($nomeArquivo, 'xlsx');
                $xlsx = SimpleXLSXGen::fromArray($matrizDados);
                $xlsx->saveAs($arquivo);
                return $arquivo;
            }
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            return false;
        }
    }
}