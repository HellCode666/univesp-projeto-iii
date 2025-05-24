<?php
// Configurações do Banco de Dados
define('DB_SERVIDOR', 'sql111.infinityfree.com');       // Endereço do servidor MySQL
define('DB_USUARIO', 'if0_39070914');            // Seu usuário do MySQL (padrão do XAMPP é root)
define('DB_SENHA', 'vELm0RQqCcxfO');                  // Sua senha do MySQL (padrão do XAMPP é vazia)
define('DB_NOME_BANCO', 'if0_39070914_univesp'); // O nome do banco de dados que você criou

// Criar a conexão
$conexao = new mysqli(DB_SERVIDOR, DB_USUARIO, DB_SENHA, DB_NOME_BANCO);

// Checar a conexão
if ($conexao->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conexao->connect_error);
}

if (!$conexao->set_charset("utf8")) {
}
?>