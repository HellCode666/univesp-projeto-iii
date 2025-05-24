<?php
session_start();
require_once 'db_conexao.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['funcionario_cargo']) || $_SESSION['funcionario_cargo'] !== 'admin') {
    $_SESSION['page_message_type'] = "danger";
    $_SESSION['page_message'] = "Acesso negado. Funcionalidade restrita a administradores.";
    header("Location: dashboard.php");
    exit;
}
$nome_funcionario_logado = htmlspecialchars($_SESSION['funcionario_nome'] ?? 'Usuário');
$id_funcionario_logado = $_SESSION['funcionario_id'];
$is_admin = (isset($_SESSION['funcionario_cargo']) && $_SESSION['funcionario_cargo'] === 'admin');

if (isset($_SESSION['funcionario_nome'])) {
    $nome_funcionario = htmlspecialchars($_SESSION['funcionario_nome']);
} else {
    $nome_funcionario = "Usuário";
}

$modo_edicao = false;
$funcionario_id_edicao = null;
$funcionario_data = [
    'nome_completo' => '', 'email' => '', 'cargo' => 'atendente'
];

if (isset($_GET['action']) && $_GET['action'] == 'editar' && isset($_GET['id'])) {
    $modo_edicao = true;
    $funcionario_id_edicao = (int)$_GET['id'];

    $sql_func = "SELECT nome_completo, email, cargo FROM funcionarios WHERE id = ?";
    $stmt_func = $conexao->prepare($sql_func);
    if ($stmt_func) {
        $stmt_func->bind_param("i", $funcionario_id_edicao);
        $stmt_func->execute();
        $result_func = $stmt_func->get_result();
        if ($result_func->num_rows === 1) {
            $db_data = $result_func->fetch_assoc();
            $funcionario_data['nome_completo'] = $db_data['nome_completo'];
            $funcionario_data['email'] = $db_data['email'];
            $funcionario_data['cargo'] = $db_data['cargo'];
        } else {
            $_SESSION['form_message_type'] = "danger";
            $_SESSION['form_message'] = "Funcionário não encontrado para edição.";
            header("Location: listar_funcionarios.php"); exit;
        }
        $stmt_func->close();
    } else {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "Erro ao buscar dados do funcionário.";
        header("Location: listar_funcionarios.php"); exit;
    }
}
$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $modo_edicao ? 'Editar Funcionário' : 'Cadastrar Novo Funcionário'; ?> - Administração</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> body { padding-top: 56px; } </style>
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
                <h2><?php echo $modo_edicao ? 'Editar Funcionário' : 'Cadastro de Novo Funcionário'; ?></h2>
                <hr>
                <?php
                if (isset($_SESSION['form_message_type']) && isset($_SESSION['form_message'])) {
                    echo '<div class="alert alert-' . htmlspecialchars($_SESSION['form_message_type']) . '" role="alert">' . nl2br(htmlspecialchars($_SESSION['form_message'])) . '</div>';
                    unset($_SESSION['form_message_type']);
                    unset($_SESSION['form_message']);
                }
                ?>
                <form action="processa_cadastro_funcionario.php" method="POST">
                    <?php if ($modo_edicao): ?>
                        <input type="hidden" name="funcionario_id" value="<?php echo $funcionario_id_edicao; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="nome_completo" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome_completo" name="nome_completo" required value="<?php echo htmlspecialchars($funcionario_data['nome_completo'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email (para login) <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($funcionario_data['email'] ?? ''); ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="senha" class="form-label">Senha <?php if(!$modo_edicao) echo '<span class="text-danger">*</span>'; ?></label>
                            <input type="password" class="form-control" id="senha" name="senha" <?php echo !$modo_edicao ? 'required' : ''; ?> minlength="6">
                            <small class="form-text text-muted"><?php echo $modo_edicao ? 'Deixe em branco para não alterar a senha.' : 'Mínimo de 6 caracteres.'; ?></small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirma_senha" class="form-label">Confirmar Senha <?php if(!$modo_edicao) echo '<span class="text-danger">*</span>'; ?></label>
                            <input type="password" class="form-control" id="confirma_senha" name="confirma_senha" <?php echo !$modo_edicao ? 'required' : ''; ?>>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="cargo" class="form-label">Cargo <span class="text-danger">*</span></label>
                        <select class="form-select" id="cargo" name="cargo" required>
                            <option value="atendente" <?php echo ($funcionario_data['cargo'] == 'atendente') ? 'selected' : ''; ?>>Atendente</option>
                            <option value="admin" <?php echo ($funcionario_data['cargo'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                        </select>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary btn-lg"><?php echo $modo_edicao ? 'Salvar Alterações' : 'Cadastrar Funcionário'; ?></button>
                    <a href="listar_funcionarios.php" class="btn btn-secondary btn-lg">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>