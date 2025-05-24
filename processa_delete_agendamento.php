<?php
session_start();
require_once 'db_conexao.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['page_message_type'] = "danger";
    $_SESSION['page_message'] = "Você precisa estar logado para realizar esta ação.";
    header("Location: login.php"); // Melhor redirecionar para login se não estiver logado
    exit;
}

$redirect_url = "gerenciar_agendamentos.php"; // URL padrão de redirecionamento
if (isset($_POST['redirect_filtro'])) {
    $redirect_url .= "?filtro=" . urlencode($_POST['redirect_filtro']);
    if ($_POST['redirect_filtro'] == 'data_especifica' && isset($_POST['redirect_data'])) {
         $redirect_url = "gerenciar_agendamentos.php?data=" . urlencode($_POST['redirect_data']);
    } elseif (isset($_POST['redirect_data']) && !empty($_POST['redirect_data']) && $_POST['redirect_filtro'] != 'data_especifica') {

    }
} elseif (isset($_POST['redirect_data'])) { // Se apenas a data foi passada
     $redirect_url = "gerenciar_agendamentos.php?data=" . urlencode($_POST['redirect_data']);
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_agendamento_delete'])) {
    $id_agendamento = (int)$_POST['id_agendamento_delete'];

    if ($id_agendamento <= 0) {
        $_SESSION['page_message_type'] = "danger";
        $_SESSION['page_message'] = "ID de agendamento inválido.";
        header("Location: " . $redirect_url);
        exit;
    }

    $sql_delete = "DELETE FROM agendamentos WHERE id = ?";
    $stmt_delete = $conexao->prepare($sql_delete);

    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $id_agendamento);
        if ($stmt_delete->execute()) {
            if ($stmt_delete->affected_rows > 0) {
                $_SESSION['page_message_type'] = "success";
                $_SESSION['page_message'] = "Agendamento excluído com sucesso!";
            } else {
                $_SESSION['page_message_type'] = "warning";
                $_SESSION['page_message'] = "Nenhum agendamento encontrado com este ID para exclusão, ou já foi excluído.";
            }
        } else {
            $_SESSION['page_message_type'] = "danger";
            $_SESSION['page_message'] = "Erro ao excluir agendamento: " . $stmt_delete->error;
        }
        $stmt_delete->close();
    } else {
        $_SESSION['page_message_type'] = "danger";
        $_SESSION['page_message'] = "Erro ao preparar a exclusão do agendamento: " . $conexao->error;
    }
    $conexao->close();
    header("Location: " . $redirect_url);
    exit;

} else {
    $_SESSION['page_message_type'] = "danger";
    $_SESSION['page_message'] = "Ação inválida ou ID do agendamento não fornecido.";
    header("Location: " . $redirect_url);
    exit;
}
?>