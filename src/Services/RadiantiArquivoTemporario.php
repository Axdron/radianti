<?php

namespace Services;

use Adianti\Widget\Dialog\TMessage;

class RadiantiArquivoTemporario
{

    static public function criar(String $nomeArquivoSemExtensao, String $extensao, $conteudo = null)
    {
        try {
            if (!$nomeArquivoSemExtensao || !$extensao)
                new TMessage("error", "Não gerará o arquivo se o nome ou a extensão não forem informados! Entre em contato com a Appelsoft!");
            $caminho = tempnam(sys_get_temp_dir(), $nomeArquivoSemExtensao);
            rename($caminho, $caminho .= '.' . $extensao);
            if ($conteudo)
                file_put_contents($caminho, $conteudo);
            return $caminho;
        } catch (\Throwable $th) {
            new TMessage('error', "Erro ao gerar arquivo" . $th->getMessage());
        }
    }
}
