<?php

use Adianti\Widget\Dialog\TMessage;

class RAdiantiPDFService {

    public static function gerarPDFHTML($nomeArquivo, $conteudoHtml, $snIncluiTextoRodape = true, string $orientacao = 'retrato')
    {
        try {
            if ($snIncluiTextoRodape)
                $conteudoHtml .= "<br><br> Gerado em: " . date('d/m/y H:i') . " por " . SessaoService::buscarLoginUsuario();

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

            $arquivo = ArquivoTemporario::criar($nomeArquivo, 'pdf', $dompdf->output());

            return $arquivo;
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            return false;
        }
    }

}