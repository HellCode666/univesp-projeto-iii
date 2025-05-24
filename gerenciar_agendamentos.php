<?php
date_default_timezone_set('America/Sao_Paulo');
session_start();
require_once 'db_conexao.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
$nome_funcionario = htmlspecialchars($_SESSION['funcionario_nome'] ?? 'Usuário');
$is_admin = (isset($_SESSION['funcionario_cargo']) && $_SESSION['funcionario_cargo'] === 'admin');

$filtro_ativo = $_GET['filtro'] ?? 'hoje';
$data_visualizacao_input = $_GET['data'] ?? date('Y-m-d');

if (isset($_GET['data'])) {
    $data_teste = DateTime::createFromFormat('Y-m-d', $data_visualizacao_input);
    if (!$data_teste || $data_teste->format('Y-m-d') !== $data_visualizacao_input) {
        $data_visualizacao_input = date('Y-m-d');
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_agendamento_status_update']) && isset($_POST['novo_status'])) { 
    $id_agendamento_update = (int)$_POST['id_agendamento_status_update'];
    $novo_status_update = $_POST['novo_status'];
    $permitidos_status = ['agendado', 'concluido', 'cancelado', 'nao_compareceu'];

    if (in_array($novo_status_update, $permitidos_status)) {
        $sql_update = "UPDATE agendamentos SET status_agendamento = ? WHERE id = ?";
        $stmt_update = $conexao->prepare($sql_update);
        $stmt_update->bind_param("si", $novo_status_update, $id_agendamento_update);
        if ($stmt_update->execute()) {
            $_SESSION['page_message_type'] = "success";
            $_SESSION['page_message'] = "Status do agendamento atualizado!";
        } else {
            $_SESSION['page_message_type'] = "danger";
            $_SESSION['page_message'] = "Erro ao atualizar status: " . $stmt_update->error;
        }
        $stmt_update->close();
    } else {
        $_SESSION['page_message_type'] = "danger";
        $_SESSION['page_message'] = "Status inválido selecionado.";
    }
    $redirect_param = isset($_GET['data']) ? "data=" . urlencode($data_visualizacao_input) : "filtro=" . urlencode($filtro_ativo);
    header("Location: gerenciar_agendamentos.php?" . $redirect_param);
    exit;
}

// Buscar agendamentos
$agendamentos_lista = []; 
$total_ganho_dia = 0.00;
$titulo_secao = "Agendamentos";
$params_sql = [];
$types_sql = "";

$sql_base = "SELECT 
                a.id, a.data_hora_inicio, a.data_hora_fim, a.status_agendamento,
                a.observacoes, c.nome_completo as nome_cliente, 
                s.nome_servico, s.preco as preco_servico
             FROM agendamentos a
             JOIN clientes c ON a.id_cliente = c.id
             JOIN servicos s ON a.id_servico = s.id";
$where_conditions = [];
$order_by = "ORDER BY a.data_hora_inicio ASC";

$hoje_sql = date('Y-m-d');
$data_selecionada_para_view = $data_visualizacao_input;

if (isset($_GET['data'])) {
    $filtro_ativo = 'data_especifica';
    $where_conditions[] = "DATE(a.data_hora_inicio) = ?";
    $params_sql[] = $data_visualizacao_input;
    $types_sql .= "s";
    $titulo_secao = "Agendamentos para " . DateTime::createFromFormat('Y-m-d', $data_visualizacao_input)->format('d/m/Y');
} else {
    switch ($filtro_ativo) {
        case 'proximos7':
            $where_conditions[] = "a.data_hora_inicio >= ? AND a.data_hora_inicio < ?";
            $params_sql[] = $hoje_sql . " 00:00:00";
            $params_sql[] = date('Y-m-d', strtotime('+8 days')) . " 00:00:00";
            $types_sql .= "ss";
            $titulo_secao = "Agendamentos para os Próximos 7 Dias";
            $data_selecionada_para_view = $hoje_sql;
            break;
        case 'ultimos7':
            $data_inicio_intervalo = date('Y-m-d', strtotime('-6 days'));
            $where_conditions[] = "DATE(a.data_hora_inicio) BETWEEN ? AND ?";
            $params_sql[] = $data_inicio_intervalo;
            $params_sql[] = $hoje_sql;
            $types_sql .= "ss";
            $titulo_secao = "Agendamentos dos Últimos 7 Dias";
            $data_selecionada_para_view = $hoje_sql;
            break;
        case 'estemes':
            $where_conditions[] = "YEAR(a.data_hora_inicio) = YEAR(CURDATE()) AND MONTH(a.data_hora_inicio) = MONTH(CURDATE())";
            $titulo_secao = "Agendamentos para Este Mês";
            $data_selecionada_para_view = date('Y-m-01');
            break;
        case 'historico_completo':
            $titulo_secao = "Histórico Completo de Agendamentos";
            $order_by = "ORDER BY a.data_hora_inicio DESC";
            $data_selecionada_para_view = $hoje_sql; 
            break;
        case 'hoje':
        default:
            $filtro_ativo = 'hoje';
            $where_conditions[] = "DATE(a.data_hora_inicio) = ?";
            $params_sql[] = $hoje_sql;
            $types_sql .= "s";
            $titulo_secao = "Agendamentos para Hoje (" . DateTime::createFromFormat('Y-m-d', $hoje_sql)->format('d/m/Y') . ")";
            $data_selecionada_para_view = $hoje_sql;
            break;
    }
}

$sql_final_agendamentos = $sql_base;
if (!empty($where_conditions)) {
    $sql_final_agendamentos .= " WHERE " . implode(" AND ", $where_conditions);
}
$sql_final_agendamentos .= " " . $order_by;

$stmt_agendamentos = $conexao->prepare($sql_final_agendamentos);
if ($stmt_agendamentos) {
    if (!empty($params_sql)) {
        $stmt_agendamentos->bind_param($types_sql, ...$params_sql);
    }
    $stmt_agendamentos->execute();
    $result_agendamentos = $stmt_agendamentos->get_result();

    if ($result_agendamentos->num_rows > 0) {
        while ($row = $result_agendamentos->fetch_assoc()) {
            $agendamentos_lista[] = $row;
            if (($filtro_ativo === 'hoje' || $filtro_ativo === 'data_especifica') && $row['status_agendamento'] === 'concluido') {
                if ($filtro_ativo === 'data_especifica' && date('Y-m-d', strtotime($row['data_hora_inicio'])) === $data_visualizacao_input) {
                    $total_ganho_dia += (float)$row['preco_servico'];
                } elseif ($filtro_ativo === 'hoje') {
                     $total_ganho_dia += (float)$row['preco_servico'];
                }
            }
        }
    }
    $stmt_agendamentos->close();
} else {
    $_SESSION['page_message_type'] = "danger";
    $_SESSION['page_message'] = "Erro ao preparar consulta de agendamentos: " . $conexao->error;
}

$conexao->close();

$status_opcoes = [
    'agendado' => 'Agendado', 'concluido' => 'Concluído',
    'cancelado' => 'Cancelado', 'nao_compareceu' => 'Não Compareceu'
];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Agendamentos - <?php echo strip_tags($titulo_secao); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { padding-top: 56px; }
        .total-dia { font-size: 1.5rem; font-weight: bold; }
        .status-badge { font-size: 0.9em; }
        .table th, .table td { vertical-align: middle; }
        .filter-buttons .btn { margin-right: 5px; margin-bottom: 10px;}
        .actions-form { display: inline-block; margin-left: 5px; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Projeto III</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="dashboard.php">Início</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cadastrar_cliente.php">Cadastrar Cliente</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="listar_clientes.php">Listar Clientes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cadastrar_servico.php">Cadastrar Serviço</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="listar_servicos.php">Listar Serviços</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="agendar_horario.php">Agendar Horário</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="gerenciar_agendamentos.php">Gerenciar Agendamentos</a>
                    </li>
                    <?php if ($is_admin): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Administração
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="adminMenuLink">
                            <li><a class="dropdown-item" href="listar_funcionarios.php">Gerenciar Funcionários</a></li>
                            <li><a class="dropdown-item" href="cadastrar_funcionario.php">Novo Funcionário</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUser" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Olá, <?php echo $nome_funcionario; ?>!
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
                            <li><a class="dropdown-item text-danger" href="logout.php">Sair do Sistema</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


    <div class="container mt-5 mb-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <h2 class="me-3"><?php echo htmlspecialchars($titulo_secao); ?></h2>
            <form method="GET" action="gerenciar_agendamentos.php" class="d-flex align-items-center flex-grow-1 flex-md-grow-0 mb-2 mb-md-0">
                <input type="date" class="form-control me-2" name="data" value="<?php echo htmlspecialchars($data_selecionada_para_view); ?>" style="min-width: 180px;">
                <button type="submit" class="btn btn-primary">Ver Data Específica</button>
            </form>
        </div>
        <div class="filter-buttons mb-3">
            <a href="?filtro=hoje" class="btn btn-outline-secondary <?php if($filtro_ativo == 'hoje' && !isset($_GET['data'])) echo 'active'; ?>">Hoje</a>
            <a href="?filtro=proximos7" class="btn btn-outline-secondary <?php if($filtro_ativo == 'proximos7') echo 'active'; ?>">Próximos 7 Dias</a>
            <a href="?filtro=ultimos7" class="btn btn-outline-secondary <?php if($filtro_ativo == 'ultimos7') echo 'active'; ?>">Últimos 7 Dias</a>
            <a href="?filtro=estemes" class="btn btn-outline-secondary <?php if($filtro_ativo == 'estemes') echo 'active'; ?>">Este Mês</a>
            <a href="?filtro=historico_completo" class="btn btn-outline-secondary <?php if($filtro_ativo == 'historico_completo') echo 'active'; ?>">Histórico Completo</a>
        </div>
         <hr>

        <?php
        if (isset($_SESSION['page_message_type']) && isset($_SESSION['page_message'])) {
            echo '<div class="alert alert-' . htmlspecialchars($_SESSION['page_message_type']) . ' alert-dismissible fade show" role="alert">' . 
                 nl2br(htmlspecialchars($_SESSION['page_message'])) . 
                 '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['page_message_type']);
            unset($_SESSION['page_message']);
        }
        ?>

        <?php if ($filtro_ativo === 'hoje' || $filtro_ativo === 'data_especifica'): ?>
        <div class="card mb-4">
            <div class="card-body text-end">
                <span class="text-muted">Total Concluído (<?php echo $filtro_ativo === 'hoje' ? 'Hoje' : 'nesta data'; ?>):</span>
                <span class="total-dia text-success">R$ <?php echo number_format($total_ganho_dia, 2, ',', '.'); ?></span>
            </div>
        </div>
        <?php endif; ?>


        <?php if (empty($agendamentos_lista)): ?>
            <div class="alert alert-info mt-3" role="alert">
                Nenhum agendamento encontrado para o filtro selecionado.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Data</th>
                            <th>Horário</th>
                            <th>Cliente</th>
                            <th>Serviço</th>
                            <th>Preço</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agendamentos_lista as $ag): ?>
                        <?php
                            $data_ag_obj = new DateTime($ag['data_hora_inicio']);
                            $data_f = $data_ag_obj->format('d/m/Y');
                            $hora_inicio_f = $data_ag_obj->format('H:i');
                            $hora_fim_f = (new DateTime($ag['data_hora_fim']))->format('H:i');
                            $badge_class = 'secondary';
                            switch ($ag['status_agendamento']) {
                                case 'agendado': $badge_class = 'primary'; break;
                                case 'concluido': $badge_class = 'success'; break;
                                case 'cancelado': $badge_class = 'danger'; break;
                                case 'nao_compareceu': $badge_class = 'warning text-dark'; break;
                            }
                        ?>
                        <tr>
                            <td><?php echo $data_f; ?></td>
                            <td><?php echo $hora_inicio_f; ?> - <?php echo $hora_fim_f; ?></td>
                            <td><?php echo htmlspecialchars($ag['nome_cliente']); ?></td>
                            <td><?php echo htmlspecialchars($ag['nome_servico']); ?></td>
                            <td>R$ <?php echo number_format($ag['preco_servico'], 2, ',', '.'); ?></td>
                            <td><span class="badge bg-<?php echo $badge_class; ?> status-badge"><?php echo htmlspecialchars($status_opcoes[$ag['status_agendamento']]); ?></span></td>
                            <td>
                                <form method="POST" action="gerenciar_agendamentos.php?<?php echo isset($_GET['data']) ? "data=" . urlencode($data_visualizacao_input) : "filtro=" . urlencode($filtro_ativo); ?>" class="d-inline-flex align-items-center mb-1">
                                    <input type="hidden" name="id_agendamento_status_update" value="<?php echo $ag['id']; ?>">
                                    <select name="novo_status" class="form-select form-select-sm me-1" style="width: auto; min-width: 140px;">
                                        <?php foreach ($status_opcoes as $value => $label): ?>
                                        <option value="<?php echo $value; ?>" <?php echo ($ag['status_agendamento'] === $value) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($label); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Salvar</button>
                                </form>
                                <form method="POST" action="processa_delete_agendamento.php" class="actions-form" onsubmit="return confirm('Tem certeza que deseja excluir este agendamento permanentemente?');">
                                    <input type="hidden" name="id_agendamento_delete" value="<?php echo $ag['id']; ?>">
                                    <input type="hidden" name="redirect_filtro" value="<?php echo htmlspecialchars($filtro_ativo); ?>">
                                    <input type="hidden" name="redirect_data" value="<?php echo htmlspecialchars($data_visualizacao_input); ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Excluir Agendamento">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>