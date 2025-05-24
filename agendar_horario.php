<?php
session_start();
require_once 'db_conexao.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$nome_funcionario = htmlspecialchars($_SESSION['funcionario_nome']);

$clientes = [];
$sql_clientes = "SELECT id, nome_completo FROM clientes ORDER BY nome_completo ASC";
$result_clientes = $conexao->query($sql_clientes);
if ($result_clientes && $result_clientes->num_rows > 0) {
    while ($row = $result_clientes->fetch_assoc()) {
        $clientes[] = $row;
    }
}

$servicos = [];
$sql_servicos = "SELECT id, nome_servico, duracao_estimada_minutos FROM servicos WHERE status = 'ativo' ORDER BY nome_servico ASC";
$result_servicos = $conexao->query($sql_servicos);
if ($result_servicos && $result_servicos->num_rows > 0) {
    while ($row = $result_servicos->fetch_assoc()) {
        $servicos[] = $row;
    }
}

$is_admin = (isset($_SESSION['funcionario_cargo']) && $_SESSION['funcionario_cargo'] === 'admin');

$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Horário - Sistema de Gestão</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 56px; }
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
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <h2>Novo Agendamento</h2>
                <hr>

                <?php
                if (isset($_SESSION['form_message_type']) && isset($_SESSION['form_message'])) {
                    echo '<div class="alert alert-' . htmlspecialchars($_SESSION['form_message_type']) . '" role="alert">' . htmlspecialchars($_SESSION['form_message']) . '</div>';
                    unset($_SESSION['form_message_type']);
                    unset($_SESSION['form_message']);
                }
                ?>

                <form action="processa_agendamento.php" method="POST" id="formAgendamento">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_cliente" class="form-label">Cliente <span class="text-danger">*</span></label>
                            <select class="form-select" id="id_cliente" name="id_cliente" required>
                                <option value="" selected disabled>Selecione um cliente...</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo $cliente['id']; ?>"><?php echo htmlspecialchars($cliente['nome_completo']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="id_servico" class="form-label">Serviço <span class="text-danger">*</span></label>
                            <select class="form-select" id="id_servico" name="id_servico" required>
                                <option value="" selected disabled>Selecione um serviço...</option>
                                <?php foreach ($servicos as $servico): ?>
                                    <option value="<?php echo $servico['id']; ?>" data-duracao="<?php echo $servico['duracao_estimada_minutos']; ?>">
                                        <?php echo htmlspecialchars($servico['nome_servico']); ?>
                                        (<?php echo $servico['duracao_estimada_minutos'] ? $servico['duracao_estimada_minutos'] . ' min' : 'Duração não informada'; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small id="duracaoInfo" class="form-text text-muted"></small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="data_agendamento" class="form-label">Data do Agendamento <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="data_agendamento" name="data_agendamento" required 
                                   min="<?php echo date('Y-m-d');?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="hora_agendamento" class="form-label">Hora do Agendamento <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="hora_agendamento" name="hora_agendamento" required>
                            </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações</label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-primary btn-lg">Agendar</button>
                    <a href="dashboard.php" class="btn btn-secondary btn-lg">Cancelar</a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('id_servico').addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            var duracao = selectedOption.getAttribute('data-duracao');
            var duracaoInfoEl = document.getElementById('duracaoInfo');
            if (duracao && duracao > 0) {
                duracaoInfoEl.textContent = 'Duração estimada: ' + duracao + ' minutos.';
            } else if (selectedOption.value !== "") {
                duracaoInfoEl.textContent = 'Duração não informada para este serviço.';
            } else {
                duracaoInfoEl.textContent = '';
            }
        });
    </script>
</body>
</html>