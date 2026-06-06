<?php

namespace Axdron\Radianti\Services;

use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\StorageObject;


/**
 * Serviço para integração com o Google Cloud Storage, permitindo upload, download e exclusão de arquivos em buckets do GCP.
 * 
 * Atenção: a autenticação em ambiente local pode ser feita em duas formas:
 * 1. Passando o caminho do arquivo de credenciais JSON como parâmetro nos métodos (recomendado para desenvolvimento local).
 * 2. Executando o comando `gcloud auth application-default login` para configurar as credenciais de forma global, o que é útil para testes locais sem precisar passar o caminho do arquivo a cada chamada. 
 * 
 * Em ambiente de produção na GCP, as credenciais da própria instância serão utilizadas automaticamente.
 * 
 * @package Axdron\Radianti\Services
 */
class RadiantiGCPStorageService
{

    /**
     * Faz o upload de um arquivo para o bucket do GCP Storage
     * 
     * @param string $caminhoArquivo Caminho local do arquivo a ser enviado
     * @param string $nomeBucket Nome do bucket no GCP
     * @param string|null $enderecoKeyFile Caminho para o arquivo de credenciais JSON
     * @return StorageObject Objeto do arquivo enviado
     */
    static function fazerUpload(String $caminhoArquivo, string $nomeBucket, ?string $enderecoKeyFile = null): StorageObject
    {
        $bucket = self::obterBucket($nomeBucket, $enderecoKeyFile);
        return $bucket->upload(fopen($caminhoArquivo, 'r'));
    }

    /**
     * Exclui um arquivo do bucket do GCP Storage
     * 
     * @param string $nomeArquivo Nome do arquivo (ex: "arquivo.jpg")
     * @param string $nomeBucket Nome do bucket
     * @param string|null $enderecoKeyFile Caminho para o arquivo de credenciais JSON
     * @return bool Retorna true se deletado com sucesso
     */
    static function excluirArquivo(String $nomeArquivo, string $nomeBucket, ?string $enderecoKeyFile = null): bool
    {
        if (empty($nomeArquivo)) {
            return false;
        }

        $bucket = self::obterBucket($nomeBucket, $enderecoKeyFile);
        $object = $bucket->object($nomeArquivo);
        $object->delete();

        return true;
    }

    /**
     * Baixa um arquivo do bucket do GCP Storage para um destino local.
     *
     * @param string $nomeArquivo Nome do objeto no bucket (ex: "pasta/arquivo.zip")
     * @param string $nomeBucket Nome do bucket
     * @param string $destino Caminho absoluto do arquivo de destino local
     * @param string|null $enderecoKeyFile Caminho para o arquivo de credenciais JSON
     * @return bool Retorna true se baixado com sucesso
     */
    public static function baixarArquivoPara(string $nomeArquivo, string $nomeBucket, string $destino, ?string $enderecoKeyFile = null): bool
    {
        $bucket = self::obterBucket($nomeBucket, $enderecoKeyFile);
        $object = $bucket->object($nomeArquivo);
        $object->downloadToFile($destino);
        return is_file($destino) && filesize($destino) > 0;
    }

    /**
     * Inicializa o cliente do GCP Storage
     * @param string|null $enderecoKeyFile Caminho para o arquivo de credenciais JSON. No ambiente local é obrigatório, no ambiente da GCP utilizará as credenciais da própria instância
     * @return StorageClient
     */
    private static function inicializarStorage(?string $enderecoKeyFile = null): StorageClient
    {
        if (!empty($enderecoKeyFile)) {
            return new StorageClient([
                'keyFilePath' => $enderecoKeyFile,
            ]);
        } else {
            return new StorageClient();
        }
    }

    /**
     * Obtém o bucket do GCP Storage
     * @param string $nomeBucket Nome do bucket
     * @param string|null $enderecoKeyFile Caminho para o arquivo de credenciais JSON
     * @return Bucket
     */
    private static function obterBucket(string $nomeBucket, ?string $enderecoKeyFile = null): Bucket
    {
        $storage = self::inicializarStorage($enderecoKeyFile);
        return $storage->bucket($nomeBucket);
    }
}
