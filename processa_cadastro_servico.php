<?php
session_start();
require_once 'db_conexao.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['login_error_message'] = "Você precisa estar logado para realizar esta ação.";
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servico_id_edicao = isset($_POST['servico_id']) ? (int)$_POST['servico_id'] : null;
    $modo_edicao = ($servico_id_edicao !== null);

    $nome_servico = trim($_POST['nome_servico']);
    $descricao = !empty(trim($_POST['descricao'])) ? trim($_POST['descricao']) : NULL;
    $preco = trim($_POST['preco']);
    $duracao_estimada_minutos = !empty(trim($_POST['duracao_estimada_minutos'])) ? (int)trim($_POST['duracao_estimada_minutos']) : NULL;
    $status = trim($_POST['status']);
    $id_funcionario_operacao = $_SESSION['funcionario_id'];

    if (empty($nome_servico) || empty($preco) || empty($status)) {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "Nome do serviço, preço e status são obrigatórios.";
        header("Location: cadastrar_servico.php" . ($modo_edicao ? "?action=editar&id=".$servico_id_edicao : ""));
        exit;
    }
    if (!is_numeric($preco) || (float)$preco < 0) {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "O preço deve ser um valor numérico não negativo.";
        header("Location: cadastrar_servico.php" . ($modo_edicao ? "?action=editar&id=".$servico_id_edicao : ""));
        exit;
    }
    $preco_formatado = number_format((float)$preco, 2, '.', '');

    if ($duracao_estimada_minutos !== NULL && (!is_numeric($duracao_estimada_minutos) || (int)$duracao_estimada_minutos < 0)) {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "A duração estimada deve ser um número inteiro não negativo ou vazia.";
        header("Location: cadastrar_servico.php" . ($modo_edicao ? "?action=editar&id=".$servico_id_edicao : ""));
        exit;
    }
    if (!in_array($status, ['ativo', 'inativo'])) {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "Status inválido.";
        header("Location: cadastrar_servico.php" . ($modo_edicao ? "?action=editar&id=".$servico_id_edicao : ""));
        exit;
    }

    if ($modo_edicao) {
        $sql = "UPDATE servicos SET 
                    nome_servico = ?, descricao = ?, preco = ?, duracao_estimada_minutos = ?, 
                    status = ?, id_funcionario_cadastro = ?
                WHERE id = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("ssdissi",
            $nome_servico, $descricao, $preco_formatado, $duracao_estimada_minutos,
            $status, $id_funcionario_operacao, $servico_id_edicao
        );
        $mensagem_acao = "atualizado";
    } else {
        $sql = "INSERT INTO servicos (
                    nome_servico, descricao, preco, duracao_estimada_minutos, status, id_funcionario_cadastro
                ) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("ssdisi",
            $nome_servico, $descricao, $preco_formatado, $duracao_estimada_minutos,
            $status, $id_funcionario_operacao
        );
        $mensagem_acao = "cadastrado";
    }

    if ($stmt) {
        if ($stmt->execute()) {
            $_SESSION['form_message_type'] = "success";
            $_SESSION['form_message'] = "Serviço " . $mensagem_acao . " com sucesso!";
        } else {
            $_SESSION['form_message_type'] = "danger";
            $_SESSION['form_message'] = "Erro ao " . ($modo_edicao ? "atualizar" : "cadastrar") . " serviço: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "Erro ao preparar a consulta: " . $conexao->error;
    }
    $conexao->close();

    if ($_SESSION['form_message_type'] == "success") {
        header("Location: listar_servicos.php");
    } else {
        header("Location: cadastrar_servico.php" . ($modo_edicao ? "?action=editar&id=".$servico_id_edicao : ""));
    }
    exit;

} else {
    header("Location: dashboard.php");
    exit;
}
?>