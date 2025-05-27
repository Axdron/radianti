# Radianti - README

## Introdução

Esta biblioteca tem como objetivo simplificar objetos e funções frequentemente utilizados no desenvolvimento de softwares utilizando o Adianti Framework.

## Estrutura do Projeto

Recomendamos que você inclua a pasta `lib` em seu projeto para mapear os objetos do Adianti. Essa pasta contém as bibliotecas e componentes necessários para o correto funcionamento do framework.

## Snippets

No projeto há a pasta `snippets` que contém Snippets para as principais classes da biblioteca. Recomendado que faça uma cópia para o VSCode do projeto para maior otimização.

## Variáveis de ambiente cuja declaração é necessária para o funcionamento da biblioteca

- RADIANTI_DB_NAME: Necessário declarar para utilizar as Transactions corretamento. Deve conter o nome do DB principal;
- RADIANTI_VARIAVEL_LOGIN: Necessário declarar para que o serviço de PDF saiba qual usuário informar no rodapé;

## Componentes Principais

Aqui estão alguns dos principais recursos do Radianti:

1. **Datagrids**: Existem recursos para a criação de colunas de Datagrids que são frequentemente utilizadas, como colunas de dinheiro (em R$) e colunas de percentual. As duas classes disponíveis são:

- RadiantiDatagridColunaDinheiro - Formata para R$;
- RadiantiDatagridColunaPercentual - Inclui % no final, desde que seja um número, senão retorna o próprio valor;
- RadiantiDatagridColunaSimNao - Se tiver valor informado, então SIM, senão, NÃO;

2. **Serviços**: Serviços de uso geral:

- RadiantiArquivoTemporario - Cria arquivos temporários na pasta temporária, para não utilizar as pastas de output (importante quando utiliza serviços como o Google App Engine);
- RadiantiArrayService - Funções para manipulação de arrays;
- RadiantiGerenciadorSessoes - Para utilização de sessões armazenadas no BD;
- RadiantiNavegacao - Funções para abrir telas e guias novas;
- RadiantiPlanilhaService - Funções para criar XLSX e CSV;
- RadiantiPDFService - Funções para geração de arquivos PDF a partir de HTMLs;

3. **Componentes de tela**: Atalhos para componentes com propriedades usadas com grande recorrência:

- RadiantiElementoBotaoOpcoes: TRadioGroup convertido em botões;
- RadiantiElementoBotaoSN: RadiantiElementoBotaoOpcoes, só que já com os campos boolenos;
- RadiantiElementoLabelExplicativa: Cria uma label utilizada para explicar o funcionamento de alguma tela, para ser utilizada, principalmente, em relatórios;
- RadiantiElementoBotaoCadastroForm: Cria um botão para ser utilizados em form, agilizando a abertura de outras telas, evitando do usuário ter que trocar de tela. Quando não for em uma TWindow, recomendado utilizar em combinação com uma função da classe RadiantiNavegacao, para abrir em nova aba;
- RadiantiElementoTexto: Cria um TTextDisplay na tela, gerenciando o conteúdo de forma que seja fácil atualizá-lo;

4. **Telas modelo**: Telas prontas para agilizar na criação de outras telas:

- RadiantiRelatorioModelo: Relatório contendo filtros, datagrid de dados, geração de XLSX e PDF;
