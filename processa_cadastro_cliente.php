<?php
session_start();
require_once 'db_conexao.php'; // Conexão com o banco

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['login_error_message'] = "Você precisa estar logado para realizar esta ação.";
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cliente_id_edicao = isset($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null;
    $modo_edicao = ($cliente_id_edicao !== null);

    $nome_completo = trim($_POST['nome_completo']);
    $telefone_celular = trim($_POST['telefone_celular']);
    $cpf = !empty(trim($_POST['cpf'])) ? trim($_POST['cpf']) : NULL;
    $telefone_fixo = !empty(trim($_POST['telefone_fixo'])) ? trim($_POST['telefone_fixo']) : NULL;
    $email = !empty(trim($_POST['email'])) ? trim($_POST['email']) : NULL;
    $data_nascimento = !empty($_POST['data_nascimento']) ? $_POST['data_nascimento'] : NULL;
    $endereco_cep = !empty(trim($_POST['endereco_cep'])) ? trim($_POST['endereco_cep']) : NULL;
    $endereco_rua = !empty(trim($_POST['endereco_rua'])) ? trim($_POST['endereco_rua']) : NULL;
    $endereco_numero = !empty(trim($_POST['endereco_numero'])) ? trim($_POST['endereco_numero']) : NULL;
    $endereco_complemento = !empty(trim($_POST['endereco_complemento'])) ? trim($_POST['endereco_complemento']) : NULL;
    $endereco_bairro = !empty(trim($_POST['endereco_bairro'])) ? trim($_POST['endereco_bairro']) : NULL;
    $endereco_cidade = !empty(trim($_POST['endereco_cidade'])) ? trim($_POST['endereco_cidade']) : NULL;
    $endereco_estado = !empty(trim($_POST['endereco_estado'])) ? trim($_POST['endereco_estado']) : NULL;
    $observacoes = !empty(trim($_POST['observacoes'])) ? trim($_POST['observacoes']) : NULL;
    $id_funcionario_operacao = $_SESSION['funcionario_id']; 

    // Validações
    if (empty($nome_completo) || empty($telefone_celular)) {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "Nome completo e telefone celular são obrigatórios.";
        header("Location: cadastrar_cliente.php" . ($modo_edicao ? "?action=editar&id=".$cliente_id_edicao : ""));
        exit;
    }
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "Formato de email inválido.";
         header("Location: cadastrar_cliente.php" . ($modo_edicao ? "?action=editar&id=".$cliente_id_edicao : ""));
        exit;
    }

    if ($cpf) {
        $sql_check_cpf = "SELECT id FROM clientes WHERE cpf = ?";
        $params_cpf = [$cpf];
        if ($modo_edicao) {
            $sql_check_cpf .= " AND id != ?";
            $params_cpf[] = $cliente_id_edicao;
        }
        $stmt_check_cpf = $conexao->prepare($sql_check_cpf);
        $types_cpf = $modo_edicao ? "si" : "s";
        $stmt_check_cpf->bind_param($types_cpf, ...$params_cpf);
        $stmt_check_cpf->execute();
        $stmt_check_cpf->store_result();
        if ($stmt_check_cpf->num_rows > 0) {
            $_SESSION['form_message_type'] = "danger";
            $_SESSION['form_message'] = "Este CPF já está cadastrado para outro cliente.";
            $stmt_check_cpf->close();
            header("Location: cadastrar_cliente.php" . ($modo_edicao ? "?action=editar&id=".$cliente_id_edicao : ""));
            exit;
        }
        $stmt_check_cpf->close();
    }

    if ($modo_edicao) {
        // Lógica de UPDATE
        $sql = "UPDATE clientes SET 
                    nome_completo = ?, cpf = ?, telefone_celular = ?, telefone_fixo = ?, 
                    email = ?, data_nascimento = ?, endereco_rua = ?, endereco_numero = ?, 
                    endereco_complemento = ?, endereco_bairro = ?, endereco_cidade = ?, 
                    endereco_estado = ?, endereco_cep = ?, observacoes = ?, 
                    id_funcionario_cadastro = ?
                WHERE id = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("ssssssssssssssii",
            $nome_completo, $cpf, $telefone_celular, $telefone_fixo, $email, $data_nascimento,
            $endereco_rua, $endereco_numero, $endereco_complemento, $endereco_bairro,
            $endereco_cidade, $endereco_estado, $endereco_cep, $observacoes,
            $id_funcionario_operacao, $cliente_id_edicao
        );
        $mensagem_acao = "atualizado";

    } else {

        $sql = "INSERT INTO clientes (
                    nome_completo, cpf, telefone_celular, telefone_fixo, email, data_nascimento,
                    endereco_rua, endereco_numero, endereco_complemento, endereco_bairro, endereco_cidade, endereco_estado, endereco_cep,
                    observacoes, id_funcionario_cadastro
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("ssssssssssssssi",
            $nome_completo, $cpf, $telefone_celular, $telefone_fixo, $email, $data_nascimento,
            $endereco_rua, $endereco_numero, $endereco_complemento, $endereco_bairro,
            $endereco_cidade, $endereco_estado, $endereco_cep, $observacoes,
            $id_funcionario_operacao
        );
        $mensagem_acao = "cadastrado";
    }

    if ($stmt) {
        if ($stmt->execute()) {
            $_SESSION['form_message_type'] = "success";
            $_SESSION['form_message'] = "Cliente " . $mensagem_acao . " com sucesso!";
        } else {
            $_SESSION['form_message_type'] = "danger";
            $_SESSION['form_message'] = "Erro ao " . ($modo_edicao ? "atualizar" : "cadastrar") . " cliente: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "Erro ao preparar a consulta: " . $conexao->error;
    }
    $conexao->close();

    if ($_SESSION['form_message_type'] == "success") {
        header("Location: listar_clientes.php");
    } else {
        header("Location: cadastrar_cliente.php" . ($modo_edicao ? "?action=editar&id=".$cliente_id_edicao : ""));
    }
    exit;

} else {
    header("Location: dashboard.php");
    exit;
}
?>