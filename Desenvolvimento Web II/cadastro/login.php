<?php
session_start();

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $arquivo = fopen("usuarios.txt", "r");
    $usuario_encontrado = false;

    while (($linha = fgets($arquivo)) !== false) {
        list($nome_salvo, $email_salvo, $senha_salva) = explode("|", trim($linha));

        if ($email == $email_salvo && $senha == $senha_salva) {
            $_SESSION['nome'] = $nome_salvo;
            $_SESSION['email'] = $email_salvo;
            $usuario_encontrado = true;
            break;
        }
    }

    fclose($arquivo);

    if ($usuario_encontrado) {
        header("Location: area_usuario.php");
        exit();
    } else {
        $mensagem = "E-mail ou senha incorretos!";
    }
}

$email_cookie = isset($_COOKIE['email_usuario']) ? $_COOKIE['email_usuario'] : "";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h2>Tela de Login</h2>

    <form method="post">
        <label>E-mail:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($email_cookie) ?>" required><br><br>

        <label>Senha:</label><br>
        <input type="password" name="senha" required><br><br>

        <button type="submit">Entrar</button>
    </form>

    <p style="color:red;"><?= $mensagem ?></p>

    <p>Não tem conta? <a href="cadastro.php">Cadastre-se aqui</a></p>
</body>
</html>
