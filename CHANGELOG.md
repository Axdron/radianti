# Changelog

## [3.15.4] - 2025-10-07

### Added

- Documentada a classe `RadiantiTransaction` com PHPDoc, incluindo o método `executarQueryComTransacao()`, detalhando parâmetros, retorno e exceções.

## [3.15.3] - 2025-09-12

### Fixed

- Corrigido nome da classe reimplementada em `src/TelasModelo/RadiantiListagemModelo.php` de `RTDatagrid` para `RTDataGrid` para resolver erro de classe não encontrada durante exportação/print da datagrid.

### Added

- Criado o método estático `clicarNoBotaoBuscarEstaticamente()` em `RadiantiListagemModelo` para permitir clicar no botão de busca da listagem via código, útil para cenários onde se deseja disparar a ação de busca programaticamente, como atualizar a listagem após uma ação da datagrid, preservando os filtros.
- Possibilidade de passar parâmetros iniciais para o formulário de busca em `RadiantiRelatorioModelo::abrir($param)`, permitindo pré-preencher filtros ao abrir o relatório.

## [3.15.1] - 2025-09-01

### Added

- Service `RadiantiNavegacao` atualizado para informar o id além da key quando necessário.

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
