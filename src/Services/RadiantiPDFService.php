<?php

namespace Axdron\Radianti\Services;

use Adianti\Registry\TSession;
use Adianti\Widget\Dialog\TMessage;
use Exception;

class RadiantiPDFService
{

    /**
     * Gera um arquivo PDF a partir de um conteúdo HTML
     *
     * @param string $nomeArquivo Nome do arquivo PDF a ser gerado
     * @param string $conteudoHtml Conteúdo HTML que será convertido em PDF
     * @param bool $snIncluiTextoRodape Indica se deve incluir o texto de rodapé no PDF (padrão: true)
     * @param string $orientacao Orientação do PDF ('retrato' ou 'paisagem', padrão: 'retrato')
     * @return mixed Retorna o caminho do arquivo PDF gerado ou false em caso de erro
     */
    public static function gerarPDFHTML($nomeArquivo, $conteudoHtml, $snIncluiTextoRodape = true, string $orientacao = 'retrato')
    {
        try {
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

            if ($snIncluiTextoRodape) {
                self::gerarTextoRodape($dompdf);
            }

            $arquivo = RadiantiArquivoTemporario::criar($nomeArquivo, 'pdf', $dompdf->output());

            return $arquivo;
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            return false;
        }
    }

    public static function gerarTextoRodape(\Dompdf\Dompdf &$dompdf)
    {
        if (empty(getenv('RADIANTI_VARIAVEL_LOGIN')))
            throw new Exception('Variável de ambiente RADIANTI_VARIAVEL_LOGIN não definida, não é possível gerar o rodapé do PDF.');

        $nomeUsuario = TSession::getValue(getenv('RADIANTI_VARIAVEL_LOGIN'));
        $dataHora = date('d/m/y H:i:s');

        // Adicionar rodapé em cada página usando page_text()
        $canvas = $dompdf->getCanvas();
        $y = $canvas->get_height() - 25;

        // Texto do rodapé consolidado alinhado à esquerda
        $textRodape = "Página {PAGE_NUM}/{PAGE_COUNT} gerada por " . $nomeUsuario . " em " . $dataHora;
        $canvas->page_text(50, $y, $textRodape, null, 10);
        return "<br><br> Página gerada por " . $nomeUsuario . " em " . date('d/m/y H:i:s');
    }
}
