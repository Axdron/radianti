# Instruções do projeto Radianti

Este projeto é uma extensão do framework Adianti (PHP MVC) e fornece classes, traits e métodos para simplificar a criação de telas de cadastro, listagem e manipulação de dados.

Regras e boas práticas (resumo objetivo)

- Namespace e organização

  - Todas as classes devem estar em namespaces compatíveis com PSR-4 e os arquivos devem estar organizados conforme o mapeamento do `composer.json`.

- Padronização de nomes (português)

  - Como a biblioteca é destinada a projetos em português, os nomes de classes, métodos e variáveis devem ser em português (ex.: `MeuServico`, `criarRelatorio`).

- Documentação e PHPDocs

  - Toda classe deve conter PHPDoc para classe, propriedades e métodos.
  - Toda nova classe deve constar no `README.md` (descrição curta e exemplo de uso quando aplicável).

- Requisitos mínimos de ambiente

  - Versão mínima suportada do PHP: 8.2 (deve constar em `composer.json` como `"php": ">=8.2"`).
  - Extensões PHP: usar as mesmas exigidas pelo Adianti Framework (manter compatibilidade com as extensões requeridas pelo Adianti utilizado pelo projeto). Liste e valide essas extensões na documentação de ambiente.

- Composer e versionamento

  - O campo `version` em `composer.json` é a fonte da verdade para a versão do pacote. Deve ser atualizado antes de criar uma nova tag no repositório (ou sempre que alterar a versão).
  - O arquivo `lib/VERSION` se refere a versão do Adianti Framework utilizado, não deve ser usado para versionamento do pacote Radianti.
  - A tag criada para o release deve seguir exatamente o padrão/valor definido em `composer.json` (ex.: `3.14.0` ou `v3.14.0`, conforme padrão adotado; preferir consistência em todo o projeto).
  - Seguir SemVer (https://semver.org/) para versionamento e documentar breaking changes no CHANGELOG.

- Nomes de arquivos importantes

  - Padronizar os nomes de documentação no repositório:
    - `README.md` (arquivo principal de documentação do repositório);
    - `CHANGELOG.md` (registro de mudanças por versão).
  - Renomear `readme.md` e `changelog.md` caso existam com maiúsculas/minúsculas diferentes, para manter consistência.

- Changelog e releases

  - Registrar todas as alterações relevantes em `CHANGELOG.md` seguindo SemVer. Destacar mudanças que quebrem compatibilidade e instruções de migração.
  - Processo de release curto (checklist mínimo):
    1. Atualizar `composer.json` -> campo `version`;
    2. Atualizar `CHANGELOG.md` com as alterações;
    3. Commit e push;
    4. Criar tag com o mesmo valor do campo `version`.

- Testes

  - Criar testes automatizados com PHPUnit.

- Arquivo `TODO.md`

  - Manter o arquivo `TODO.md` atualizado com tarefas pendentes, melhorias e sugestões de implementação.
  - Revisar periodicamente para garantir que as tarefas estejam sendo concluídas e documentadas.
  - Quando desenvolver uma ação nova, validar se já não existe uma tarefa correspondente em `TODO.md` para evitar duplicação de esforço.

- Política de contribuição

  - Ainda não há política de contribuição.

- Qualidade de código

  - Adotar PSR-12 como padrão de estilo (ou documentar variação). Incluir configuração de formatter e/ou php-cs-fixer nas instruções.

- Exemplos, snippets e documentação adicional
  - Manter exemplos em `examples/` ou `docs/` quando necessário.
  - Atualizar snippets quando APIs mudarem.

3. Atualizar `composer.json` adicionando `"php": ">=8.2"` no campo `require` e garantindo que `version` esteja co
