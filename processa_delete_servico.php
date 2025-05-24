<?php
session_start();
require_once 'db_conexao.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['form_message_type'] = "danger";
    $_SESSION['form_message'] = "Você precisa estar logado para realizar esta ação.";
    header("Location: listar_servicos.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['servico_id_delete'])) {
    $servico_id = (int)$_POST['servico_id_delete'];

    if ($servico_id <= 0) {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "ID de serviço inválido.";
        header("Location: listar_servicos.php");
        exit;
    }

    $sql_check_agendamentos = "SELECT COUNT(*) as total_agendamentos FROM agendamentos WHERE id_servico = ?";
    $stmt_check = $conexao->prepare($sql_check_agendamentos);
    $stmt_check->bind_param("i", $servico_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row_check = $result_check->fetch_assoc();
    $stmt_check->close();

    if ($row_check && $row_check['total_agendamentos'] > 0) {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "Este serviço não pode ser excluído pois está associado a " . $row_check['total_agendamentos'] . " agendamento(s). Considere desativá-lo.";
        header("Location: listar_servicos.php");
        exit;
    }

    $sql_delete = "DELETE FROM servicos WHERE id = ?";
    $stmt_delete = $conexao->prepare($sql_delete);
    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $servico_id);
        if ($stmt_delete->execute()) {
            if ($stmt_delete->affected_rows > 0) {
                $_SESSION['form_message_type'] = "success";
                $_SESSION['form_message'] = "Serviço excluído com sucesso!";
            } else {
                $_SESSION['form_message_type'] = "warning";
                $_SESSION['form_message'] = "Nenhum serviço encontrado com este ID para exclusão, ou já foi excluído.";
            }
        } else {
            $_SESSION['form_message_type'] = "danger";
            $_SESSION['form_message'] = "Erro ao excluir serviço: (" . $stmt_delete->errno . ") " . $stmt_delete->error;
        }
        $stmt_delete->close();
    } else {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "Erro ao preparar a exclusão do serviço: (" . $conexao->errno . ") " . $conexao->error;
    }
    $conexao->close();
    header("Location: listar_servicos.php");
    exit;

} else {

    $_SESSION['form_message_type'] = "danger";
    $_SESSION['form_message'] = "Ação inválida ou ID do serviço não fornecido.";
    header("Location: listar_servicos.php");
    exit;
}
?>