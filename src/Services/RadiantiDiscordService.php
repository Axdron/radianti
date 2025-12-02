<?php

namespace Axdron\Radianti\Services;

/**
 * RadiantiDiscordService
 *
 * Serviço para envio de notificações para canais do Discord via webhooks.
 * Fornece métodos para enviar mensagens de texto/JSON, exceptions e arquivos.
 *
 * Observações:
 * - Métodos retornam booleano indicando sucesso (true) ou falha (false).
 * - Webhook deve ser fornecido em cada chamada (parâmetro $canal / $webhook).
 * - Esta classe assume que a extensão cURL está disponível.
 *
 * @package Axdron\Radianti\Services
 */
class RadiantiDiscordService
{
    /**
     * Envia o conteúdo já formatado para o webhook do Discord.
     *
     * @param string $mensagem Conteúdo a ser enviado (já pode conter markdown/code blocks).
     * @param string $webhook URL do webhook do Discord (ex: https://discord.com/api/webhooks/...).
     * @return bool true se a chamada foi executada, false caso ocorra falha durante a execução do cURL.
     * @throws \InvalidArgumentException Se webhook estiver vazio ou não for informado.
     */
    protected static function notificarWebhook(string $mensagem, string $webhook): bool
    {
        if (empty($webhook)) {
            throw new \InvalidArgumentException('Webhook não foi informado. Por favor, forneça uma URL válida do webhook do Discord.');
        }

        $ambiente = getenv('AMBIENTE') ?: 'DEV';
        $conteudo = "[{$ambiente}] {$mensagem}";

        $json_data = json_encode([
            'content' => $conteudo,
            'tts' => false,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $ch = curl_init($webhook);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        try {
            curl_exec($ch);
            return true;
        } catch (\Throwable $th) {
            return false;
        } finally {
            curl_close($ch);
        }
    }

    /**
     * Envia uma mensagem ou um payload JSON detectado automaticamente para um canal Discord.
     *
     * Aceita string, array ou object. Se receber array/object ou string JSON válida, a mensagem
     * será enviada como bloco de código JSON (```json ... ```). Caso contrário, será enviada como texto simples.
     *
     * @param string|array|object $mensagem Mensagem ou payload a ser enviado.
     * @param string $webhook URL do webhook do Discord.
     * @return bool true em caso de sucesso, false em falha.
     * @throws \InvalidArgumentException Se webhook estiver vazio ou não for informado.
     */
    public static function enviarMensagem(string|array|object $mensagem, string $webhook): bool
    {
        if (empty($webhook)) {
            throw new \InvalidArgumentException('Webhook não foi informado. Por favor, forneça uma URL válida do webhook do Discord.');
        }

        $snJson = false;
        $dadosJson = null;

        if (is_array($mensagem) || is_object($mensagem)) {
            $snJson = true;
            $dadosJson = $mensagem;
        } elseif (is_string($mensagem)) {
            // tenta decodificar para verificar se é JSON
            $decoded = json_decode($mensagem, true);
            if (json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded))) {
                $snJson = true;
                $dadosJson = $decoded;
            }
        }

        if ($snJson) {
            $texto = '```json' . PHP_EOL . json_encode($dadosJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL . '```';
        } else {
            $texto = (string)$mensagem;
        }

        $partes = self::segmentarMensagem($texto);
        foreach ($partes as $parte) {
            try {
                self::notificarWebhook($parte, $webhook);
            } catch (\Throwable $th) {
                return false;
            }
        }
        return true;
    }

    /**
     * Envia informações sobre uma exception para o canal especificado.
     *
     * @param \Throwable $exception A exception a ser reportada.
     * @param string|null $webhook URL do webhook do Discord.
     * @param array|null $request Dados opcionais do request (ex: ['class' => '', 'method' => '']).
     * @param bool $incluirStack Se true inclui a stack trace no payload.
     * @return bool true em caso de envio bem sucedido, false caso contrário.
     * @throws \InvalidArgumentException Se webhook estiver vazio ou não for informado.
     */
    public static function enviarException(\Throwable $exception, ?string $webhook = null, ?array $request = null, bool $incluirStack = false): bool
    {
        if (empty($webhook)) {
            throw new \InvalidArgumentException('Webhook não foi informado. Por favor, forneça uma URL válida do webhook do Discord.');
        }

        $dados = new \stdClass();
        $dados->erro = substr($exception->getMessage(), 0, 100);

        $arquivo = $exception->getFile();
        $arquivoNome = basename($arquivo);
        $dados->linha = $arquivoNome . ':' . $exception->getLine();

        if ($request !== null) {
            if (isset($request['class'])) $dados->classe = $request['class'];
            if (isset($request['method'])) $dados->metodo = $request['method'];
        }

        if ($incluirStack) {
            $dados->stack = $exception->getTraceAsString();
        }

        return self::enviarMensagem($dados, $webhook);
    }

    /**
     * Envia um arquivo ao canal especificado via multipart/form-data.
     *
     * @param string $caminhoArquivo Caminho local do arquivo a enviar.
     * @param string $webhook URL do webhook do Discord.
     * @return bool true em caso de sucesso, false caso ocorra erro.
     * @throws \InvalidArgumentException Se webhook estiver vazio ou não for informado.
     */
    public static function enviarArquivo(string $caminhoArquivo, string $webhook): bool
    {
        if (empty($webhook)) {
            throw new \InvalidArgumentException('Webhook não foi informado. Por favor, forneça uma URL válida do webhook do Discord.');
        }

        if (!file_exists($caminhoArquivo) || !is_readable($caminhoArquivo)) {
            return false;
        }

        $ambiente = getenv('AMBIENTE') ?: 'DEV';

        $mensagem = [
            'content' => $ambiente,
            'file' => new \CURLFile($caminhoArquivo),
        ];

        $ch = curl_init($webhook);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $mensagem);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        try {
            curl_exec($ch);
            return true;
        } catch (\Throwable $th) {
            return false;
        } finally {
            curl_close($ch);
        }
    }

    /**
     * Quebra mensagem em partes não maiores que maxLen preservando multibyte.
     *
     * @param string $mensagem Texto a ser segmentado.
     * @param int $maxLen Comprimento máximo por segmento (padrão 1500).
     * @return array Lista de segmentos de string.
     */
    protected static function segmentarMensagem(string $mensagem, int $maxLen = 1500): array
    {
        if (mb_strlen($mensagem) <= $maxLen) {
            return [$mensagem];
        }

        $partes = [];
        $pos = 0;
        $len = mb_strlen($mensagem);
        while ($pos < $len) {
            $partes[] = mb_substr($mensagem, $pos, $maxLen);
            $pos += $maxLen;
        }

        return $partes;
    }
}
