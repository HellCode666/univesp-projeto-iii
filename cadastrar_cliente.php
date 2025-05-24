<?php
session_start();
require_once 'db_conexao.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
$nome_funcionario_logado = htmlspecialchars($_SESSION['funcionario_nome'] ?? 'Usuário');
$is_admin = (isset($_SESSION['funcionario_cargo']) && $_SESSION['funcionario_cargo'] === 'admin');

if (isset($_SESSION['funcionario_nome'])) {
    $nome_funcionario = htmlspecialchars($_SESSION['funcionario_nome']);
} else {
    $nome_funcionario = "Usuário";
}

$modo_edicao = false;
$cliente_id_edicao = null;
$cliente_data = [
    'nome_completo' => '', 'cpf' => '', 'telefone_celular' => '', 'telefone_fixo' => '',
    'email' => '', 'data_nascimento' => '', 'endereco_rua' => '', 'endereco_numero' => '',
    'endereco_complemento' => '', 'endereco_bairro' => '', 'endereco_cidade' => '',
    'endereco_estado' => '', 'endereco_cep' => '', 'observacoes' => ''
];

if (isset($_GET['action']) && $_GET['action'] == 'editar' && isset($_GET['id'])) {
    $modo_edicao = true;
    $cliente_id_edicao = (int)$_GET['id'];

    $sql_cliente = "SELECT * FROM clientes WHERE id = ?";
    $stmt_cliente = $conexao->prepare($sql_cliente);
    if ($stmt_cliente) {
        $stmt_cliente->bind_param("i", $cliente_id_edicao);
        $stmt_cliente->execute();
        $result_cliente = $stmt_cliente->get_result();
        if ($result_cliente->num_rows === 1) {
    
            $db_data = $result_cliente->fetch_assoc();
            foreach ($cliente_data as $key => $value) {
                if (isset($db_data[$key])) {
                    $cliente_data[$key] = $db_data[$key];
                }
            }
        } else {
            $_SESSION['form_message_type'] = "danger";
            $_SESSION['form_message'] = "Cliente não encontrado para edição.";
            $stmt_cliente->close();
            $conexao->close();
            header("Location: listar_clientes.php");
            exit;
        }
        $stmt_cliente->close();
    } else {
        $_SESSION['form_message_type'] = "danger";
        $_SESSION['form_message'] = "Erro ao preparar para buscar cliente.";
        $conexao->close();
        header("Location: listar_clientes.php");
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
    <title><?php echo $modo_edicao ? 'Editar Cliente' : 'Cadastrar Novo Cliente'; ?> - Sistema de Gestão</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 56px; }
        .form-section-title { margin-top: 2rem; margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; }
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
            <div class="col-md-10 offset-md-1">
                <h2><?php echo $modo_edicao ? 'Editar Cliente: ' . htmlspecialchars($cliente_data['nome_completo'] ?? '') : 'Cadastro de Novo Cliente'; ?></h2>
                <hr>

                <?php
                if (isset($_SESSION['form_message_type']) && isset($_SESSION['form_message'])) {
                    echo '<div class="alert alert-' . htmlspecialchars($_SESSION['form_message_type']) . '" role="alert">' . nl2br(htmlspecialchars($_SESSION['form_message'])) . '</div>';
                    unset($_SESSION['form_message_type']);
                    unset($_SESSION['form_message']);
                }
                ?>

                <form action="processa_cadastro_cliente.php" method="POST">
                    <?php if ($modo_edicao): ?>
                        <input type="hidden" name="cliente_id" value="<?php echo $cliente_id_edicao; ?>">
                    <?php endif; ?>

                    <h5 class="form-section-title">Dados Pessoais</h5>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="nome_completo" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nome_completo" name="nome_completo" required value="<?php echo htmlspecialchars($cliente_data['nome_completo'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="cpf" class="form-label">CPF</label>
                            <input type="text" class="form-control" id="cpf" name="cpf" placeholder="000.000.000-00" value="<?php echo htmlspecialchars($cliente_data['cpf'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="telefone_celular" class="form-label">Celular <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="telefone_celular" name="telefone_celular" placeholder="(00) 00000-0000" required value="<?php echo htmlspecialchars($cliente_data['telefone_celular'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="telefone_fixo" class="form-label">Telefone Fixo</label>
                            <input type="text" class="form-control" id="telefone_fixo" name="telefone_fixo" placeholder="(00) 0000-0000" value="<?php echo htmlspecialchars($cliente_data['telefone_fixo'] ?? ''); ?>">
                        </div>
                         <div class="col-md-4 mb-3">
                            <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                            <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($cliente_data['data_nascimento'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="cliente@exemplo.com" value="<?php echo htmlspecialchars($cliente_data['email'] ?? ''); ?>">
                        </div>
                    </div>

                    <h5 class="form-section-title">Endereço</h5>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="endereco_cep" class="form-label">CEP</label>
                            <input type="text" class="form-control" id="endereco_cep" name="endereco_cep" placeholder="00000-000" value="<?php echo htmlspecialchars($cliente_data['endereco_cep'] ?? ''); ?>">
                        </div>
                        <div class="col-md-7 mb-3">
                            <label for="endereco_rua" class="form-label">Rua / Logradouro</label>
                            <input type="text" class="form-control" id="endereco_rua" name="endereco_rua" value="<?php echo htmlspecialchars($cliente_data['endereco_rua'] ?? ''); ?>">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="endereco_numero" class="form-label">Número</label>
                            <input type="text" class="form-control" id="endereco_numero" name="endereco_numero" value="<?php echo htmlspecialchars($cliente_data['endereco_numero'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="endereco_complemento" class="form-label">Complemento</label>
                            <input type="text" class="form-control" id="endereco_complemento" name="endereco_complemento" value="<?php echo htmlspecialchars($cliente_data['endereco_complemento'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="endereco_bairro" class="form-label">Bairro</label>
                            <input type="text" class="form-control" id="endereco_bairro" name="endereco_bairro" value="<?php echo htmlspecialchars($cliente_data['endereco_bairro'] ?? ''); ?>">
                        </div>
                         <div class="col-md-3 mb-3">
                            <label for="endereco_cidade" class="form-label">Cidade</label>
                            <input type="text" class="form-control" id="endereco_cidade" name="endereco_cidade" value="<?php echo htmlspecialchars($cliente_data['endereco_cidade'] ?? ''); ?>">
                        </div>
                        <div class="col-md-1 mb-3">
                            <label for="endereco_estado" class="form-label">UF</label>
                            <input type="text" class="form-control" id="endereco_estado" name="endereco_estado" maxlength="2" value="<?php echo htmlspecialchars($cliente_data['endereco_estado'] ?? ''); ?>">
                        </div>
                    </div>

                    <h5 class="form-section-title">Outras Informações</h5>
                     <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="observacoes" class="form-label">Observações</label>
                            <textarea class="form-control" id="observacoes" name="observacoes" rows="3"><?php echo htmlspecialchars($cliente_data['observacoes'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <?php echo $modo_edicao ? 'Salvar Alterações' : 'Cadastrar Cliente'; ?>
                    </button>
                    <a href="<?php echo $modo_edicao ? 'listar_clientes.php' : 'dashboard.php'; ?>" class="btn btn-secondary btn-lg">Cancelar</a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>