<?php
session_start();
require_once 'db_conexao.php';

// VERIFICAÇÃO DE ADMIN
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

$funcionarios_lista = [];
$sql_funcionarios = "SELECT id, nome_completo, email, cargo FROM funcionarios ORDER BY nome_completo ASC";
$result_funcionarios = $conexao->query($sql_funcionarios);
if ($result_funcionarios && $result_funcionarios->num_rows > 0) {
    while ($row = $result_funcionarios->fetch_assoc()) {
        $funcionarios_lista[] = $row;
    }
}
$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Funcionários - Administração</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style> body { padding-top: 56px; } .actions-form { display: inline-block; margin-left: 5px; } </style>
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
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Gerenciamento de Funcionários</h2>
            <a href="cadastrar_funcionario.php" class="btn btn-success"><i class="bi bi-plus-circle"></i> Novo Funcionário</a>
        </div>
        <hr>
        <?php
        if (isset($_SESSION['form_message_type']) && isset($_SESSION['form_message'])) {
            echo '<div class="alert alert-' . htmlspecialchars($_SESSION['form_message_type']) . ' alert-dismissible fade show" role="alert">' .
                 nl2br(htmlspecialchars($_SESSION['form_message'])) .
                 '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['form_message_type']);
            unset($_SESSION['form_message']);
        }
        ?>
        <?php if (empty($funcionarios_lista)): ?>
            <div class="alert alert-info">Nenhum funcionário cadastrado.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr><th>ID</th><th>Nome Completo</th><th>Email</th><th>Cargo</th><th>Ações</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($funcionarios_lista as $func): ?>
                        <tr>
                            <td><?php echo $func['id']; ?></td>
                            <td><?php echo htmlspecialchars($func['nome_completo']); ?></td>
                            <td><?php echo htmlspecialchars($func['email']); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($func['cargo'])); ?></td>
                            <td>
                                <a href="cadastrar_funcionario.php?action=editar&id=<?php echo $func['id']; ?>" class="btn btn-sm btn-primary" title="Editar"><i class="bi bi-pencil-square"></i></a>
                                <?php if ($func['id'] != $id_funcionario_logado): // Não permitir auto-exclusão ?>
                                <form method="POST" action="processa_delete_funcionario.php" class="actions-form" onsubmit="return confirm('Tem certeza que deseja excluir este funcionário?');">
                                    <input type="hidden" name="funcionario_id_delete" value="<?php echo $func['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Excluir"><i class="bi bi-trash3-fill"></i></button>
                                </form>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" title="Não pode excluir a si mesmo" disabled><i class="bi bi-trash3-fill"></i></button>
                                <?php endif; ?>
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