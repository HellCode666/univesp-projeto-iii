<?php
session_start();
require_once 'db_conexao.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['form_message_type'] = "danger";
    $_SESSION['form_message'] = "Você precisa estar logado para realizar esta ação.";
    header("Location: login.php");
    exit;
}

$redirect_url = "listar_clientes.php"; 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cliente_id_delete'])) {
    $cliente_id = (int)$_POST['cliente_id_delete'];

    if ($cliente_id <= 0) {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "ID de cliente inválido.";
        header("Location: " . $redirect_url);
        exit;
    }


    $sql_delete = "DELETE FROM clientes WHERE id = ?";
    $stmt_delete = $conexao->prepare($sql_delete);

    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $cliente_id);
        if ($stmt_delete->execute()) {
            if ($stmt_delete->affected_rows > 0) {
                $_SESSION['form_message_type'] = "success";
                $_SESSION['form_message'] = "Cliente e todos os seus agendamentos associados foram excluídos com sucesso!";
            } else {
                $_SESSION['form_message_type'] = "warning";
                $_SESSION['form_message'] = "Nenhum cliente encontrado com este ID para exclusão, ou já foi excluído.";
            }
        } else {
            $_SESSION['form_message_type'] = "danger";
            $_SESSION['form_message'] = "Erro ao excluir cliente: (" . $stmt_delete->errno . ") " . $stmt_delete->error;
        }
        $stmt_delete->close();
    } else {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "Erro ao preparar a exclusão do cliente: (" . $conexao->errno . ") " . $conexao->error;
    }
    $conexao->close();
    header("Location: " . $redirect_url);
    exit;

} else {
    $_SESSION['form_message_type'] = "danger";
    $_SESSION['form_message'] = "Ação inválida ou ID do cliente não fornecido.";
    header("Location: " . $redirect_url);
    exit;
}
?>