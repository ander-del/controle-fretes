# Sistema de Controle de Fretes

Projeto de aplicação web para cadastrar produtos, endereços e
transportadores, registrar fretes com vários produtos e consultar
relatórios por data.

## Funcionalidades

- Cadastro, listagem e edição de produtos.
- Cadastro, listagem e edição de endereços.
- Cadastro, listagem e edição de transportadores.
- Registro de fretes com transportador, origem e destino.
- Inclusão e remoção dinâmica de produtos no formulário.
- Cálculo do peso total da carga.
- Consulta dos fretes e seus detalhes.
- Relatório diário com quantidade de fretes e valor total.
- Download do relatório em CSV.

## Tecnologias

- PHP com PDO.
- MariaDB/MySQL.
- HTML e CSS.
- JavaScript.
- Bootstrap 5.
- Git para versionamento.
- XAMPP para execução local.

## Requisitos

- XAMPP com PHP 8.1 ou superior.
- Extensões PHP pdo_mysql e mbstring habilitadas.
- Navegador atualizado.
- Internet para carregar o Bootstrap pelo CDN.

## Como executar

1. Baixe ou clone o repositório.
2. Coloque a pasta do projeto em C:\xampp\htdocs\controle-fretes.
3. Abra o painel do XAMPP e inicie Apache e MySQL.
4. Acesse http://localhost/phpmyadmin/.
5. Na aba Importar, selecione o arquivo database/banco.sql.
6. Confira as configurações em config/conexao.php.
7. Abra http://localhost/controle-fretes/ no navegador.

A configuração inicial da conexão utiliza localhost, banco
controle_fretes, usuário root e senha vazia, conforme o ambiente
local padrão usado no desenvolvimento.

Se o seu ambiente utilizar outras credenciais, ajuste o arquivo
config/conexao.php.

## Organização do projeto

| Local | Conteúdo |
| --- | --- |
| index.php | Página inicial, menu e seleção das telas |
| assets/ | Estilos CSS e JavaScript |
| config/ | Conexão com o banco e consulta dos relatórios |
| database/banco.sql | Criação do banco e das tabelas |
| paginas/ | Telas de cadastros, fretes e relatórios |
| acoes/ | Salvamento dos dados e download do relatório |

## Como utilizar

1. Cadastre os produtos e seus pesos unitários em quilogramas.
2. Cadastre pelo menos dois endereços.
3. Cadastre um transportador.
4. Em Fretes, selecione data, transportador, origem e destino.
5. Adicione os produtos e informe suas quantidades.
6. Salve o frete e consulte seus detalhes.
7. Em Relatórios, escolha uma data e consulte os fretes do dia.
8. Use o botão de download para obter o relatório CSV.

Origem e destino devem ser diferentes. Cada produto deve aparecer
uma única vez no frete, com quantidade inteira maior que zero.

O peso unitário é registrado junto ao frete para preservar o peso
da carga caso o cadastro do produto seja alterado posteriormente.

## Relatório CSV

O relatório utiliza ponto e vírgula como separador e pode ser aberto
no Excel, LibreOffice Calc ou em um editor de texto.

O arquivo contém ID, data, transportador, origem, destino, valor
dos fretes e o total do dia.

## Banco de dados

O arquivo database/banco.sql cria a estrutura do banco e das tabelas.
Ele não inclui os cadastros e fretes feitos durante os testes.

Depois de uma instalação nova, cadastre os dados pelo próprio sistema.

## Observações

Aplicação desenvolvida para demonstração e execução local.
Os exemplos utilizados nos testes são fictícios.