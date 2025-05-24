<?php
session_start();
require_once 'db_conexao.php';
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

if (isset($_SESSION['funcionario_nome'])) {
    $nome_funcionario = htmlspecialchars($_SESSION['funcionario_nome']);
} else {
    $nome_funcionario = "Usuário";
}

$is_admin = (isset($_SESSION['funcionario_cargo']) && $_SESSION['funcionario_cargo'] === 'admin');

$hoje = date('Y-m-d');
$total_clientes = 0;
$result_clientes = $conexao->query("SELECT COUNT(*) as total FROM clientes");
if ($result_clientes) {
    $row_cli_count = $result_clientes->fetch_assoc();
    if ($row_cli_count) {
        $total_clientes = $row_cli_count['total'];
    }
}

$total_servicos_ativos = 0;
$result_servicos = $conexao->query("SELECT COUNT(*) as total FROM servicos WHERE status = 'ativo'");
if ($result_servicos) {
    $row_serv_count = $result_servicos->fetch_assoc();
    if ($row_serv_count){
        $total_servicos_ativos = $row_serv_count['total'];
    }
}

$agendamentos_hoje_total = 0;
$agendamentos_hoje_concluidos = 0;
$agendamentos_hoje_agendados = 0;
$ganhos_hoje = 0.00;

$sql_ag_hoje = "SELECT a.status_agendamento, s.preco 
                FROM agendamentos a
                JOIN servicos s ON a.id_servico = s.id
                WHERE DATE(a.data_hora_inicio) = ?";
$stmt_ag_hoje = $conexao->prepare($sql_ag_hoje);
if ($stmt_ag_hoje) {
    $stmt_ag_hoje->bind_param("s", $hoje);
    $stmt_ag_hoje->execute();
    $result_ag_hoje = $stmt_ag_hoje->get_result();

    if ($result_ag_hoje) {
        $agendamentos_hoje_total = $result_ag_hoje->num_rows;
        while ($ag = $result_ag_hoje->fetch_assoc()) {
            if ($ag['status_agendamento'] == 'concluido') {
                $ganhos_hoje += (float)$ag['preco'];
                $agendamentos_hoje_concluidos++;
            }
            if ($ag['status_agendamento'] == 'agendado') {
                $agendamentos_hoje_agendados++;
            }
        }
    }
    $stmt_ag_hoje->close();
}

$proximos_agendamentos = [];
$data_hora_atual = date('Y-m-d H:i:s');
$sql_proximos = "SELECT a.data_hora_inicio, c.nome_completo as nome_cliente, s.nome_servico
                 FROM agendamentos a
                 JOIN clientes c ON a.id_cliente = c.id
                 JOIN servicos s ON a.id_servico = s.id
                 WHERE a.data_hora_inicio >= ? AND a.status_agendamento = 'agendado'
                 ORDER BY a.data_hora_inicio ASC
                 LIMIT 5";
$stmt_proximos = $conexao->prepare($sql_proximos);
if ($stmt_proximos) {
    $stmt_proximos->bind_param("s", $data_hora_atual);
    $stmt_proximos->execute();
    $result_proximos = $stmt_proximos->get_result();
    if ($result_proximos) {
        while ($row = $result_proximos->fetch_assoc()) {
            $proximos_agendamentos[] = $row;
        }
    }
    $stmt_proximos->close();
}

$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Gestão</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            padding-top: 56px; 
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .action-buttons .btn {
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .stat-card {
            margin-bottom: 20px;
        }
        .stat-card .card-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .stat-card .stat-icon {
            font-size: 2.5rem;
            opacity: 0.6;
        }
        .stat-card .stat-number {
            font-size: 2rem;
            font-weight: bold;
        }
        .stat-card .card-title {
            font-size: 0.95rem;
            color: #6c757d;
        }
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

    <div class="container mt-4">
        <div class="p-5 mb-4 bg-light rounded-3">
            <div class="container-fluid py-4">
                <h1 class="display-5 fw-bold">Acesso Rápido</h1>
                <p class="col-md-10 fs-5">
                    Bem-vindo ao sistema de gerenciamento. Utilize os botões abaixo ou o menu de navegação para acessar as funcionalidades.
                </p>
                <div class="action-buttons mt-4">
                    <a href="cadastrar_cliente.php" class="btn btn-primary btn-lg">Cadastrar Cliente</a>
                    <a href="cadastrar_servico.php" class="btn btn-success btn-lg">Cadastrar Serviço</a>
                    <a href="agendar_horario.php" class="btn btn-info btn-lg">Agendar Horário</a>
                    <a href="gerenciar_agendamentos.php" class="btn btn-warning btn-lg">Gerenciar Agendamentos</a>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <h3>Visão Geral Rápida</h3>
                <hr>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 stat-card">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div>
                            <div class="stat-number"><?php echo $agendamentos_hoje_total; ?></div>
                            <div class="card-title">Agendamentos Hoje</div>
                        </div>
                        <i class="bi bi-calendar-check stat-icon text-primary"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 stat-card">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div>
                            <div class="stat-number">R$ <?php echo number_format($ganhos_hoje, 2, ',', '.'); ?></div>
                            <div class="card-title">Ganhos Hoje (Concluídos)</div>
                        </div>
                        <i class="bi bi-cash-coin stat-icon text-success"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 stat-card">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div>
                            <div class="stat-number"><?php echo $total_clientes; ?></div>
                            <div class="card-title">Total de Clientes</div>
                        </div>
                        <i class="bi bi-people-fill stat-icon text-info"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 stat-card">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div>
                            <div class="stat-number"><?php echo $total_servicos_ativos; ?></div>
                            <div class="card-title">Serviços Ativos</div>
                        </div>
                        <i class="bi bi-tags-fill stat-icon text-warning"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-7">
                <h4>Próximos Agendamentos</h4>
                <?php if (!empty($proximos_agendamentos)): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Data/Hora</th>
                                <th>Cliente</th>
                                <th>Serviço</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proximos_agendamentos as $pa): ?>
                            <tr>
                                <td><?php echo (new DateTime($pa['data_hora_inicio']))->format('d/m H:i'); ?></td>
                                <td><?php echo htmlspecialchars($pa['nome_cliente']); ?></td>
                                <td><?php echo htmlspecialchars($pa['nome_servico']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted">Nenhum próximo agendamento encontrado.</p>
                <?php endif; ?>
            </div>

            <div class="col-md-5">
                <h4>Resumo de Hoje</h4>
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Agendamentos Pendentes
                        <span class="badge bg-primary rounded-pill"><?php echo $agendamentos_hoje_agendados; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Agendamentos Concluídos
                        <span class="badge bg-success rounded-pill"><?php echo $agendamentos_hoje_concluidos; ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>