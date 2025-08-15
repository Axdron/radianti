Relatório modelo:

- paginar
- otimizar declaração dos campos de busca, para que aceite um objeto, assim fica mais fácil de saber os campos que devem ser declarados

Geral

- É possível atualizar a versão antes de criar uma tag, automaticamente?
- Criar testes automatizados

- CI/CD recomendado

  - Adicionar workflow GitHub Actions que execute pelo menos:
    - Instalação das dependências via Composer;
    - Verificação de estilo (php-cs-fixer ou PHP CodeSniffer);
    - Static analysis (PHPStan ou Psalm);
    - Execução dos testes (PHPUnit).
  - Tornar checks obrigatórios antes do merge.
