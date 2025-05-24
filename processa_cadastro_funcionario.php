<?php
session_start();
require_once 'db_conexao.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['funcionario_cargo']) || $_SESSION['funcionario_cargo'] !== 'admin') {
    $_SESSION['form_message_type'] = "danger";
    $_SESSION['form_message'] = "Acesso negado.";
    header("Location: dashboard.php");
    exit;
}
$id_funcionario_logado = $_SESSION['funcionario_id'];


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $funcionario_id_edicao = isset($_POST['funcionario_id']) ? (int)$_POST['funcionario_id'] : null;
    $modo_edicao = ($funcionario_id_edicao !== null);

    $nome_completo = trim($_POST['nome_completo']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']); // Será vazia se não for para alterar em modo de edição
    $confirma_senha = trim($_POST['confirma_senha']);
    $cargo = trim($_POST['cargo']);

    $erros = [];
    if (empty($nome_completo)) $erros[] = "Nome completo é obrigatório.";
    if (empty($email)) $erros[] = "Email é obrigatório.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = "Formato de email inválido.";
    if (!in_array($cargo, ['admin', 'atendente'])) $erros[] = "Cargo inválido.";

    // Validação de senha
    if (!$modo_edicao) { // Se estiver criando um novo funcionário
        if (empty($senha)) $erros[] = "Senha é obrigatória.";
        elseif (strlen($senha) < 6) $erros[] = "A senha deve ter no mínimo 6 caracteres.";
        if ($senha !== $confirma_senha) $erros[] = "As senhas não coincidem.";
    } else { // Se estiver editando
        if (!empty($senha)) {
            if (strlen($senha) < 6) $erros[] = "A nova senha deve ter no mínimo 6 caracteres.";
            if ($senha !== $confirma_senha) $erros[] = "As novas senhas não coincidem.";
        }
    }

    if (empty($erros) && !empty($email)) {
        $sql_check_email = "SELECT id FROM funcionarios WHERE email = ?";
        $params_email_check = [$email];
        if ($modo_edicao) {
            $sql_check_email .= " AND id != ?";
            $params_email_check[] = $funcionario_id_edicao;
        }
        $stmt_check_email = $conexao->prepare($sql_check_email);
        $types_email_check = $modo_edicao ? "si" : "s";
        $stmt_check_email->bind_param($types_email_check, ...$params_email_check);
        $stmt_check_email->execute();
        $stmt_check_email->store_result();
        if ($stmt_check_email->num_rows > 0) {
            $erros[] = "Este email já está cadastrado para outro funcionário.";
        }
        $stmt_check_email->close();
    }
    
    if ($modo_edicao && $cargo === 'atendente' && $funcionario_id_edicao === $id_funcionario_logado) {
        $sql_count_admin = "SELECT COUNT(*) as total_admins FROM funcionarios WHERE cargo = 'admin'";
        $res_count_admin = $conexao->query($sql_count_admin);
        $count_admins = $res_count_admin->fetch_assoc()['total_admins'];
        if ($count_admins <= 1) {
            // Verifica se o usuário atual é o que está sendo editado e se o cargo antigo era admin
            $sql_get_old_cargo = "SELECT cargo FROM funcionarios WHERE id = ?";
            $stmt_old_cargo = $conexao->prepare($sql_get_old_cargo);
            $stmt_old_cargo->bind_param("i", $funcionario_id_edicao);
            $stmt_old_cargo->execute();
            $old_cargo_res = $stmt_old_cargo->get_result()->fetch_assoc();
            $stmt_old_cargo->close();
            if ($old_cargo_res && $old_cargo_res['cargo'] === 'admin') {
                 $erros[] = "Não é possível rebaixar o último administrador do sistema.";
            }
        }
    }


    if (!empty($erros)) {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = implode("<br>", $erros);
        header("Location: cadastrar_funcionario.php" . ($modo_edicao ? "?action=editar&id=".$funcionario_id_edicao : ""));
        exit;
    }

    if ($modo_edicao) {
        if (!empty($senha)) {
            $senha_hashed = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "UPDATE funcionarios SET nome_completo = ?, email = ?, senha = ?, cargo = ? WHERE id = ?";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("ssssi", $nome_completo, $email, $senha_hashed, $cargo, $funcionario_id_edicao);
        } else { // Não atualiza a senha
            $sql = "UPDATE funcionarios SET nome_completo = ?, email = ?, cargo = ? WHERE id = ?";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("sssi", $nome_completo, $email, $cargo, $funcionario_id_edicao);
        }
        $mensagem_acao = "atualizado";
    } else {
        $senha_hashed = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "INSERT INTO funcionarios (nome_completo, email, senha, cargo) VALUES (?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("ssss", $nome_completo, $email, $senha_hashed, $cargo);
        $mensagem_acao = "cadastrado";
    }

    if ($stmt) {
        if ($stmt->execute()) {
            $_SESSION['form_message_type'] = "success";
            $_SESSION['form_message'] = "Funcionário " . $mensagem_acao . " com sucesso!";
        } else {
            $_SESSION['form_message_type'] = "danger";
            $_SESSION['form_message'] = "Erro ao " . $mensagem_acao . " funcionário: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "Erro ao preparar a consulta: " . $conexao->error;
    }
    $conexao->close();
    header("Location: " . ($modo_edicao && $_SESSION['form_message_type'] == "danger" ? "cadastrar_funcionario.php?action=editar&id=".$funcionario_id_edicao : "listar_funcionarios.php"));
    exit;

} else {
    header("Location: dashboard.php");
    exit;
}
?>