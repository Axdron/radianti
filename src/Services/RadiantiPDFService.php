<?php

namespace Axdron\Radianti\Services;

use Adianti\Registry\TSession;
use Adianti\Widget\Dialog\TMessage;
use Exception;

class RadiantiPDFService
{

    public static function gerarPDFHTML($nomeArquivo, $conteudoHtml, $snIncluiTextoRodape = true, string $orientacao = 'retrato')
    {
        try {
            if ($snIncluiTextoRodape) {
                if (empty(getenv('RADIANTI_VARIAVEL_LOGIN')))
                    throw new Exception('Variável de ambiente RADIANTI_VARIAVEL_LOGIN não definida');
                $conteudoHtml .= "<br><br> Gerado em: " . date('d/m/y H:i') . " por " . TSession::getValue(getenv('RADIANTI_VARIAVEL_LOGIN'));
            }

            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($conteudoHtml);

            switch ($orientacao) {
                case 'retrato':
                    $orientacao = 'portrait';
                    break;
                case 'paisagem':
                    $orientacao = 'landscape';
                    break;
                default:
                    $orientacao = 'portrait';
            }

            $dompdf->setPaper('A4', $orientacao);
            $dompdf->render();

            $arquivo = RadiantiArquivoTemporario::criar($nomeArquivo, 'pdf', $dompdf->output());

            return $arquivo;
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            return false;
        }
    }
}
