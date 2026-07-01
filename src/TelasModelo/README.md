# Telas Modelo (TelasModelo)

## Descrição

A pasta `TelasModelo` contém classes abstratas que servem como modelos padronizados para criação de telas específicas. Cada classe fornece uma estrutura base com métodos abstratos que devem ser implementados pelas classes filhas para casos de uso particulares.

## Classes Disponíveis

### 1. RadiantiRelatorioModelo

**Propósito**: Criar telas de relatórios com filtros, datagrid de dados e exportação (XLSX/PDF).

**Características**:

- Formulário de filtros customizável
- DataGrid com colunas dinâmicas
- Exportação para XLSX
- Exportação para PDF
- Paginação automática
- Validação de filtros

**Exemplo de uso**:

```php
<?php
use Axdron\Radianti\TelasModelo\RadiantiRelatorioModelo;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TDate;

class RelatorioVendas extends RadiantiRelatorioModelo
{
    protected static function getTitulo(): string
    {
        return 'Relatório de Vendas';
    }

    protected function criarCamposFiltro(): array
    {
        return [
            'data_inicio' => new TDate('data_inicio'),
            'data_fim' => new TDate('data_fim'),
        ];
    }

    protected function criarColunasDatagrid(): array
    {
        return [
            'id' => ['label' => 'ID', 'width' => '10%'],
            'produto' => ['label' => 'Produto', 'width' => '40%'],
            'valor' => ['label' => 'Valor', 'width' => '20%'],
        ];
    }

    protected function carregarDados(array $filtros): array
    {
        // Lógica para carregar dados conforme filtros
        return [];
    }
}
```

---

### 2. RadiantiListagemModelo

**Propósito**: Criar telas de listagem com filtros, datagrid e operações (CRUD).

**Características**:

- Listagem com filtros
- DataGrid com ações
- Busca em tempo real
- Paginação
- Integração com CRUD

**Nota**: Veja a documentação específica dessa classe para mais detalhes.

---

### 3. RadiantiDashboardModelo

**Propósito**: Criar dashboards com filtros, indicadores visuais e cards informativos.

**Características**:

- Formulário de filtros dinâmicos
- Sistema de cards com indicadores
- Seções organizadas
- Ícones e cores customizáveis
- Métodos auxiliares de formatação
- Suporte a crescimento/decrescimento com cores e ícones

**Exemplo de uso**:

```php
<?php
use Axdron\Radianti\TelasModelo\RadiantiDashboardModelo;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Base\TElement;

class DashboardVendas extends RadiantiDashboardModelo
{
    protected static function getTituloDashboard(): string
    {
        return 'Dashboard de Vendas';
    }

    protected static function getExplicacaoDashboard(): string
    {
        return 'Visão geral das vendas do mês atual';
    }

    protected function criarCamposFiltros(): array
    {
        return [
            ['label' => 'Data Inicial', 'campo' => new TDate('data_inicio')],
            ['label' => 'Data Final', 'campo' => new TDate('data_final')],
        ];
    }

    protected function criarSecoes(): array
    {
        $secoes = [];

        // Seção de indicadores principais
        $secao = $this->criarSecaoDashboard(
            '<i class="fas fa-chart-line"></i> Vendas',
            '#3498db',
            'col-md-12'
        );

        // Container de cards
        $container = $this->criarContainerCards();

        // Card 1: Total de Vendas
        $container->add(
            $this->criarCardIndicador(
                'Total',
                $this->formatarValor(125000.50),
                'Vendas do período',
                'fas fa-dollar-sign',
                'primary',
                'col-md-3'
            )
        );

        // Card 2: Crescimento
        $crescimento = 15.5;
        $container->add(
            $this->criarCardIndicador(
                'Crescimento',
                $this->formatarPercentual($crescimento, true),
                'Vs. período anterior',
                $this->determinarIconeCrescimento($crescimento),
                $this->determinarCorCrescimento($crescimento),
                'col-md-3'
            )
        );

        $this->adicionarCardsNaSecao($secao, $container);
        $secoes[] = $secao;

        return $secoes;
    }

    protected function aplicarValidacoesFiltros(array $param): array
    {
        // Validar e tratar os filtros
        return $this->tratarFiltros($param, [
            'data_inicio' => 'string',
            'data_final' => 'string',
        ]);
    }
}
```

**Métodos Principais**:

#### Criação de Componentes

- `criarSecaoDashboard(string $titulo, string $corBorda, string $classe, ?string $explicacao): TElement`
  - Cria uma seção com título e borda

- `criarCardIndicador(string $titulo, string $valor, string $descricao, string $icone, string $cor, string $colClass, ?TAction $acao): TElement`
  - Cria um card de indicador estilizado

- `criarContainerCards(): TElement`
  - Cria um container row para agrupar cards

- `criarCardErro(string $mensagem, string $tipo): TElement`
  - Cria um card de alerta/erro

- `criarCardInfo(string $mensagem): TElement`
  - Cria um card informativo

#### Formatação

- `formatarValor(?float $valor, bool $incluirSimbolo): string`
  - Formata valor monetário em R$

- `formatarNumero(?int $numero): string`
  - Formata número inteiro com separadores

- `formatarPercentual(float $percentual, bool $incluirSinal): string`
  - Formata percentual com %

#### Utilidades

- `tratarFiltros(array $filtros, array $configuracao): array`
  - Converte tipos de filtros (int, float, string, bool)

- `determinarCorCrescimento(float $valor): string`
  - Retorna cor (success, danger, secondary) baseada no valor

- `determinarIconeCrescimento(float $valor): string`
  - Retorna ícone FontAwesome (seta para cima/baixo)

- `abrir(array $param): void`
  - Abre o dashboard em nova guia do navegador

---

## Padrão de Implementação

Ao criar uma nova tela modelo:

1. **Estenda a classe base**

   ```php
   class MinhaTelaModelo extends RadiantiRelatorioModelo
   ```

2. **Implemente os métodos abstratos**
   - Cada método abstrato deve ter um propósito específico

3. **Documente com PHPDoc**
   - Classes, propriedades e métodos devem ter documentação

4. **Siga PSR-12**
   - Mantenha consistência com o estilo de código do projeto

5. **Crie testes unitários**
   - Na pasta `tests/TelasModelo/` com nome `SuaClasseTest.php`

---

## Boas Práticas

- **Reutilização**: Use as classes modelo para evitar duplicação de código
- **Extensibilidade**: Sobrescreva métodos quando necessário customizar comportamento
- **Validação**: Sempre valide e sanitize os filtros recebidos
- **Documentação**: Mantenha exemplos de uso atualizados
- **Testes**: Crie testes para novas funcionalidades

---

## Contribuição

Para adicionar nova classe modelo:

1. Crie a classe em `src/TelasModelo/`
2. Implemente com PHPDoc completo
3. Crie testes em `tests/TelasModelo/`
4. Atualize este README
5. Atualize o README.md principal
6. Atualize CHANGELOG.md
