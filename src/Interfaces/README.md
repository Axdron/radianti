# Interfaces

## Propósito

Esta pasta contém elementos de interface que não necessariamente compõem uma tela completa, mas que são apresentados ao usuário como componentes de interação em contextos específicos. Exemplos incluem:

- **Diálogos e janelas de confirmação**: Para solicitar confirmações de ações do usuário
- **Janelas de pergunta**: Para fazer perguntas com múltiplas opções de resposta
- **Janelas modais**: Para apresentar informações ou coletar dados sem sair do contexto atual
- **Componentes de entrada de dados em janelas**: Pequenos formulários em contextos flutuantes

## Classes Disponíveis

### RadiantiJanelaPergunta

Cria uma janela de pergunta com suporte completo a close action (botão X). Diferente do `TQuestion` padrão do Adianti, gerencia adequadamente o comportamento ao fechar a janela, disparando a ação de "Não" quando o usuário clica no botão de fechar (X).

**Uso:**

```php
use Adianti\Control\TAction;
use Axdron\Radianti\Interfaces\RadiantiJanelaPergunta;

$action_sim = new TAction(['MinhaClasse', 'metodoSim']);
$action_nao = new TAction(['MinhaClasse', 'metodoNao']);

new RadiantiJanelaPergunta(
    'Deseja realmente deletar este registro?',
    $action_sim,
    $action_nao,
    'Confirmação de Exclusão',
    'Sim, deletar',
    'Não, cancelar'
);
```

**Parâmetros:**

- `$message` (string): Mensagem da pergunta a ser exibida
- `$action_yes` (TAction): Ação executada ao clicar em "Sim" ou no botão principal
- `$action_no` (TAction): Ação executada ao clicar em "Não" ou ao fechar a janela (X)
- `$title_msg` (string, opcional): Título da janela
- `$label_yes` (string, opcional): Rótulo do botão "Sim"
- `$label_no` (string, opcional): Rótulo do botão "Não"

## Orientações para Contribuição

Ao adicionar novas interfaces para esta pasta, considere:

1. **Nome descritivo**: Use nomes que descrevam claramente o tipo de interface (ex.: `RadiantiJanelaConfirmacao`, `RadiantiDialogoEntrada`)
2. **Documentação**: Inclua PHPDoc completo e atualizar este README com exemplos de uso
3. **Testes**: Crie testes unitários em `tests/Interfaces/` para validar o comportamento
4. **Padrão do projeto**: Siga o padrão de nomenclatura em português e as diretrizes definidas no `copilot-instructions.md`
