# Changelog

## [3.15.7] - 2025-11-20

### Added

- Hook `adicionarFiltrosCarregamento()` adicionado em `RadiantiTraitDetalheDatagrid` para permitir filtros customizados na query de carregamento de itens dependentes. Implementar este método nas classes filhas para adicionar filtros específicos sem necessidade de sobrescrever o método `carregar()` completo.

## [3.15.6] - 2025-11-19

### Added

- Nova interface `RadiantiJanelaPergunta` adicionada em `src/Interfaces/` para criar janelas de pergunta com suporte completo a close action (botão X). Diferente do `TQuestion`, gerencia adequadamente o comportamento ao fechar a janela. Exemplo: criar uma confirmação com ações personalizadas para "Sim" e "Não", ambas acionadas também pelo botão de fechar.
- Testes unitários para `RadiantiJanelaPergunta` em `tests/Interfaces/RadiantiJanelaPerguntaTest.php` com cobertura de instanciação, tipagem de parâmetros, PHPDoc, strict_types e comportamento geral.
- Documentação: README atualizado incluindo seção de Interfaces e exemplo de uso de `RadiantiJanelaPergunta`.

## [3.15.5] - 2025-11-06

### Deprecated

- Método `RadiantiTransaction::executarQueryComTransacao()` foi marcado como deprecated em favor de `RadiantiTransaction::executarConsultaBruta()`. O novo método tem um nome mais descritivo que representa corretamente o comportamento da função (executa queries SQL brutas sem gerenciar transações). O método antigo continuará funcionando como um wrapper para manter compatibilidade com código existente, mas será removido em uma versão futura.

### Added

- Novo método `RadiantiTransaction::executarConsultaBruta()` para executar queries SQL brutas de forma explícita, com melhor clareza do que a função realmente faz.
- Método `getArquivoMenu()` adicionado em `RadiantiRelatorioModelo` para permitir customizar o arquivo de menu por relatório, similar à classe `RadiantiListagemModelo`. Valor padrão: `'menu.xml'`.
- Método `montarConsulta()` adicionado em `RadiantiRelatorioModelo` (não abstrato) para construir a query SQL. Deve ser implementado nas classes filhas e retorna uma string com o SQL. Lança exceção se não for implementado.
- Método `processarRetornoConsulta()` adicionado em `RadiantiRelatorioModelo` (opcional) para processar/transformar os dados retornados da consulta. Por padrão, retorna os dados sem modificações.

### Changed

- Método `executarConsulta()` em `RadiantiRelatorioModelo` agora é concreto (não abstrato) e orquestra a execução através de `montarConsulta()` e `processarRetornoConsulta()`. Simplifica a implementação de relatórios ao executar a transação internamente.

## [3.15.4] - 2025-10-07

### Added

- Documentada a classe `RadiantiTransaction` com PHPDoc, incluindo o método `executarQueryComTransacao()`, detalhando parâmetros, retorno e exceções.
- Documentada a classe `RadiantiSessaoService` com PHPDoc, incluindo o método `buscarInstanciaSingleton()`, detalhando o retorno.

### Fixed

- Corrigido o acesso aos atributos `usuarioLogin` e `usuarioId` na classe `RadiantiSessaoService` para serem acessados como atributos de instância (`$this->usuarioLogin` e `$this->usuarioId`) em vez de atributos estáticos (`self::$usuarioLogin` e `self::$usuarioId`).

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
