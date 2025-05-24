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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['funcionario_id_delete'])) {
    $funcionario_id_delete = (int)$_POST['funcionario_id_delete'];

    if ($funcionario_id_delete <= 0) {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "ID de funcionário inválido.";
        header("Location: listar_funcionarios.php");
        exit;
    }

    if ($funcionario_id_delete === $id_funcionario_logado) {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "Você não pode excluir sua própria conta de administrador.";
        header("Location: listar_funcionarios.php");
        exit;
    }

    $sql_get_cargo = "SELECT cargo FROM funcionarios WHERE id = ?";
    $stmt_get_cargo = $conexao->prepare($sql_get_cargo);
    $stmt_get_cargo->bind_param("i", $funcionario_id_delete);
    $stmt_get_cargo->execute();
    $res_get_cargo = $stmt_get_cargo->get_result();
    $cargo_deletar = $res_get_cargo->fetch_assoc()['cargo'];
    $stmt_get_cargo->close();

    if ($cargo_deletar === 'admin') {
        $sql_count_admins = "SELECT COUNT(*) as total_admins FROM funcionarios WHERE cargo = 'admin'";
        $res_count_admins = $conexao->query($sql_count_admins);
        $count_admins = $res_count_admins->fetch_assoc()['total_admins'];
        if ($count_admins <= 1) {
            $_SESSION['form_message_type'] = "danger";
            $_SESSION['form_message'] = "Não é possível excluir o último administrador do sistema.";
            header("Location: listar_funcionarios.php");
            exit;
        }
    }

    $tabelas_com_restricao = [
        'servicos' => 'id_funcionario_cadastro',
        'agendamentos' => 'id_funcionario_agendou'
    ];
    foreach ($tabelas_com_restricao as $tabela => $coluna_fk) {
        $sql_check_fk = "SELECT COUNT(*) as total FROM $tabela WHERE $coluna_fk = ?";
        $stmt_fk = $conexao->prepare($sql_check_fk);
        $stmt_fk->bind_param("i", $funcionario_id_delete);
        $stmt_fk->execute();
        $count_fk = $stmt_fk->get_result()->fetch_assoc()['total'];
        $stmt_fk->close();
        if ($count_fk > 0) {
            $_SESSION['form_message_type'] = "danger";
            $_SESSION['form_message'] = "Este funcionário não pode ser excluído pois está associado a $count_fk registro(s) na tabela '$tabela'. Reatribua ou delete esses registros primeiro.";
            header("Location: listar_funcionarios.php");
            exit;
        }
    }
    
    $sql_delete = "DELETE FROM funcionarios WHERE id = ?";
    $stmt_delete = $conexao->prepare($sql_delete);
    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $funcionario_id_delete);
        if ($stmt_delete->execute()) {
            if ($stmt_delete->affected_rows > 0) {
                $_SESSION['form_message_type'] = "success";
                $_SESSION['form_message'] = "Funcionário excluído com sucesso!";
            } else {
                $_SESSION['form_message_type'] = "warning";
                $_SESSION['form_message'] = "Funcionário não encontrado ou já excluído.";
            }
        } else {
            $_SESSION['form_message_type'] = "danger";
            $_SESSION['form_message'] = "Erro ao excluir funcionário: " . $stmt_delete->error;
        }
        $stmt_delete->close();
    } else {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "Erro ao preparar exclusão: " . $conexao->error;
    }
    $conexao->close();
    header("Location: listar_funcionarios.php");
    exit;

} else {
    header("Location: listar_funcionarios.php");
    exit;
}
?>