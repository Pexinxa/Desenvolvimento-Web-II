<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $arquivo = fopen("usuarios.txt", "a+");

    fwrite($arquivo, "$nome|$email|$senha\n");
    fclose($arquivo);

    setcookie("email_usuario", $email, time() + 3600); // 1h

    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Cadastro</title>
</head>
<body>
  <div class="container">
    <h2>Tela de Cadastro</h2>
    <form method="post">
      <label>Nome:</label>
      <input type="text" name="nome" placeholder="Seu nome completo" required>

      <label>E-mail:</label>
      <input type="email" name="email" placeholder="Seu e-mail" required>

      <label>Senha:</label>
      <input type="password" name="senha" placeholder="Crie uma senha" required>

      <button type="submit">Salvar</button>
    </form>
    <p>Já tem conta? <a href="login.php">Faça login</a></p>
  </div>
</body>

