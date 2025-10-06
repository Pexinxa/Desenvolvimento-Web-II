<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Área do Usuário</title>
</head>
<body>
    <h2>Bem-vindo à Área do Usuário!</h2>

    <p><strong>Nome:</strong> <?= htmlspecialchars($_SESSION['nome']) ?></p>
    <p><strong>E-mail:</strong> <?= htmlspecialchars($_SESSION['email']) ?></p>

    <form action="logout.php" method="post">
        <button type="submit">Logout</button>
    </form>
</body>
</html>
