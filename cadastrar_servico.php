<?php
session_start();
require_once 'db_conexao.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
$nome_funcionario_logado = htmlspecialchars($_SESSION['funcionario_nome'] ?? 'Usuário');
$is_admin = (isset($_SESSION['funcionario_cargo']) && $_SESSION['funcionario_cargo'] === 'admin');

$is_admin = (isset($_SESSION['funcionario_cargo']) && $_SESSION['funcionario_cargo'] === 'admin');

if (isset($_SESSION['funcionario_nome'])) {
    $nome_funcionario = htmlspecialchars($_SESSION['funcionario_nome']);
} else {
    $nome_funcionario = "Usuário";
}

$modo_edicao = false;
$servico_id_edicao = null;
$servico_data = [
    'nome_servico' => '',
    'descricao' => '',
    'preco' => '',
    'duracao_estimada_minutos' => '',
    'status' => 'ativo'
];

if (isset($_GET['action']) && $_GET['action'] == 'editar' && isset($_GET['id'])) {
    $modo_edicao = true;
    $servico_id_edicao = (int)$_GET['id'];

    $sql_servico = "SELECT * FROM servicos WHERE id = ?";
    $stmt_servico = $conexao->prepare($sql_servico);
    if ($stmt_servico) {
        $stmt_servico->bind_param("i", $servico_id_edicao);
        $stmt_servico->execute();
        $result_servico = $stmt_servico->get_result();
        if ($result_servico->num_rows === 1) {
            $db_data = $result_servico->fetch_assoc();
            foreach ($servico_data as $key => $value) {
                if (isset($db_data[$key])) {
                    $servico_data[$key] = $db_data[$key];
                }
            }
        } else {
            $_SESSION['form_message_type'] = "danger";
            $_SESSION['form_message'] = "Serviço não encontrado para edição.";
            $stmt_servico->close();
            $conexao->close();
            header("Location: listar_servicos.php");
            exit;
        }
        $stmt_servico->close();
    } else {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "Erro ao preparar para buscar serviço.";
        $conexao->close();
        header("Location: listar_servicos.php");
        exit;
    }
}
$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $modo_edicao ? 'Editar Serviço' : 'Cadastrar Novo Serviço'; ?> - Sistema de Gestão</title>
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
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <h2><?php echo $modo_edicao ? 'Editar Serviço: ' . htmlspecialchars($servico_data['nome_servico'] ?? '') : 'Cadastro de Novo Serviço'; ?></h2>
                <hr>

                <?php
                if (isset($_SESSION['form_message_type']) && isset($_SESSION['form_message'])) {
                    echo '<div class="alert alert-' . htmlspecialchars($_SESSION['form_message_type']) . '" role="alert">' . nl2br(htmlspecialchars($_SESSION['form_message'])) . '</div>';
                    unset($_SESSION['form_message_type']);
                    unset($_SESSION['form_message']);
                }
                ?>

                <form action="processa_cadastro_servico.php" method="POST">
                    <?php if ($modo_edicao): ?>
                        <input type="hidden" name="servico_id" value="<?php echo $servico_id_edicao; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="nome_servico" class="form-label">Nome do Serviço <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome_servico" name="nome_servico" required value="<?php echo htmlspecialchars($servico_data['nome_servico'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3"><?php echo htmlspecialchars($servico_data['descricao'] ?? ''); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="preco" class="form-label">Preço (R$) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="preco" name="preco" step="0.01" min="0" placeholder="Ex: 50.00" required value="<?php echo htmlspecialchars($servico_data['preco'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="duracao_estimada_minutos" class="form-label">Duração Estimada (minutos)</label>
                            <input type="number" class="form-control" id="duracao_estimada_minutos" name="duracao_estimada_minutos" min="0" placeholder="Ex: 60" value="<?php echo htmlspecialchars($servico_data['duracao_estimada_minutos'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status do Serviço <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="ativo" <?php echo ($servico_data['status'] == 'ativo') ? 'selected' : ''; ?>>Ativo</option>
                            <option value="inativo" <?php echo ($servico_data['status'] == 'inativo') ? 'selected' : ''; ?>>Inativo</option>
                        </select>
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <?php echo $modo_edicao ? 'Salvar Alterações' : 'Cadastrar Serviço'; ?>
                    </button>
                    <a href="<?php echo $modo_edicao ? 'listar_servicos.php' : 'dashboard.php'; ?>" class="btn btn-secondary btn-lg">Cancelar</a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>