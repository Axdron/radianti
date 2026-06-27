<?php

namespace Axdron\Radianti\Services\RadiantiMailService;

use Adianti\Widget\Base\TScript;
use Axdron\Radianti\Services\RadiantiArquivoTemporario;
use SendGrid;
use SendGrid\Mail\Mail;

abstract class RadiantiEmailService extends Mail
{

    private $destinatarios = [];
    private $attachments = []; // Armazena os anexos adicionados

    /**
     * Verifica se o envio de e-mails está em modo de simulação.
     * Em modo de simulação, os e-mails não são enviados, mas uma página HTML é gerada para visualização.
     * 
     * @return bool Retorna true se estiver em modo de simulação, false caso contrário.
     */
    abstract protected static function verificarSeSimulacao(): bool;

    /**
     * Busca a chave de API do SendGrid para envio de e-mails.
     * 
     * @return string Retorna a chave de API do SendGrid.
     */
    abstract protected function buscarChaveAPI(): string;

    public bool $snDisparaSimulacaoEmTela = true;

    /**
     * RadiantiEmailService constructor.
     * @param string $emailRemetente
     * @param string $nomeRemetente
     * @param string[]|string $destinatarios
     * @param string $assunto
     * @param string $mensagem
     * @param RadiantiEmailAnexo[]|null $anexos
     */
    public function __construct(string $emailRemetente, string $nomeRemetente, array|string $destinatarios, string $assunto, string $mensagem, ?array $anexos = null)
    {
        parent::__construct();
        $this->setFrom($emailRemetente, $nomeRemetente);
        $this->setSubject($assunto);

        if (is_string($destinatarios))
            $destinatarios = explode(';', $destinatarios);

        foreach ($destinatarios as $destinatario) {
            $destinatario = trim($destinatario);

            if (!empty($destinatario)) {
                if (!$this->validarEmail($destinatario))
                    throw new \Exception("E-mail inválido: $destinatario");
                $this->addTo($destinatario);
                $this->destinatarios[] = $destinatario;
            }
        }

        if (!empty($anexos)) {
            foreach ($anexos as $anexo) {
                $this->adicionarAnexo($anexo);
            }
        }

        $this->addContent("text/html", $mensagem);
    }

    public function adicionarAnexo(RadiantiEmailAnexo $arquivo)
    {
        $base64 = base64_encode(file_get_contents($arquivo->caminho));
        $this->addAttachment(
            $base64,
            $arquivo->tipo,
            $arquivo->nome
        );
    }

    public function enviar()
    {
        $sendgrid = new SendGrid($this->buscarChaveAPI());

        if (static::verificarSeSimulacao()) {
            $this->gerarPaginaSimulacaoEmail();
            return true;
        }

        if (empty($sendgrid)) {
            throw new \Exception('Erro ao enviar e-mail! Chave de API do SendGrid não configurada!');
        }

        if (empty($this->destinatarios)) {
            throw new \Exception('Nenhum destinatário informado para enviar e-mail!');
        }

        $response = $sendgrid->send($this);
        $responseBody = json_decode($response->body());

        if ($responseBody->errors ?? false) {
            $erros = [];

            foreach ($responseBody->errors as $erro) {
                $mensagem = $erro->message ?? '';

                if ($mensagem == 'Maximum credits exceeded') {
                    $mensagem = 'Limite diário de e-mails atingido!';
                }
                $erros[] = $mensagem;
            }
            $erros = implode(', ', $erros);
            throw new \Exception($erros);
        } else {
            return true;
        }
    }

    private function gerarPaginaSimulacaoEmail(): void
    {
        // Gerar o conteúdo HTML do e-mail
        $conteudoEmail = "<html><head><title>Simulação de envio de e-mail</title></head><body>";
        $conteudoEmail .= "<h1>Simulação de envio de e-mail</h1>";
        $conteudoEmail .= "<p><strong>Destinatários:</strong> " . implode(', ', $this->destinatarios) . "</p>";
        $conteudoEmail .= "<p><strong>Assunto:</strong> " . htmlspecialchars($this->getGlobalSubject()->getSubject()) . "</p>";
        $conteudoEmail .= "<p><strong>Mensagem:</strong></p>";

        $conteudos = $this->getContents();
        $conteudoEmail .= $conteudos[0]->getValue();

        if (!empty($this->attachments)) {
            $conteudoEmail .= "<p><strong>Anexos:</strong></p><ul>";
            foreach ($this->attachments as $anexo) {
                $conteudoEmail .= "<li>" . htmlspecialchars($anexo['filename']) . "</li>";
            }
            $conteudoEmail .= "</ul>";
        }

        $conteudoEmail .= "</body></html>";

        $arquivoTemporario = RadiantiArquivoTemporario::criar('simulacao_email', 'html', $conteudoEmail);

        if ($this->snDisparaSimulacaoEmTela) {
            TScript::create("var w = window.open(); w.document.write(`{$conteudoEmail}`); w.document.close();");
            return;
        } else {
            $this->enviarSimulacaoParaDiscord($arquivoTemporario);
        }
    }

    /**
     * Método abstrato para enviar a simulação de e-mail para o Discord.
     * Implementações concretas devem definir como a simulação será enviada.
     * 
     * Exemplo de implementação:
     * 
     * protected function enviarSimulacaoParaDiscord(RadiantiArquivoTemporario $arquivoTemporario): void
     * {
     *    DiscordProvider::notificarMensagem('Um e-mail foi enviado em ambiente de homologação pelo usuário ' . SessaoService::buscarLoginUsuario(), DiscordProvider::CANAL_DEBUG);
     *    DiscordProvider::notificarArquivo($arquivoTemporario, DiscordProvider::CANAL_DEBUG);
     * }
     *
     * @param string $arquivoTemporario O arquivo temporário contendo a simulação do e-mail.
     * @return void
     */
    abstract protected function enviarSimulacaoParaDiscord(string $arquivoTemporario): void;

    public static function validarEmail(string $email): bool
    {
        return !!filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function validarEmails(string $emails): bool
    {
        $emails = explode(';', $emails);
        foreach ($emails as $email) {
            if (!self::validarEmail($email))
                return false;
        }
        return true;
    }
}
