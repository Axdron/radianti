# Changelog

## [3.15.0] - 2025-08-20

### Added

- Componente `RadiantiElementoDataHora` adicionado em `src/Componentes` — componente baseado em `TDateTime` com máscara padrão `dd/mm/yyyy hh:ii` e métodos utilitários `definirValorComoHoje()`, `definirValorComoAgora()` e `definirValorComoPrimeiroDiaMes()`.
- Documentação: README atualizado incluindo referência e exemplo breve do `RadiantiElementoDataHora`.

## [3.14.0] - 2025-08-15

### Added

- Novo serviço `RadiantiDiscordService` para envio de notificações ao Discord via webhooks (mensagens, exceptions e arquivos).
- Novo serviço `RadiantiConnectionService` para preparar conexões MySQL com suporte a unix_socket (destinado ao uso por `Adianti\\Database\\TConnection`).
- Novo serviço `RadiantiEngineService` para validações de segurança em `engine.php` (verificação de `max_input_vars`).
- Arquivo de instruções do Copilot (`.github/copilot-instructions.md`) criado para orientar contribuições e uso da biblioteca.
- Arquivo `CHANGELOG.md` criado com os registros iniciais desta versão.
