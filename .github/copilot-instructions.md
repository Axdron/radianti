Este projeto é uma extensão do framework Adianti, que é um framework PHP MVC para desenvolvimento de aplicações web.

Ele apresenta classes, traits e métodos que facilitam a criação de telas de cadastro, listagem e manipulação de dados, além de fornecer funcionalidades para navegação entre telas, abertura de transações, criação de componentes de formulários, dentre diversas outras funcionalidades visando estender a capacidade do Adianti Framework.

É importante que toda classe se encontre em um namespace que siga o padrão PSR-4, e que os arquivos estejam organizados de acordo com a estrutura de diretórios do projeto, uma vez que se trata de uma biblioteca que será utilizada por outros projetos.

Pelo fato da biblioteca estar sendo utilizada somente em projetos em português, é importante que os nomes de classes, métodos e variáveis sejam em português, mesmo que não siga o padrão do Adianti Framework, que é em inglês.

Por ser um projeto em PHP, o PHPDocs deve ser utilizado para documentar as classes, métodos e propriedades.

Toda classe deve estar descrita no arquivo `README.md` do projeto, com uma breve descrição de sua funcionalidade e exemplos de uso, se necessário.

Toda nova funcionalidade deve avançar o versionamento do projeto, seguindo o padrão SemVer (https://semver.org/) e criar uma nova tag no repositório.

É interessante que seja registrado um changelog no arquivo `CHANGELOG.md` do projeto, com as alterações realizadas em cada versão, para facilitar o acompanhamento das mudanças e a manutenção do projeto. Destaque alterações que possam quebrar a compatibilidade com versões anteriores e como proceder para atualizar o código.
