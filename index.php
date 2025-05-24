<?php
session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gestão</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f0f2f5; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 420px;
        }
        .login-container h2 {
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .login-container p.lead {
            color: #666;
            margin-bottom: 30px;
            font-size: 0.95rem;
        }
        .form-floating label {
            color: #555;
        }
        .btn-login {
            background-color: #007bff; 
            border: none;
            padding: 12px;
            font-size: 1rem;
            font-weight: 500;
        }
        .btn-login:hover {
            background-color: #0056b3;
        }
        .alert-custom {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="text-center">
            <h2>Bem-vindo de volta!</h2>
            <p class="lead">Acesse sua conta para continuar.</p>
        </div>

        <?php
        if (isset($_SESSION['login_error_message'])) {
            echo '<div class="alert alert-danger alert-custom" role="alert">' . htmlspecialchars($_SESSION['login_error_message']) . '</div>';
            unset($_SESSION['login_error_message']);
        }
        ?>

        <form action="processa_login.php" method="POST" novalidate>
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email" placeholder="seu@email.com" required autofocus>
                <label for="email">Endereço de Email</label>
            </div>
            <div class="form-floating mb-4">
                <input type="password" class="form-control" id="senha" name="senha" placeholder="Sua senha" required>
                <label for="senha">Senha</label>
            </div>
            <div class="d-grid">
                <button class="btn btn-primary btn-login" type="submit">Entrar</button>
            </div>
        </form>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>