<?php
date_default_timezone_set('America/Sao_Paulo');
session_start();
require_once 'db_conexao.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['login_error_message'] = "Você precisa estar logado para realizar esta ação.";
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_cliente = trim($_POST['id_cliente']);
    $id_servico = trim($_POST['id_servico']);
    $data_agendamento = trim($_POST['data_agendamento']);
    $hora_agendamento = trim($_POST['hora_agendamento']);
    $observacoes = !empty(trim($_POST['observacoes'])) ? trim($_POST['observacoes']) : NULL;
    
    $id_funcionario_agendou = $_SESSION['funcionario_id'];
    $status_agendamento = 'agendado'; 

    if (empty($id_cliente) || empty($id_servico) || empty($data_agendamento) || empty($hora_agendamento)) {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "Cliente, serviço, data e hora são obrigatórios.";
        header("Location: agendar_horario.php");
        exit;
    }

    try {
        $data_hora_inicio_obj = new DateTime($data_agendamento . ' ' . $hora_agendamento);
        $data_hora_inicio_sql = $data_hora_inicio_obj->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "Formato de data ou hora inválido.";
        header("Location: agendar_horario.php");
        exit;
    }

    $agora = new DateTime();
    if ($data_hora_inicio_obj < $agora) {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "Não é possível agendar horários em datas ou horas passadas.";
        header("Location: agendar_horario.php");
        exit;
    }

    $sql_duracao = "SELECT duracao_estimada_minutos FROM servicos WHERE id = ?";
    $stmt_duracao = $conexao->prepare($sql_duracao);
    $stmt_duracao->bind_param("i", $id_servico);
    $stmt_duracao->execute();
    $result_duracao = $stmt_duracao->get_result();
    
    if ($result_duracao->num_rows === 1) {
        $servico_info = $result_duracao->fetch_assoc();
        $duracao_minutos = (int)$servico_info['duracao_estimada_minutos'];
        if ($duracao_minutos <= 0) { 
            $_SESSION['form_message_type'] = "danger";
            $_SESSION['form_message'] = "A duração do serviço selecionado não é válida ou não foi informada. Verifique o cadastro do serviço.";
            $stmt_duracao->close();
            $conexao->close();
            header("Location: agendar_horario.php");
            exit;
        }
    } else {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "Serviço não encontrado ou inválido.";
        $stmt_duracao->close();
        $conexao->close();
        header("Location: agendar_horario.php");
        exit;
    }
    $stmt_duracao->close();

    $data_hora_fim_obj = clone $data_hora_inicio_obj; // Clona o objeto para não modificar o original
    $data_hora_fim_obj->add(new DateInterval('PT' . $duracao_minutos . 'M')); // Adiciona X minutos
    $data_hora_fim_sql = $data_hora_fim_obj->format('Y-m-d H:i:s');

    $sql_insert = "INSERT INTO agendamentos (id_cliente, id_servico, id_funcionario_agendou, data_hora_inicio, data_hora_fim, status_agendamento, observacoes) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";

    if ($stmt_insert = $conexao->prepare($sql_insert)) {
        $stmt_insert->bind_param("iiissss",
            $id_cliente,
            $id_servico,
            $id_funcionario_agendou,
            $data_hora_inicio_sql,
            $data_hora_fim_sql,
            $status_agendamento,
            $observacoes
        );

        if ($stmt_insert->execute()) {
            $_SESSION['form_message_type'] = "success";
            $_SESSION['form_message'] = "Agendamento realizado com sucesso!";
        } else {
            $_SESSION['form_message_type'] = "danger";
            $_SESSION['form_message'] = "Erro ao realizar agendamento: (" . $stmt_insert->errno . ") " . $stmt_insert->error;
        }
        $stmt_insert->close();
    } else {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "Erro ao preparar a consulta de agendamento: (" . $conexao->errno . ") " . $conexao->error;
    }
    $conexao->close();
    header("Location: agendar_horario.php");
    exit;

} else {
    header("Location: dashboard.php");
    exit;
}
?>