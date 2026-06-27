<?php

namespace Axdron\Radianti\Services\RadiantiMailService;

/**
 * Representa um anexo de e-mail a ser enviado pelo RadiantiEmailService.
 *
 * Esta classe encapsula as informações necessárias para adicionar um arquivo
 * como anexo a um e-mail, incluindo o nome do arquivo, caminho local e tipo MIME.
 *
 * @example
 * $anexo = new RadiantiEmailAnexo();
 * $anexo->nome = 'documento.pdf';
 * $anexo->caminho = '/path/to/documento.pdf';
 * $anexo->tipo = 'application/pdf';
 *
 * @package Axdron\Radianti\Services\RadiantiMailService
 */
class RadiantiEmailAnexo
{
    /**
     * Nome do arquivo anexo (como aparecerá no e-mail).
     * Exemplo: 'documento.pdf', 'planilha.xlsx'
     *
     * @var string
     */
    public string $nome;

    /**
     * Caminho absoluto do arquivo no servidor.
     * Exemplo: '/tmp/arquivo_123.pdf'
     *
     * @var string
     */
    public string $caminho;

    /**
     * Tipo MIME do arquivo.
     * Exemplos: 'application/pdf', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'image/png'
     *
     * @var string
     */
    public string $tipo;
}
