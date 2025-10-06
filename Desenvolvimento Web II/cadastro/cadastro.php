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
    <title>Cadastro</title>
</head>
<body>
    <h2>Tela de Cadastro</h2>
    <form method="post">
        <label>Nome:</label><br>
        <input type="text" name="nome" required><br><br>

        <label>E-mail:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Senha:</label><br>
        <input type="password" name="senha" required><br><br>

        <button type="submit">Salvar</button>
    </form>
</body>
</html>
