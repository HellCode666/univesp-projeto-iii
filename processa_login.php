<?php
date_default_timezone_set('America/Sao_Paulo');
session_start();
require_once 'db_conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $senha_digitada = trim($_POST['senha']);

    if (empty($email) || empty($senha_digitada)) {
        $_SESSION['login_error_message'] = "Por favor, preencha o email e a senha.";
        header("Location: login.php");
        exit;
    }

    $sql = "SELECT id, nome_completo, email, senha, cargo FROM funcionarios WHERE email = ? LIMIT 1";
    
    if ($stmt = $conexao->prepare($sql)) {
        $stmt->bind_param("s", $email);

        if ($stmt->execute()) {
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id_funcionario, $nome_completo, $email_db, $hash_senha_db, $cargo_db);
                
                if ($stmt->fetch()) {
                    if (password_verify($senha_digitada, $hash_senha_db)) {
                        session_regenerate_id(true);

                        $_SESSION['loggedin'] = true;
                        $_SESSION['funcionario_id'] = $id_funcionario;
                        $_SESSION['funcionario_nome'] = $nome_completo;
                        $_SESSION['funcionario_email'] = $email_db;
                        $_SESSION['funcionario_cargo'] = $cargo_db; // Cargo armazenado na sessão

                        header("Location: dashboard.php");
                        exit;
                    } else {
                        $_SESSION['login_error_message'] = "Email ou senha inválidos. Tente novamente.";
                    }
                }
            } else {
                $_SESSION['login_error_message'] = "Email ou senha inválidos. Tente novamente.";
            }
        } else {
            $_SESSION['login_error_message'] = "Ocorreu um erro no servidor. Tente mais tarde. [DB Execute Error]";
        }
        $stmt->close();
    } else {
        $_SESSION['login_error_message'] = "Ocorreu um erro no servidor. Tente mais tarde. [DB Prepare Error]";
    }
    
    $conexao->close();
    header("Location: login.php");
    exit;

} else {
    header("Location: login.php");
    exit;
}
?>