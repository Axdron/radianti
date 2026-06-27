# RadiantiMailService

Serviço para envio de e-mails integrado com SendGrid, com suporte a simulação em ambiente de homologação.

## Descrição

O `RadiantiMailService` é uma classe abstrata que estende `SendGrid\Mail\Mail` e simplifica o envio de e-mails através da plataforma SendGrid. Oferece recursos como:

- **Envio de e-mails**: Suporte para múltiplos destinatários e anexos
- **Validação de e-mails**: Métodos para validar endereços de e-mail
- **Simulação em tela**: Em ambiente de homologação, exibe uma pré-visualização do e-mail em uma nova janela
- **Envio para Discord**: Alternativa para enviar a simulação para um canal Discord via webhook
- **Anexos**: Suporte para adicionar anexos aos e-mails

## Requisitos

- PHP >= 8.2
- SendGrid API key
- Biblioteca `sendgrid/sendgrid` (>=8.1)

## Instalação

A biblioteca já está incluída no `composer.json`. Se precisar reinstalar:

```bash
composer require sendgrid/sendgrid:^8.1
```

## Uso

### Criar uma implementação concreta

Como `RadiantiEmailService` é uma classe abstrata, você precisa criar uma implementação concreta:

```php
<?php

namespace Seu\Namespace;

use Axdron\Radianti\Services\RadiantiMailService\RadiantiEmailService;
use Axdron\Radianti\Services\RadiantiMailService\RadiantiEmailAnexo;

class MeuEmailService extends RadiantiEmailService
{
    protected static function verificarSeSimulacao(): bool
    {
        // Retorna true em ambiente de homologação/desenvolvimento
        return getenv('AMBIENTE') !== 'PROD';
    }

    protected function buscarChaveAPI(): string
    {
        // Buscar a chave do SendGrid (ex: variável de ambiente)
        return getenv('SENDGRID_API_KEY');
    }

    protected function enviarSimulacaoParaDiscord(string $arquivoTemporario): void
    {
        // Implementar envio para Discord
        // Exemplo:
        // DiscordProvider::notificarMensagem('E-mail simulado...', DiscordProvider::CANAL_DEBUG);
        // DiscordProvider::notificarArquivo($arquivoTemporario, DiscordProvider::CANAL_DEBUG);
    }
}
```

### Enviar um e-mail simples

```php
$email = new MeuEmailService(
    emailRemetente: 'noreply@empresa.com',
    nomeRemetente: 'Minha Empresa',
    destinatarios: 'cliente@example.com',
    assunto: 'Bem-vindo!',
    mensagem: '<h1>Olá!</h1><p>Bem-vindo ao nosso sistema.</p>'
);

$email->enviar();
```

### Enviar para múltiplos destinatários

```php
$email = new MeuEmailService(
    emailRemetente: 'noreply@empresa.com',
    nomeRemetente: 'Minha Empresa',
    destinatarios: 'usuario1@example.com; usuario2@example.com; usuario3@example.com',
    assunto: 'Notificação',
    mensagem: '<p>Conteúdo da mensagem</p>'
);

$email->enviar();
```

### Enviar com anexos

```php
// Criar anexos
$anexo1 = new RadiantiEmailAnexo();
$anexo1->nome = 'relatorio.pdf';
$anexo1->caminho = '/tmp/relatorio_123.pdf';
$anexo1->tipo = 'application/pdf';

$anexo2 = new RadiantiEmailAnexo();
$anexo2->nome = 'dados.xlsx';
$anexo2->caminho = '/tmp/dados_123.xlsx';
$anexo2->tipo = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

// Enviar e-mail com anexos
$email = new MeuEmailService(
    emailRemetente: 'noreply@empresa.com',
    nomeRemetente: 'Minha Empresa',
    destinatarios: 'cliente@example.com',
    assunto: 'Seus documentos',
    mensagem: '<p>Segue em anexo seus documentos solicitados.</p>',
    anexos: [$anexo1, $anexo2]
);

$email->enviar();
```

### Adicionar anexos após criar o e-mail

```php
$email = new MeuEmailService(
    emailRemetente: 'noreply@empresa.com',
    nomeRemetente: 'Minha Empresa',
    destinatarios: 'cliente@example.com',
    assunto: 'Documentos',
    mensagem: '<p>Conteúdo</p>'
);

// Adicionar anexo dinamicamente
$anexo = new RadiantiEmailAnexo();
$anexo->nome = 'anexo.pdf';
$anexo->caminho = '/path/to/anexo.pdf';
$anexo->tipo = 'application/pdf';

$email->adicionarAnexo($anexo);
$email->enviar();
```

### Validar e-mails

```php
// Validar um único e-mail
if (MeuEmailService::validarEmail('usuario@example.com')) {
    echo 'E-mail válido';
}

// Validar múltiplos e-mails (separados por ponto-e-vírgula)
if (MeuEmailService::validarEmails('user1@example.com; user2@example.com')) {
    echo 'Todos os e-mails são válidos';
}
```

### Modo de simulação em tela

Por padrão, em modo de simulação, o e-mail é exibido em uma nova janela do navegador:

```php
class MeuEmailService extends RadiantiEmailService
{
    // ...

    protected function enviarSimulacaoParaDiscord(string $arquivoTemporario): void
    {
        // Não faz nada (padrão)
    }
}

// Em homologação, a simulação será exibida em uma nova janela
$email = new MeuEmailService(...);
$email->enviar(); // Abre uma nova aba com a pré-visualização
```

### Modo de simulação com Discord

Se preferir enviar a simulação para Discord em vez de exibir em tela:

```php
class MeuEmailService extends RadiantiEmailService
{
    protected function enviarSimulacaoParaDiscord(string $arquivoTemporario): void
    {
        DiscordProvider::notificarMensagem(
            'Um e-mail foi enviado em ambiente de homologação pelo usuário ' . SessaoService::buscarLoginUsuario(),
            DiscordProvider::CANAL_DEBUG
        );
        DiscordProvider::notificarArquivo($arquivoTemporario, DiscordProvider::CANAL_DEBUG);
    }
}

// Em homologação, a simulação será enviada para Discord
$email = new MeuEmailService(...);
$email->snDisparaSimulacaoEmTela = false; // Desativar simulação em tela
$email->enviar();
```

## Variáveis de Ambiente

Recomenda-se configurar as seguintes variáveis de ambiente:

```bash
# SendGrid API Key (obrigatório)
SENDGRID_API_KEY=SG.xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

# Ambiente (opcional, para distinguir produção de homologação)
AMBIENTE=DEV # ou PROD, HOM, etc.
```

## Tratamento de erros

O serviço lança exceções em caso de:

- E-mail inválido: `Exception("E-mail inválido: $email")`
- Sem destinatários: `Exception("Nenhum destinatário informado para enviar e-mail!")`
- Chave de API não configurada: `Exception("Erro ao enviar e-mail! Chave de API do SendGrid não configurada!")`
- Limite de créditos do SendGrid excedido: `Exception("Limite diário de e-mails atingido!")`
- Outros erros do SendGrid: Exceção com a mensagem de erro da API

## Classes

### RadiantiEmailService

Classe abstrata para envio de e-mails via SendGrid.

**Métodos principais:**

- `__construct(string $emailRemetente, string $nomeRemetente, array|string $destinatarios, string $assunto, string $mensagem, ?array $anexos = null)`
- `adicionarAnexo(RadiantiEmailAnexo $arquivo): void`
- `enviar(): bool`
- `validarEmail(string $email): bool` (estático)
- `validarEmails(string $emails): bool` (estático)

**Propriedades:**

- `snDisparaSimulacaoEmTela: bool` - Controla se a simulação é exibida em tela (padrão: `true`)

### RadiantiEmailAnexo

Classe que representa um anexo de e-mail.

**Propriedades:**

- `nome: string` - Nome do arquivo como aparecerá no e-mail
- `caminho: string` - Caminho absoluto do arquivo no servidor
- `tipo: string` - Tipo MIME do arquivo

## Notas

- O serviço usa `base64_encode()` para codificar os anexos, suportando qualquer tipo de arquivo
- Em modo de simulação, o e-mail **não é enviado** realmente, apenas uma pré-visualização é gerada
- Múltiplos destinatários podem ser separados por ponto-e-vírgula (`;`) ou passados como array
- O conteúdo do e-mail deve ser em HTML
