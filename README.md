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
- RADIANTI_SN_MOSTRA_EXPORTACAO_LISTAGEM: Declaração opcional para definir se mostra os botões de exportação nas listagens. Caso não declarado, o valor default é false, sendo necessário configurar em cada listagem;

## Componentes Principais

Aqui estão alguns dos principais recursos do Radianti:

1. **Datagrids**: Existem recursos para a criação de colunas de Datagrids que são frequentemente utilizadas, como colunas de dinheiro (em R$) e colunas de percentual. As duas classes disponíveis são:

- RadiantiDatagridColunaDinheiro - Formata para R$;
- RadiantiDatagridColunaPercentual - Inclui % no final, desde que seja um número, senão retorna o próprio valor;
- RadiantiDatagridColunaSimNao - Se tiver valor informado, então SIM, senão, NÃO;

2. **Serviços**: Serviços de uso geral:

- RadiantiArquivoTemporario - Cria arquivos temporários na pasta temporária, para não utilizar as pastas de output (importante quando utiliza serviços como o Google App Engine);
- RadiantiArrayService - Funções para manipulação de arrays;
- RadiantiConnectionService - Prepara conexões MySQL com suporte a unix_socket (para uso por Adianti\\Database\\TConnection);
- RadiantiDiscordService - Serviço para envio de notificações ao Discord via webhooks (mensagens, exceptions e arquivos);
- RadiantiEngineService - Validações de segurança para engine.php (verifica total de variáveis frente a max_input_vars);
- RadiantiGerenciadorSessoes - Para utilização de sessões armazenadas no BD;
- RadiantiMascaras - Funções para aplicação de máscaras dinâmicas;
- RadiantiNavegacao - Funções para abrir telas e guias novas;
- RadiantiPDFService - Funções para geração de arquivos PDF a partir de HTMLs;
- RadiantiPlanilhaService - Funções para criar XLSX e CSV;
- RadiantiQuestionService - Funções para TQuestions próprios, como operações de confirmação ou de input de valores;
- RadiantiSessaoService - Classe abstrata que cria um singleton para gerenciar as variáveis de sessão, evitando consultas desnecessárias ao BD;
- RadiantiValidacoes - Funções para validações específicas, como CPF e CNPJ em um mesmo campo;

3. **Componentes de tela**: Atalhos para componentes com propriedades usadas com grande recorrência:

- RadiantiElementoBotaoCadastroForm: Cria um botão para ser utilizados em form, agilizando a abertura de outras telas, evitando do usuário ter que trocar de tela. Quando não for em uma TWindow, recomendado utilizar em combinação com uma função da classe RadiantiNavegacao, para abrir em nova aba;
- RadiantiElementoBotaoOpcoes: TRadioGroup convertido em botões;
- RadiantiElementoBotaoSN: RadiantiElementoBotaoOpcoes, só que já com os campos boolenos;
- RadiantiElementoCPFCNPJ: TEntry com máscara dinâmica de CPF/CNPJ ativa;
- RadiantiElementoDataHora: Componente baseado em TDateTime com máscara pronta (dd/mm/yyyy hh:ii) e métodos utilitários `definirValorComoHoje()` e `definirValorComoPrimeiroDiaMes()` para facilitar preenchimentos comuns;
- RadiantiElementoDinheiro: Cria um TNumeric com formatação pronta para trabalhar com valores monetários (= separador de milhar e de decimais com 2 casas);
- RadiantiElementoLabelExplicativa: Cria uma label utilizada para explicar o funcionamento de alguma tela, para ser utilizada, principalmente, em relatórios;
- RadiantiElementoNumeroInteiro: Cria um TNumeric com formatação pronta para trabalhar com números inteiros (= separador de milhar);
- RadiantiElementoTexto: Cria um TTextDisplay na tela, gerenciando o conteúdo de forma que seja fácil atualizá-lo;

4. **Interfaces**: Interfaces e janelas para interação com o usuário:

- RadiantiJanelaPergunta: Cria uma janela de pergunta com suporte completo a close action (botão X). Diferente do `TQuestion`, gerencia adequadamente o comportamento ao fechar a janela.

5. **Telas modelo**: Telas prontas para agilizar na criação de outras telas:

- RadiantiRelatorioModelo: Relatório contendo filtros, datagrid de dados, geração de XLSX e PDF;
