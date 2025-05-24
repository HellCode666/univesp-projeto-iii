Projeto Univesp III

Site disponível em: https://projetounivesp.rf.gd/

## Executando Localmente

Para executar este projeto em seu ambiente local, você precisará de um servidor web com suporte a PHP e um banco de dados MySQL. Abaixo estão algumas opções populares e um guia geral:

### Opções de Ambiente de Desenvolvimento Local:

*   **WAMP (Windows):** Se você está no Windows, o WAMP Server (www.wampserver.com) é uma excelente escolha. Ele instala o Apache, MySQL e PHP de forma integrada.
*   **XAMPP (Multiplataforma):** O XAMPP (www.apachefriends.org) é uma alternativa popular que funciona no Windows, macOS e Linux. Ele também inclui Apache, MariaDB (compatível com MySQL) e PHP.
*   **MAMP (macOS):** Para usuários de macOS, o MAMP (www.mamp.info) oferece uma configuração similar.
*   **Docker:** Se você tem familiaridade com Docker, pode criar um ambiente containerizado com PHP e MySQL.

### Passos Gerais para Configuração:

1.  **Instale um Servidor Web:**
    *   Faça o download e instale uma das opções mencionadas acima (WAMP, XAMPP, MAMP) ou configure seu ambiente Docker.
2.  **Inicie os Serviços:**
    *   Após a instalação, inicie os serviços Apache e MySQL através do painel de controle do software escolhido (WAMP, XAMPP, MAMP) ou via comandos Docker.
3.  **Copie os Arquivos do Projeto:**
    *   Clone ou baixe este repositório.
    *   Copie todos os arquivos do projeto para o diretório raiz do seu servidor web.
        *   Para WAMP, geralmente é `c:\wamp64\www\` ou `c:\wamp\www\`.
        *   Para XAMPP, geralmente é `c:\xampp\htdocs\`.
        *   Para MAMP, geralmente é `/Applications/MAMP/htdocs/`.
4.  **Configure o Banco de Dados (se aplicável):**
    *   Se o projeto utiliza um banco de dados, você precisará importar o arquivo SQL (geralmente com extensão `.sql`) para o seu MySQL/MariaDB. Isso pode ser feito através de ferramentas como phpMyAdmin (geralmente acessível via `http://localhost/phpmyadmin`).
    *   Certifique-se de que as credenciais de conexão com o banco de dados no código do projeto (geralmente em um arquivo de configuração PHP) correspondem às configurações do seu ambiente local.
5.  **Acesse o Projeto:**
    *   Abra seu navegador e acesse `http://localhost/nome_da_pasta_do_projeto/` (substitua `nome_da_pasta_do_projeto` pelo nome da pasta onde você copiou os arquivos).