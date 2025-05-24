<?php
$senhaParaTeste = 'senha123';

$hashDaSenha = password_hash($senhaParaTeste, PASSWORD_DEFAULT);

echo "Senha original: " . htmlspecialchars($senhaParaTeste) . "<br>";
echo "Hash para o banco: " . htmlspecialchars($hashDaSenha);
?>