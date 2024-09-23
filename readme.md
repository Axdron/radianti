# Radianti - README

## Introdução

Esta biblioteca tem como objetivo simplificar objetos e funções frequentemente utilizados no desenvolvimento de softwares utilizando o Adianti Framework.

## Estrutura do Projeto

Recomendamos que você inclua a pasta `lib` em seu projeto para mapear os objetos do Adianti. Essa pasta contém as bibliotecas e componentes necessários para o correto funcionamento do framework.

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
- RadiantiNavegacao - Funções para abrir telas e guias novas;
- RadiantiPlanilhaService - Funções para criar XLSX e CSV;
- RadiantiArrayService - Funções para manipulação de arrays;
- RadiantiPDFService - Funções para geração de arquivos PDF a partir de HTMLs;

3. **Componentes de tela**: Atalhos para componentes com propriedades usadas com grande recorrência:

- RadiantiElementoBotaoOpcoes: TRadioGroup convertido em botões;
- RadiantiElementoBotaoSN: RadiantiElementoBotaoOpcoes, só que já com os campos boolenos;

4. **Telas modelo**: Telas prontas para agilizar na criação de outras telas:

- RadiantiRelatorioModelo: Relatório contendo filtros, datagrid de dados, geração de XLSX e PDF;
